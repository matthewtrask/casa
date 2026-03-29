<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class PlantNetService
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://my-api.plantnet.org/v2/identify';

    public function __construct()
    {
        $this->client = new Client(['timeout' => 20]);
        $this->apiKey = config('services.plantnet.key', '');
    }

    /**
     * Identify a plant from an uploaded photo.
     * HEIC files are auto-converted to JPEG via ImageMagick before sending.
     * Returns top matches with scientific name, common names, and confidence score.
     */
    public function identify(UploadedFile $image): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'PlantNet API key not configured.'];
        }

        try {
            [$filePath, $fileName, $tempCreated] = $this->prepareImage($image);

            $response = $this->client->post("{$this->baseUrl}/all", [
                'query' => [
                    'api-key' => $this->apiKey,
                ],
                'multipart' => [
                    [
                        'name'     => 'images',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $fileName,
                    ],
                    [
                        'name'     => 'organs',
                        'contents' => 'auto',
                    ],
                ],
            ]);

            // Clean up any temp file we created
            if ($tempCreated && file_exists($filePath)) {
                unlink($filePath);
            }

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['results'])) {
                return ['error' => 'No plants identified. Try a clearer photo.'];
            }

            // Return top 3 matches
            return [
                'results' => collect($data['results'])
                    ->take(3)
                    ->map(fn($r) => [
                        'scientific_name' => $r['species']['scientificNameWithoutAuthor'] ?? '',
                        'common_names'    => $r['species']['commonNames'] ?? [],
                        'family'          => $r['species']['family']['scientificNameWithoutAuthor'] ?? '',
                        'score'           => round(($r['score'] ?? 0) * 100),
                    ])
                    ->values()
                    ->toArray(),
            ];
        } catch (GuzzleException $e) {
            Log::warning('PlantNet identification failed: ' . $e->getMessage());
            return ['error' => 'Identification request failed. Please try again.'];
        }
    }

    /**
     * Prepare the image for upload.
     * HEIC/HEIF files are converted to JPEG via Imagick before sending to PlantNet.
     * Returns [filePath, fileName, wasConverted].
     */
    protected function prepareImage(UploadedFile $image): array
    {
        $ext  = strtolower($image->getClientOriginalExtension());
        $mime = strtolower($image->getMimeType() ?? '');

        $isHeic = in_array($ext, ['heic', 'heif'])
               || in_array($mime, ['image/heic', 'image/heif', 'image/x-heic']);

        if ($isHeic) {
            $tmpPath = tempnam(sys_get_temp_dir(), 'casa_') . '.jpg';

            // ffmpeg is already in the Docker image and handles all HEIC variants
            // including those with auxiliary image references that trip up libheif.
            exec('ffmpeg -y -i ' . escapeshellarg($image->getRealPath()) . ' -q:v 2 ' . escapeshellarg($tmpPath) . ' 2>&1', $out, $exitCode);

            if ($exitCode === 0 && file_exists($tmpPath) && filesize($tmpPath) > 0) {
                return [$tmpPath, 'photo.jpg', true];
            }

            // Fall back to Imagick if ffmpeg fails for any reason.
            if (class_exists(\Imagick::class)) {
                try {
                    $imagick = new \Imagick();
                    $imagick->readImage($image->getRealPath() . '[0]');
                    $imagick->setImageFormat('jpeg');
                    $imagick->setImageCompressionQuality(85);
                    $imagick->stripImage();

                    file_put_contents($tmpPath, $imagick->getImageBlob());
                    $imagick->clear();

                    return [$tmpPath, 'photo.jpg', true];
                } catch (\ImagickException $e) {
                    Log::info('HEIC conversion skipped, sending original to PlantNet: ' . $e->getMessage());
                }
            }
        }

        return [$image->getRealPath(), $image->getClientOriginalName(), false];
    }
}

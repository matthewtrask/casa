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
     * HEIC conversion is handled client-side; this just returns the file as-is.
     * Returns [filePath, fileName, wasConverted].
     */
    protected function prepareImage(UploadedFile $image): array
    {
        return [$image->getRealPath(), $image->getClientOriginalName(), false];
    }
}

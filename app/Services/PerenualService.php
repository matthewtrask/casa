<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class PerenualService
{
    protected Client $client;
    protected string $apiKey;
    protected string $baseUrl = 'https://perenual.com/api';

    public function __construct()
    {
        $this->client = new Client(['timeout' => 10]);
        $this->apiKey = config('services.perenual.key', '');
    }

    /**
     * Search for plants by name, returns simplified list for autocomplete.
     */
    public function search(string $query): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = $this->client->get("{$this->baseUrl}/species-list", [
                'query' => [
                    'key' => $this->apiKey,
                    'q'   => $query,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return collect($data['data'] ?? [])
                ->take(6)
                ->map(fn($plant) => [
                    'id'              => $plant['id'],
                    'common_name'     => $plant['common_name'] ?? '',
                    'scientific_name' => $plant['scientific_name'][0] ?? '',
                ])
                ->values()
                ->toArray();
        } catch (GuzzleException $e) {
            Log::warning('Perenual search failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get full care details for a species by ID.
     * Maps Perenual fields to Casa's TrackableItem fields.
     */
    public function getCareDetails(int $speciesId): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        try {
            $response = $this->client->get("{$this->baseUrl}/species/details/{$speciesId}", [
                'query' => ['key' => $this->apiKey],
            ]);

            $plant = json_decode($response->getBody()->getContents(), true);

            return [
                'species'               => $plant['scientific_name'][0] ?? null,
                'action_frequency_days' => $this->mapWateringToDays($plant['watering'] ?? null),
                'sunlight_needs'        => $this->mapSunlight($plant['sunlight'] ?? []),
                'notes'                 => $this->buildCareNotes($plant),
            ];
        } catch (GuzzleException $e) {
            Log::warning('Perenual details failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert Perenual watering string to days between watering.
     * Frequent = ~3 days, Average = ~7 days, Minimum = ~14 days, None = ~30 days
     */
    protected function mapWateringToDays(?string $watering): int
    {
        return match (strtolower($watering ?? '')) {
            'frequent'  => 3,
            'average'   => 7,
            'minimum'   => 14,
            'none'      => 30,
            default     => 7,
        };
    }

    /**
     * Convert Perenual sunlight array to Casa's enum (low/medium/high/direct).
     * Perenual returns values like "full sun", "full_sun", "part shade",
     * "filtered_shade", "deep_shade", "part sun/part shade", etc.
     */
    protected function mapSunlight(array $sunlight): string
    {
        // Normalize: lowercase and replace underscores with spaces
        $sunlight = array_map(
            fn($s) => str_replace('_', ' ', strtolower($s)),
            $sunlight
        );

        $joined = implode(' ', $sunlight);

        // Direct / full sun
        if (str_contains($joined, 'full sun')) {
            return 'direct';
        }

        // Low light — shade varieties
        if (
            str_contains($joined, 'deep shade') ||
            str_contains($joined, 'full shade') ||
            str_contains($joined, 'filtered shade')
        ) {
            return 'low';
        }

        // Medium — partial sun/shade
        if (
            str_contains($joined, 'part shade') ||
            str_contains($joined, 'part sun') ||
            str_contains($joined, 'indirect')
        ) {
            return 'medium';
        }

        // High — bright but not direct
        if (str_contains($joined, 'bright')) {
            return 'high';
        }

        return 'medium';
    }

    /**
     * Build a notes string from available care data.
     */
    protected function buildCareNotes(array $plant): string
    {
        $notes = [];

        if (!empty($plant['care_level'])) {
            $notes[] = "Care level: {$plant['care_level']}";
        }
        if (!empty($plant['maintenance'])) {
            $notes[] = "Maintenance: {$plant['maintenance']}";
        }
        if (!empty($plant['cycle'])) {
            $notes[] = "Cycle: {$plant['cycle']}";
        }
        if (!empty($plant['poisonous_to_pets'])) {
            $notes[] = "⚠️ Toxic to pets";
        }
        if (!empty($plant['poisonous_to_humans'])) {
            $notes[] = "⚠️ Toxic to humans";
        }

        return implode(' · ', $notes);
    }
}

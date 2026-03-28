<?php

namespace App\Http\Controllers;

use App\Services\PerenualService;
use App\Services\PlantNetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlantLookupController extends Controller
{
    public function __construct(
        protected PerenualService $perenual,
        protected PlantNetService $plantNet,
    ) {}

    /**
     * Search for plants by name — returns JSON for autocomplete.
     * GET /plants/search?q=calathea
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        return response()->json($this->perenual->search($query));
    }

    /**
     * Get care details for a specific species ID.
     * GET /plants/care/{id}
     */
    public function care(int $id): JsonResponse
    {
        $details = $this->perenual->getCareDetails($id);

        if (empty($details)) {
            return response()->json(['error' => 'Could not fetch care details'], 404);
        }

        return response()->json($details);
    }

    /**
     * Identify a plant from a photo, then look up care details via Perenual.
     * POST /plants/identify
     */
    public function identify(Request $request): JsonResponse
    {
        $request->validate(['photo' => 'required|file|max:20480|mimes:jpg,jpeg,png,gif,webp,heic,heif']);

        // Step 1: Identify via PlantNet
        $identification = $this->plantNet->identify($request->file('photo'));

        if (isset($identification['error'])) {
            return response()->json(['error' => $identification['error']], 422);
        }

        $results = $identification['results'];

        // Step 2: Enrich only the top result with Perenual care data.
        // Results 2 & 3 are fetched on-demand client-side only if the user clicks them,
        // keeping Perenual API usage minimal on the free tier.
        if (!empty($results[0])) {
            $perenualMatch = $this->findInPerenual(
                $results[0]['scientific_name'],
                $results[0]['common_names'] ?? []
            );

            if ($perenualMatch) {
                $results[0]['care'] = $this->perenual->getCareDetails($perenualMatch['id']);
                $results[0]['perenual_id'] = $perenualMatch['id'];
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Try to find a plant in Perenual using multiple search strategies.
     * Perenual's taxonomy can lag modern names (e.g. Sansevieria vs Dracaena),
     * so we try: scientific name → genus only → first common name → second common name.
     */
    protected function findInPerenual(string $scientificName, array $commonNames): ?array
    {
        // Try scientific name first, fall back to first common name
        foreach (array_filter([$scientificName, $commonNames[0] ?? null]) as $term) {
            $results = $this->perenual->search($term);
            if (!empty($results)) {
                return $results[0];
            }
        }

        return null;
    }
}

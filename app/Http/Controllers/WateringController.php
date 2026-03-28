<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class WateringController extends Controller
{
    public function store(Request $request, Plant $plant): RedirectResponse
    {
        $validated = $request->validate([
            'watered_by' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $plant->wateringLogs()->create($validated);
        $plant->update(['last_watered_at' => now()]);

        return redirect()->route('plants.show', $plant)->with('success', 'Plant watering logged!');
    }
}

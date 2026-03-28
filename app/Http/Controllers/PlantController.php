<?php

namespace App\Http\Controllers;

use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PlantController extends Controller
{
    public function index(): View
    {
        $plants = Plant::all();
        return view('plants.index', compact('plants'));
    }

    public function create(): View
    {
        return view('plants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'water_frequency_days' => 'required|integer|min:1|max:365',
            'sunlight_needs' => 'required|in:low,medium,high,direct',
            'notes' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
        ]);

        Plant::create($validated);

        return redirect()->route('plants.index')->with('success', 'Plant added successfully!');
    }

    public function show(Plant $plant): View
    {
        $plant->load('wateringLogs');
        return view('plants.show', compact('plant'));
    }

    public function edit(Plant $plant): View
    {
        return view('plants.edit', compact('plant'));
    }

    public function update(Request $request, Plant $plant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'water_frequency_days' => 'required|integer|min:1|max:365',
            'sunlight_needs' => 'required|in:low,medium,high,direct',
            'notes' => 'nullable|string',
            'image_path' => 'nullable|string|max:255',
        ]);

        $plant->update($validated);

        return redirect()->route('plants.show', $plant)->with('success', 'Plant updated successfully!');
    }

    public function destroy(Plant $plant): RedirectResponse
    {
        $plant->delete();
        return redirect()->route('plants.index')->with('success', 'Plant deleted successfully!');
    }
}

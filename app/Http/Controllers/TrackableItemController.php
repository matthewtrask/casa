<?php

namespace App\Http\Controllers;

use App\Models\TrackableItem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class TrackableItemController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $query = TrackableItem::query();

        if ($category && in_array($category, ['plant', 'chore', 'maintenance', 'pet', 'other'])) {
            $query->where('category', $category);
        }

        $items = $query->get();

        return view('items.index', compact('items', 'category'));
    }

    public function create(Request $request): View
    {
        $preselectedCategory = $request->query('category');
        return view('items.create', compact('preselectedCategory'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'species'               => 'nullable|string|max:255',
            'location'              => 'required|string|max:255',
            'action_frequency_days' => 'required|integer|min:1|max:365',
            'category'              => 'required|in:plant,chore,maintenance,pet,other',
            'sunlight_needs'        => 'nullable|in:low,medium,high,direct',
            'notes'                 => 'nullable|string',
            'photo'                 => 'nullable|file|image|max:20480',
            'last_action_at'        => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->hasFile('photo')) {
            $validated['image_path'] = $request->file('photo')->store('plants', config('filesystems.default'));
        }

        // Maintenance items: default date to today if not provided, and set a far-future frequency
        if ($validated['category'] === 'maintenance') {
            $validated['last_action_at']        = $validated['last_action_at'] ?? now()->toDateString();
            $validated['action_frequency_days'] = 3650;
        }

        unset($validated['photo']);
        $item = TrackableItem::create($validated);

        // Redirect back to the same category list
        $category = $item->category instanceof \App\Enums\ItemCategory
            ? $item->category->value
            : $item->category;

        return redirect()
            ->route('items.index', ['category' => $category])
            ->with('success', $category === 'maintenance' ? 'Maintenance entry logged!' : 'Item added successfully!');
    }

    public function show(TrackableItem $item): View
    {
        $item->load('actionLogs');
        return view('items.show', compact('item'));
    }

    public function edit(TrackableItem $item): View
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, TrackableItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'species'               => 'nullable|string|max:255',
            'location'              => 'required|string|max:255',
            'action_frequency_days' => 'required|integer|min:1|max:365',
            'category'              => 'required|in:plant,chore,maintenance,pet,other',
            'sunlight_needs'        => 'nullable|in:low,medium,high,direct',
            'notes'                 => 'nullable|string',
            'photo'                 => 'nullable|file|image|max:20480',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if it exists
            if ($item->image_path) {
                Storage::disk(config('filesystems.default'))->delete($item->image_path);
            }
            $validated['image_path'] = $request->file('photo')->store('plants', config('filesystems.default'));
        }

        unset($validated['photo']);
        $item->update($validated);

        return redirect()->route('items.show', $item)->with('success', 'Item updated successfully!');
    }

    public function destroy(TrackableItem $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk(config('filesystems.default'))->delete($item->image_path);
        }
        $item->delete();
        return redirect()->route('items.index')->with('success', 'Item deleted successfully!');
    }

    public function dashboard(): View
    {
        $categories = ['plant', 'chore', 'maintenance', 'pet', 'other'];
        $groupedItems = [];

        foreach ($categories as $category) {
            $items = TrackableItem::where('category', $category)->get();
            $due = $items->filter(fn ($item) => $item->isDue());
            $groupedItems[$category] = [
                'all'       => $items,
                'due'       => $due,
                'count'     => $items->count(),
                'due_count' => $due->count(),
            ];
        }

        return view('dashboard', compact('groupedItems'));
    }
}

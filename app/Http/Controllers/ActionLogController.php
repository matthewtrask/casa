<?php

namespace App\Http\Controllers;

use App\Models\TrackableItem;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ActionLogController extends Controller
{
    public function store(Request $request, TrackableItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'action_type' => 'required|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        $validated['performed_by'] = auth()->user()->name;

        $item->actionLogs()->create($validated);
        $item->update(['last_action_at' => now()]);

        return redirect()->back()->with('success', "{$item->name} marked as {$validated['action_type']}.");
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids'    => 'required|array|min:1',
            'item_ids.*'  => 'integer|exists:trackable_items,id',
            'action_type' => 'required|string|max:255',
        ]);

        $items = TrackableItem::whereIn('id', $validated['item_ids'])->get();
        $now   = now();

        foreach ($items as $item) {
            $item->actionLogs()->create([
                'action_type'  => $validated['action_type'],
                'performed_by' => auth()->user()->name,
            ]);
            $item->update(['last_action_at' => $now]);
        }

        $count = $items->count();
        $word  = $count === 1 ? 'item' : 'items';

        return redirect()->back()->with('success', "Marked {$count} {$word} as {$validated['action_type']}.");
    }
}

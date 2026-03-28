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
            'performed_by' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $item->actionLogs()->create($validated);
        $item->update(['last_action_at' => now()]);

        return redirect()->route('items.show', $item)->with('success', 'Action logged successfully!');
    }
}

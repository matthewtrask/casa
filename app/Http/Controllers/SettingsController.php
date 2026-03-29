<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('id')->get()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $channelKeys = [
            'slack_channel_default',
            'slack_channel_plant',
            'slack_channel_chore',
            'slack_channel_maintenance',
            'slack_channel_pet',
            'slack_channel_other',
        ];

        $validated = $request->validate(
            array_fill_keys($channelKeys, ['nullable', 'string', 'max:100', 'regex:/^#[a-z0-9_-]+$/'])
        );

        foreach ($channelKeys as $key) {
            Setting::set($key, $validated[$key] ?? null);
        }

        return redirect()->route('settings.index')->with('success', 'Settings saved.');
    }
}

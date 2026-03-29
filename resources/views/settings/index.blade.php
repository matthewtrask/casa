@extends('layouts.app')
@section('title', 'Settings')

@section('styles')
<style>
    .settings-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 24px;
        overflow: hidden;
    }
    html[data-theme="dark"] .settings-section { border-color: var(--border-strong); }

    .settings-section-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
    }
    html[data-theme="dark"] .settings-section-header { border-color: var(--border-strong); }

    .settings-section-title {
        font-family: var(--font-display);
        font-size: 16px;
        font-weight: 600;
    }
    .settings-section-desc {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 3px;
        line-height: 1.4;
    }

    .settings-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
    }
    html[data-theme="dark"] .settings-row { border-color: var(--border-strong); }
    .settings-row:last-child { border-bottom: none; }

    .settings-row-label { flex: 1; min-width: 0; }
    .settings-row-label strong {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .settings-row-label p {
        font-size: 12px;
        color: var(--text-muted);
    }

    .settings-row-control { width: 210px; flex-shrink: 0; }

    @media (max-width: 560px) {
        .settings-row { flex-direction: column; align-items: stretch; gap: 8px; }
        .settings-row-control { width: 100%; }
    }

    .channel-input {
        width: 100%;
        padding: 8px 12px;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-family: var(--font-body);
        color: var(--text);
        background: var(--surface);
        transition: border-color var(--ease);
        min-height: 40px;
        -webkit-appearance: none;
    }
    html[data-theme="dark"] .channel-input { border-color: var(--border-strong); background: var(--surface-2); }
    .channel-input:focus { outline: none; border-color: var(--text); }
    .channel-input::placeholder { color: var(--text-light); }

    .settings-hint {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 12px;
        padding: 10px 14px;
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        line-height: 1.5;
    }
    .settings-hint code {
        font-family: monospace;
        background: var(--surface-3);
        padding: 1px 5px;
        border-radius: 4px;
        font-size: 11px;
    }
</style>
@endsection

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">⚙️ Settings</div>
        <div class="page-subtitle">Configure notifications and app behaviour</div>
    </div>
</div>

<form method="POST" action="{{ route('settings.update') }}">
    @csrf

    <div class="settings-section">
        <div class="settings-section-header">
            <div class="settings-section-title">💬 Slack channels</div>
            <div class="settings-section-desc">Where the daily digest gets sent for each category. Leave a category blank to fall back to the default.</div>
        </div>

        @php
            $rows = [
                'slack_channel_default'     => ['emoji' => '🏠', 'label' => 'Default',     'hint' => 'Fallback when no category channel is set'],
                'slack_channel_plant'       => ['emoji' => '🌿', 'label' => 'Plants',       'hint' => 'Plant watering reminders'],
                'slack_channel_chore'       => ['emoji' => '🧹', 'label' => 'Chores',       'hint' => 'Household chore reminders'],
                'slack_channel_maintenance' => ['emoji' => '🔧', 'label' => 'Maintenance',  'hint' => 'Maintenance log reminders'],
                'slack_channel_pet'         => ['emoji' => '🐾', 'label' => 'Pets',         'hint' => 'Pet care reminders'],
                'slack_channel_other'       => ['emoji' => '📌', 'label' => 'Other',        'hint' => 'Everything else'],
            ];
        @endphp

        @foreach ($rows as $key => $cfg)
        <div class="settings-row">
            <div class="settings-row-label">
                <strong>{{ $cfg['emoji'] }} {{ $cfg['label'] }}</strong>
                <p>{{ $cfg['hint'] }}</p>
            </div>
            <div class="settings-row-control">
                <input
                    type="text"
                    name="{{ $key }}"
                    class="channel-input"
                    value="{{ old($key, $settings[$key]->value ?? '') }}"
                    placeholder="#channel-name"
                    autocomplete="off"
                    autocapitalize="none"
                    spellcheck="false"
                >
                @error($key)
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>
        @endforeach

        <div style="padding: 0 20px 16px;">
            <div class="settings-hint">
                Channel names must start with <code>#</code> and use lowercase letters, numbers, hyphens, or underscores.
                Make sure the bot has been invited to each channel with <code>/invite @YourBot</code>.
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary btn-lg">Save settings</button>
    </div>
</form>

@endsection

@extends('layouts.app')
@section('title', $item->name)

@section('styles')
<style>
    .item-hero {
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 20px;
        position: relative;
        background: var(--surface-2);
        width: 100%;
        height: 260px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
    }
    @media (min-width: 600px) { .item-hero { height: 340px; } }
    html[data-theme="dark"] .item-hero { border-color: var(--border-strong); }
    .item-hero img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
    .item-hero-emoji { font-size: 80px; padding: 32px; }
    .item-hero-status {
        position: absolute;
        bottom: 14px; right: 14px;
        background: rgba(15,13,11,.72);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-radius: 999px;
        padding: 5px 11px;
        border: 1px solid rgba(255,255,255,.1);
    }
    .item-hero-status .status-badge {
        background: transparent;
        padding: 0;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
    }
    .item-hero-status .status-dot { background: #4ade80; }
    .item-hero-cat {
        position: absolute;
        top: 14px; left: 14px;
        background: rgba(255,255,255,.88);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        color: #2c2825;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    html[data-theme="dark"] .item-hero-cat {
        background: rgba(30,28,25,.82);
        color: var(--text);
        border: 1px solid var(--border-strong);
    }

    .item-name {
        font-family: var(--font-display);
        font-size: 26px;
        font-weight: 600;
        letter-spacing: -.4px;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .item-header-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .item-header-actions { display: flex; gap: 8px; flex-shrink: 0; }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 20px;
    }
    @media (min-width: 600px) { .info-grid { grid-template-columns: repeat(4, 1fr); } }
    .info-tile {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 14px;
    }
    .info-tile-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .info-tile-value { font-size: 15px; font-weight: 500; }

    .action-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 20px;
    }
    .action-card-title {
        font-family: var(--font-display);
        font-size: 17px;
        font-weight: 600;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .action-card-due {
        border-left: 3px solid var(--critical);
        padding-left: 12px;
        margin-bottom: 14px;
    }
    .action-card-due strong { display: block; color: var(--critical); font-size: 14px; }
    .action-card-due span { font-size: 13px; color: var(--text-muted); }

    .notes-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        margin-bottom: 20px;
    }
    .notes-card-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
    .notes-card-body { font-size: 14px; line-height: 1.6; color: var(--text); }

    .timeline-title { font-family: var(--font-display); font-size: 17px; font-weight: 600; margin-bottom: 14px; }
    .timeline { display: flex; flex-direction: column; gap: 0; }
    .timeline-item {
        display: flex;
        gap: 14px;
        padding-bottom: 16px;
        position: relative;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 15px; top: 30px; bottom: 0;
        width: 1px;
        background: var(--border);
    }
    .timeline-item:last-child::before { display: none; }
    .timeline-dot {
        width: 30px; height: 30px;
        border-radius: 50%;
        background: var(--surface-2);
        border: 2px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .timeline-content { flex: 1; padding-top: 4px; }
    .timeline-action { font-weight: 600; font-size: 14px; margin-bottom: 2px; }
    .timeline-meta { font-size: 12px; color: var(--text-muted); }
    .timeline-note { font-size: 13px; color: var(--text); margin-top: 4px; font-style: italic; }
    .timeline-empty { font-size: 14px; color: var(--text-muted); }
</style>
@endsection

@section('content')
@php
    $catConfig = [
        'plant'       => ['label' => 'Plants',      'emoji' => '🌿', 'color' => '#16a34a'],
        'chore'       => ['label' => 'Chores',       'emoji' => '🧹', 'color' => '#3b82f6'],
        'maintenance' => ['label' => 'Maintenance',  'emoji' => '🔧', 'color' => '#f59e0b'],
        'pet'         => ['label' => 'Pets',         'emoji' => '🐾', 'color' => '#8b5cf6'],
        'other'       => ['label' => 'Other',        'emoji' => '📌', 'color' => '#64748b'],
    ];
    $cfg         = $catConfig[$item->category] ?? $catConfig['other'];
    $statusClass = $item->getStatusCssClass();
    $badgeClass  = match($statusClass) { 'status-ok' => 'badge-ok', 'status-warning' => 'badge-warning', default => 'badge-critical' };
@endphp

{{-- Hero --}}
<div class="item-hero">
    @if ($item->image_path)
        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}">
    @else
        <div class="item-hero-emoji">{{ $cfg['emoji'] }}</div>
    @endif
    <div class="item-hero-cat" style="color: {{ $cfg['color'] }}">
        {{ $cfg['emoji'] }} {{ $cfg['label'] }}
    </div>
    <div class="item-hero-status">
        <span class="status-badge {{ $badgeClass }}">
            <span class="status-dot"></span>
            {{ $item->status }}
        </span>
    </div>
</div>

{{-- Header --}}
<div class="item-header-row">
    <div>
        <div class="item-name">{{ $item->name }}</div>
        @if ($item->location)
        <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">📍 {{ $item->location }}</div>
        @endif
    </div>
    <div class="item-header-actions">
        <a href="{{ route('items.edit', $item) }}" class="btn btn-secondary">Edit</a>
        <form method="POST" action="{{ route('items.destroy', $item) }}"
              onsubmit="return confirm('Delete {{ $item->name }}? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>

{{-- Info tiles --}}
<div class="info-grid">
    <div class="info-tile">
        <div class="info-tile-label">Location</div>
        <div class="info-tile-value">{{ $item->location ?: '—' }}</div>
    </div>
    <div class="info-tile">
        <div class="info-tile-label">Frequency</div>
        <div class="info-tile-value">Every {{ $item->action_frequency_days }} {{ Str::plural('day', $item->action_frequency_days) }}</div>
    </div>
    @if ($item->isPlant() && $item->species)
    <div class="info-tile">
        <div class="info-tile-label">Species</div>
        <div class="info-tile-value">{{ $item->species }}</div>
    </div>
    @endif
    @if ($item->isPlant())
    @php
        $sunMap = [
            'low'    => ['label' => 'Low light',    'icon' => '🌑', 'bars' => 1],
            'medium' => ['label' => 'Indirect',     'icon' => '🌤',  'bars' => 2],
            'high'   => ['label' => 'Bright',       'icon' => '⛅',  'bars' => 3],
            'direct' => ['label' => 'Full sun',     'icon' => '☀️',  'bars' => 4],
        ];
        $sun = $sunMap[$item->sunlight_needs] ?? null;
    @endphp
    <div class="info-tile">
        <div class="info-tile-label">Sunlight</div>
        @if ($sun)
        <div class="info-tile-value" style="display:flex; align-items:center; gap:8px;">
            <span style="font-size:18px; line-height:1;">{{ $sun['icon'] }}</span>
            <div>
                <div style="font-size:14px; font-weight:600;">{{ $sun['label'] }}</div>
                <div style="display:flex; gap:3px; margin-top:4px;">
                    @for ($i = 1; $i <= 4; $i++)
                        <div style="width:14px; height:4px; border-radius:2px;
                            background: {{ $i <= $sun['bars'] ? '#f59e0b' : 'var(--border)' }};"></div>
                    @endfor
                </div>
            </div>
        </div>
        @else
        <div class="info-tile-value" style="color:var(--text-muted); font-size:13px;">
            Not set &mdash; <a href="{{ route('items.edit', $item) }}" style="color:inherit; text-decoration:underline;">edit to add</a>
        </div>
        @endif
    </div>
    @endif
    <div class="info-tile">
        <div class="info-tile-label">Last done</div>
        <div class="info-tile-value">{{ $item->last_action_at ? $item->last_action_at->diffForHumans() : 'Never' }}</div>
    </div>
</div>

{{-- Notes --}}
@if ($item->notes)
<div class="notes-card">
    <div class="notes-card-title">Notes</div>
    <div class="notes-card-body">{{ $item->notes }}</div>
</div>
@endif

{{-- Action card --}}
<div class="action-card">
    <div class="action-card-title">
        @if ($item->isDue())
            ⚡ Action needed
        @else
            ✓ Log an action
        @endif
    </div>
    @if ($item->isDue())
    <div class="action-card-due">
        <strong>{{ $item->status }}</strong>
        <span>{{ $item->getDueLabel() }} it now to get back on track.</span>
    </div>
    @endif
    <form method="POST" action="{{ route('items.action', $item) }}">
        @csrf
        <input type="hidden" name="action_type" value="{{ $item->getDueLabel() }}">
        <div class="form-group">
            <label class="form-label">Note (optional)</label>
            <textarea name="notes" class="form-input" rows="2" placeholder="e.g. Used fertiliser, replaced filter..."></textarea>
        </div>
        @php
            $dueLabel = $item->getDueLabel();
            $pastTense = str_ends_with(strtolower($dueLabel), 'e') ? $dueLabel . 'd' : $dueLabel . 'ed';
        @endphp
        <button type="submit" class="btn btn-primary btn-lg btn-full"
                style="background: {{ $cfg['color'] }}">
            ✓ Mark as {{ $pastTense }}
        </button>
    </form>
</div>

{{-- Timeline --}}
<div class="timeline-title">History</div>
@if ($item->actionLogs->count() > 0)
<div class="timeline">
    @foreach ($item->actionLogs->sortByDesc('created_at') as $log)
    <div class="timeline-item">
        <div class="timeline-dot">{{ $cfg['emoji'] }}</div>
        <div class="timeline-content">
            <div class="timeline-action">{{ $log->action_type }}</div>
            <div class="timeline-meta">
                {{ $log->performed_by ?? 'Unknown' }} · {{ $log->created_at->format('M j, Y') }}
            </div>
            @if ($log->notes)
            <div class="timeline-note">"{{ $log->notes }}"</div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<p class="timeline-empty">No actions logged yet.</p>
@endif
@endsection

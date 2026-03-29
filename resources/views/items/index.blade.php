@extends('layouts.app')
@section('title', ucfirst($category ?? 'All Items'))

@section('styles')
<style>
    .index-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .index-title { font-family: var(--font-display); font-size: 26px; font-weight: 600; letter-spacing: -.4px; display: flex; align-items: center; gap: 10px; }
    .item-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    @media (min-width: 640px)  { .item-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; } }
    @media (min-width: 900px)  { .item-grid { grid-template-columns: repeat(3, 1fr); } }

    .item-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow var(--ease), transform var(--ease);
        position: relative;
    }
    .item-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    html[data-theme="dark"] .item-card { border-color: var(--border-strong); background: var(--surface); }
    html[data-theme="dark"] .item-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.55), 0 1px 4px rgba(0,0,0,.3); }
    .item-card-accent {
        height: 3px;
        background: var(--cat-color, #64748b);
        flex-shrink: 0;
    }
    .item-card-photo {
        aspect-ratio: 4/3;
        background: var(--surface-2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .item-card-photo img { width: 100%; height: 100%; object-fit: cover; }
    .item-card-body { padding: 14px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .item-card-name { font-weight: 600; font-size: 15px; line-height: 1.3; }
    .item-card-location { font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 4px; }
    .item-card-footer { padding: 10px 14px 14px; display: flex; gap: 8px; align-items: center; }
    .item-card-footer .btn { flex: 1; font-size: 13px; padding: 8px 10px; min-height: 36px; }
    .item-card-footer form { flex: 1; }
    .item-card-footer form .btn { width: 100%; }
    .btn-ghost { background: transparent; color: var(--text-muted); border: 1px solid var(--border); }
    .btn-ghost:hover { background: var(--surface-2); color: var(--text); }
    html[data-theme="dark"] .btn-ghost { border-color: var(--border-strong); }
    html[data-theme="dark"] .btn-ghost:hover { background: var(--surface-3); color: var(--text); }

    .empty-state {
        grid-column: 1/-1;
        text-align: center;
        padding: 60px 24px;
        color: var(--text-muted);
    }
    .empty-state-icon { font-size: 52px; margin-bottom: 14px; }
    .empty-state h3 { font-family: var(--font-display); font-size: 20px; color: var(--text); margin-bottom: 6px; }
    .empty-state p { font-size: 14px; margin-bottom: 20px; }

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
    $cfg   = $catConfig[$category ?? 'other'] ?? ['label' => 'All Items', 'emoji' => '🏠', 'color' => '#2c2825'];
    $color = $category ? $cfg['color'] : '#2c2825';
@endphp

<div class="index-header">
    <div>
        <div class="index-title">
            <span style="color: {{ $color }}">{{ $cfg['emoji'] }}</span> {{ $cfg['label'] }}
        </div>
        <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
        </div>
    </div>
    <a href="{{ route('items.create', $category ? ['category' => $category] : []) }}" class="btn btn-primary">
        + Add {{ $category ? Str::singular($cfg['label']) : 'Item' }}
    </a>
</div>


<div class="item-grid">
    @forelse ($items as $item)
    @php
        $ic = $catConfig[$item->category] ?? $catConfig['other'];
        $statusClass = $item->getStatusCssClass();
        $badgeClass  = match($statusClass) { 'status-ok' => 'badge-ok', 'status-warning' => 'badge-warning', default => 'badge-critical' };
        $statusLabel = match($statusClass) { 'status-ok' => 'Good', 'status-warning' => 'Due soon', default => 'Overdue' };
    @endphp
    <div class="item-card">
        <div class="item-card-accent" style="background: {{ $ic['color'] }}"></div>
        <div class="item-card-photo">
            @if ($item->image_path)
                <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->name }}">
            @else
                {{ $ic['emoji'] }}
            @endif
        </div>
        <div class="item-card-body">
            <div class="item-card-name">{{ $item->name }}</div>
            @if ($item->location)
            <div class="item-card-location">📍 {{ $item->location }}</div>
            @endif
            <span class="status-badge {{ $badgeClass }}">
                <span class="status-dot"></span>
                {{ $item->status }}
            </span>
        </div>
        <div class="item-card-footer">
            <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">View</a>
            <form method="POST" action="{{ route('items.action', $item) }}">
                @csrf
                <input type="hidden" name="action_type" value="{{ $item->getDueLabel() }}">
                @if ($item->isDue())
                    <button type="submit" class="btn btn-primary">✓ Done</button>
                @else
                    <button type="submit" class="btn btn-ghost">✓ {{ $item->getDueLabel() }}</button>
                @endif
            </form>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-state-icon">{{ $cfg['emoji'] }}</div>
        <h3>No {{ strtolower($cfg['label']) }} yet</h3>
        <p>Add your first {{ strtolower(Str::singular($cfg['label'])) }} to start tracking it.</p>
        <a href="{{ route('items.create', $category ? ['category' => $category] : []) }}" class="btn btn-primary">
            + Add {{ $category ? Str::singular($cfg['label']) : 'Item' }}
        </a>
    </div>
    @endforelse
</div>
@endsection

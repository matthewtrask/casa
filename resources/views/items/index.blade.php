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
    .item-card.selected { box-shadow: 0 0 0 2px var(--cat-color, #16a34a), var(--shadow-md); transform: translateY(-2px); }
    .item-card-accent { height: 3px; background: var(--cat-color, #64748b); flex-shrink: 0; }
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

    .empty-state { grid-column: 1/-1; text-align: center; padding: 60px 24px; color: var(--text-muted); }
    .empty-state-icon { font-size: 52px; margin-bottom: 14px; }
    .empty-state h3 { font-family: var(--font-display); font-size: 20px; color: var(--text); margin-bottom: 6px; }
    .empty-state p { font-size: 14px; margin-bottom: 20px; }

    /* ── Selection checkbox ─────────────────────────────────── */
    .item-check-label {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 2;
        cursor: pointer;
        line-height: 1;
    }
    .item-check { position: absolute; opacity: 0; width: 0; height: 0; }
    .item-check-mark {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.8);
        background: rgba(0,0,0,0.3);
        color: transparent;
        font-size: 14px;
        font-weight: 700;
        transition: background 0.15s, color 0.15s;
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);
    }
    .item-check:checked + .item-check-mark {
        background: var(--cat-color, #16a34a);
        border-color: white;
        color: white;
    }

    /* ── Bulk action bar ────────────────────────────────────── */
    .bulk-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--surface);
        border-top: 2px solid var(--primary, #16a34a);
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 -4px 20px rgba(0,0,0,.12);
        z-index: 250;
        transform: translateY(100%);
        transition: transform 0.25s ease;
    }
    .bulk-bar.visible { transform: translateY(0); }
    html[data-theme="dark"] .bulk-bar { box-shadow: 0 -4px 20px rgba(0,0,0,.5); }
    .bulk-count { font-size: 14px; color: var(--text-muted); white-space: nowrap; }
    .btn-link { background: none; border: none; color: var(--text-muted); font-size: 13px; cursor: pointer; padding: 4px 8px; text-decoration: underline; }
    .btn-link:hover { color: var(--text); }
    @media (max-width: 899px) { .bulk-bar { bottom: var(--bottom-nav-h); } }

    /* ── Select-all link ────────────────────────────────────── */
    .select-all-btn { background: none; border: none; color: var(--primary, #16a34a); font-size: 13px; cursor: pointer; padding: 0; text-decoration: underline; }
    .select-all-btn:hover { opacity: 0.75; }
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
    $cfg      = $catConfig[$category ?? ''] ?? ['label' => 'All Items', 'emoji' => '🏠', 'color' => '#2c2825'];
    $color    = $category ? $cfg['color'] : '#2c2825';
    $bulkLabel = match($category ?? '') {
        'plant'       => 'Water',
        'chore'       => 'Complete',
        'maintenance' => 'Service',
        'pet'         => 'Feed/Care',
        default       => 'Complete',
    };
@endphp

<div class="index-header">
    <div>
        <div class="index-title">
            <span style="color: {{ $color }}">{{ $cfg['emoji'] }}</span> {{ $cfg['label'] }}
        </div>
        <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">
            {{ $items->count() }} {{ Str::plural('item', $items->count()) }}
            @if ($items->count() > 1)
            &middot; <button type="button" class="select-all-btn" id="select-all-btn" onclick="toggleSelectAll()">Select all</button>
            @endif
        </div>
    </div>
    <a href="{{ route('items.create', $category ? ['category' => $category] : []) }}" class="btn btn-primary">
        + Add {{ $category ? Str::singular($cfg['label']) : 'Item' }}
    </a>
</div>

{{-- Hidden form for bulk action; JS populates item IDs before submit --}}
<form id="bulk-form" method="POST" action="{{ route('items.bulk-action') }}">
    @csrf
    <input type="hidden" name="action_type" value="{{ $bulkLabel }}">
    <div id="bulk-item-ids"></div>
</form>

<div class="item-grid">
    @forelse ($items as $item)
    @php
        $ic          = $catConfig[$item->category] ?? $catConfig['other'];
        $statusClass = $item->getStatusCssClass();
        $badgeClass  = match($statusClass) { 'status-ok' => 'badge-ok', 'status-warning' => 'badge-warning', default => 'badge-critical' };
    @endphp
    <div class="item-card" style="--cat-color: {{ $ic['color'] }}">
        <div class="item-card-accent"></div>
        <label class="item-check-label" title="Select">
            <input type="checkbox" class="item-check" value="{{ $item->id }}" onchange="toggleItem({{ $item->id }}, this)">
            <span class="item-check-mark">✓</span>
        </label>
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
            @if ($item->category !== 'pet')
            <span class="status-badge {{ $badgeClass }}">
                <span class="status-dot"></span>
                {{ $item->status }}
            </span>
            @endif
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

{{-- Bulk action bar: slides up from bottom when items are selected --}}
<div id="bulk-bar" class="bulk-bar">
    <span class="bulk-count" id="bulk-count">0 items selected</span>
    <div style="flex:1"></div>
    <button type="button" class="btn-link" onclick="clearSelection()">Clear</button>
    <button type="button" class="btn btn-primary" onclick="submitBulkAction()">✓ Mark as {{ $bulkLabel }}</button>
</div>

<script>
const selectedIds = new Set();

function toggleItem(id, checkbox) {
    const card = checkbox.closest('.item-card');
    if (checkbox.checked) {
        selectedIds.add(id);
        card.classList.add('selected');
    } else {
        selectedIds.delete(id);
        card.classList.remove('selected');
    }
    updateBulkBar();
    updateSelectAllBtn();
}

function toggleSelectAll() {
    const checks = document.querySelectorAll('.item-check');
    const allSelected = checks.length > 0 && checks.length === selectedIds.size;
    checks.forEach(cb => {
        cb.checked = !allSelected;
        const card = cb.closest('.item-card');
        if (!allSelected) {
            selectedIds.add(parseInt(cb.value));
            card.classList.add('selected');
        } else {
            selectedIds.delete(parseInt(cb.value));
            card.classList.remove('selected');
        }
    });
    updateBulkBar();
    updateSelectAllBtn();
}

function updateSelectAllBtn() {
    const btn = document.getElementById('select-all-btn');
    if (!btn) return;
    const total = document.querySelectorAll('.item-check').length;
    btn.textContent = (total > 0 && total === selectedIds.size) ? 'Deselect all' : 'Select all';
}

function clearSelection() {
    document.querySelectorAll('.item-check').forEach(cb => {
        cb.checked = false;
        cb.closest('.item-card').classList.remove('selected');
    });
    selectedIds.clear();
    updateBulkBar();
    updateSelectAllBtn();
}

function updateBulkBar() {
    const bar = document.getElementById('bulk-bar');
    const countEl = document.getElementById('bulk-count');
    const n = selectedIds.size;
    if (n > 0) {
        bar.classList.add('visible');
        countEl.textContent = n + (n === 1 ? ' item' : ' items') + ' selected';
    } else {
        bar.classList.remove('visible');
    }
}

function submitBulkAction() {
    const container = document.getElementById('bulk-item-ids');
    container.innerHTML = '';
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'item_ids[]';
        input.value = id;
        container.appendChild(input);
    });
    document.getElementById('bulk-form').submit();
}
</script>
@endsection

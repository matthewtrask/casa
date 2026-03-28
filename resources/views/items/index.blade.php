@extends('layouts.app')
@use('Illuminate\Support\Facades\Storage')

@section('title', 'Trackable Items')

@section('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        color: #22863a;
        font-weight: 800;
    }

    /* ── Grid ── */
    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-top: 2.5rem;
    }

    /* ── Card ── */
    .plant-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 6px 20px rgba(0,0,0,0.07);
        display: flex;
        flex-direction: column;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .plant-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08), 0 12px 32px rgba(0,0,0,0.1);
    }

    /* ── Photo area ── */
    .card-photo-wrap {
        position: relative;
        aspect-ratio: 1 / 1;
        overflow: hidden;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        flex-shrink: 0;
    }

    .card-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }

    .card-photo-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        font-size: 5rem;
        opacity: 0.6;
    }

    /* Status badge overlaid on photo */
    .card-status-badge {
        position: absolute;
        top: 0.6rem;
        right: 0.6rem;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        white-space: nowrap;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .card-status-badge.status-ok {
        background: rgba(209,250,229,0.92);
        color: #065f46;
    }

    .card-status-badge.status-warning {
        background: rgba(254,243,199,0.93);
        color: #92400e;
    }

    .card-status-badge.status-critical {
        background: rgba(254,226,226,0.93);
        color: #991b1b;
    }

    /* ── Card body ── */
    .card-body {
        padding: 0.9rem 1rem 0.8rem;
        display: flex;
        flex-direction: column;
        flex: 1;
        gap: 0.4rem;
    }

    .card-name {
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.3;
    }

    .card-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }

    .card-tag {
        font-size: 0.72rem;
        color: #6b7280;
        background: #f3f4f6;
        padding: 0.18rem 0.45rem;
        border-radius: 4px;
        white-space: nowrap;
    }

    .card-tag-sun {
        background: #fef9c3;
        color: #854d0e;
    }

    .card-spacer { flex: 1; }

    /* Primary action button */
    .card-action-form {
        margin-top: 0.5rem;
    }

    .btn-water {
        display: block;
        width: 100%;
        background: #22863a;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.55rem 0.75rem;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
        text-align: center;
    }

    .btn-water:hover { background: #1a6b2c; }

    /* ── Card footer links ── */
    .card-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        border-top: 1px solid #f3f4f6;
        margin-top: 0.6rem;
        padding-top: 0.6rem;
    }

    .card-footer a,
    .card-footer button {
        font-size: 0.78rem;
        color: #9ca3af;
        background: none;
        border: none;
        padding: 0.15rem 0.65rem;
        cursor: pointer;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s;
        line-height: 1;
    }

    .card-footer a:hover { color: #22863a; }
    .card-footer .delete-link:hover { color: #dc2626; }

    .card-footer-sep {
        color: #e5e7eb;
        font-size: 0.75rem;
        user-select: none;
    }

    /* ── Maintenance log ── */
    .maintenance-log {
        margin-top: 2.5rem;
    }

    .log-month-group {
        margin-bottom: 2.5rem;
    }

    .log-month-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .log-entry {
        display: flex;
        align-items: flex-start;
        gap: 1.25rem;
        padding: 1rem 1.25rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 0.6rem;
        transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .log-entry:hover {
        transform: translateX(3px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.07), 0 6px 18px rgba(0,0,0,0.08);
    }

    .log-date-col {
        flex-shrink: 0;
        width: 3rem;
        text-align: center;
        padding-top: 0.1rem;
    }

    .log-date-day {
        font-size: 1.4rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1;
    }

    .log-date-month {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #9ca3af;
        font-weight: 600;
        margin-top: 0.15rem;
    }

    .log-icon {
        flex-shrink: 0;
        width: 2.25rem;
        height: 2.25rem;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-top: 0.05rem;
    }

    .log-content {
        flex: 1;
        min-width: 0;
    }

    .log-task-name {
        font-size: 0.975rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.2rem;
    }

    .log-location {
        font-size: 0.8rem;
        color: #9ca3af;
    }

    .log-notes {
        font-size: 0.83rem;
        color: #6b7280;
        margin-top: 0.35rem;
        line-height: 1.5;
        font-style: italic;
    }

    .log-actions-col {
        flex-shrink: 0;
        display: flex;
        gap: 0.4rem;
        align-items: center;
    }

    .log-actions-col a,
    .log-actions-col button {
        font-size: 0.75rem;
        color: #d1d5db;
        background: none;
        border: none;
        padding: 0.2rem 0.3rem;
        cursor: pointer;
        text-decoration: none;
        transition: color 0.15s;
        line-height: 1;
    }

    .log-actions-col a:hover { color: #22863a; }
    .log-actions-col .delete-link:hover { color: #dc2626; }

    .log-never-done {
        display: inline-block;
        font-size: 0.7rem;
        background: #fef9c3;
        color: #854d0e;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    /* ── Empty state ── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }

    .empty-state .empty-icon { font-size: 4rem; margin-bottom: 1rem; }
    .empty-state h2 { font-size: 1.5rem; color: #374151; margin-bottom: 0.75rem; }
    .empty-state p { margin-bottom: 1.5rem; }
</style>
@endsection

@section('content')

<div class="page-header">
    <h1>
        @if ($category)
            @switch($category)
                @case('plant') 🌿 Plants @break
                @case('chore') 🧹 Chores @break
                @case('maintenance') 🔧 Maintenance Log @break
                @case('pet') 🐾 Pets @break
                @case('other') 📋 Other @break
                @default All Items
            @endswitch
        @else
            All Items
        @endif
    </h1>
    <a href="{{ route('items.create', $category ? ['category' => $category] : []) }}" class="btn btn-primary">
        {{ $category === 'maintenance' ? '+ Log Entry' : '+ Add Item' }}
    </a>
</div>

@if ($items->isEmpty())
    <div class="empty-state">
        <div class="empty-icon">
            @switch($category)
                @case('plant') 🌱 @break
                @case('chore') 🧹 @break
                @case('maintenance') 🔧 @break
                @case('pet') 🐾 @break
                @default 🏠
            @endswitch
        </div>
        <h2>Nothing here yet</h2>
        <p>
            @switch($category)
                @case('plant') Add your first plant and start tracking its care. @break
                @case('chore') Add a chore to keep the house in order. @break
                @case('maintenance') Start your maintenance log by recording the first task you've done. @break
                @case('pet') Add a pet to track their feeding and care. @break
                @default Start by adding something to track.
            @endswitch
        </p>
        <a href="{{ route('items.create', $category ? ['category' => $category] : []) }}" class="btn btn-primary">
            {{ $category === 'maintenance' ? '+ Log First Entry' : '+ Add Your First Item' }}
        </a>
    </div>

@elseif ($category === 'maintenance')

    {{-- ── Maintenance log / timeline view ── --}}
    @php
        $sorted = $items->sortByDesc(fn($i) => $i->last_action_at ?? $i->created_at);
        $grouped = $sorted->groupBy(fn($i) => ($i->last_action_at ?? $i->created_at)->format('F Y'));
    @endphp

    <div class="maintenance-log">
        @foreach ($grouped as $month => $entries)
            <div class="log-month-group">
                <div class="log-month-label">{{ $month }}</div>

                @foreach ($entries as $item)
                    @php
                        $logDate = $item->last_action_at ?? $item->created_at;
                    @endphp
                    <div class="log-entry">
                        <div class="log-date-col">
                            <div class="log-date-day">{{ $logDate->format('j') }}</div>
                            <div class="log-date-month">{{ $logDate->format('M') }}</div>
                        </div>

                        <div class="log-icon">🔧</div>

                        <div class="log-content">
                            <div class="log-task-name">{{ $item->name }}</div>
                            <div class="log-location">📍 {{ $item->location }}</div>
                            @if ($item->notes)
                                <div class="log-notes">{{ $item->notes }}</div>
                            @endif
                        </div>

                        <div class="log-actions-col">
                            <a href="{{ route('items.show', $item) }}" title="View">View</a>
                            <a href="{{ route('items.edit', $item) }}" title="Edit">Edit</a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" style="display:contents">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-link"
                                        onclick="return confirm('Remove this log entry?')">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

@else
    <div class="items-grid">
        @foreach ($items as $item)
            @php
                $category_value = $item->category instanceof \App\Enums\ItemCategory
                    ? $item->category->value
                    : $item->category;
                $icon = match($category_value) {
                    'plant'       => '🌿',
                    'chore'       => '🧹',
                    'maintenance' => '🔧',
                    'pet'         => '🐾',
                    default       => '📋',
                };
                $sunLabel = match($item->sunlight_needs ?? '') {
                    'low'    => '🌑 Low light',
                    'medium' => '⛅ Indirect',
                    'high'   => '🌤 Bright',
                    'direct' => '☀️ Full sun',
                    default  => null,
                };
            @endphp

            <div class="plant-card">

                {{-- Photo / placeholder --}}
                <div class="card-photo-wrap">
                    @if ($item->image_path)
                        <img src="{{ Storage::url($item->image_path) }}"
                             alt="{{ $item->name }}"
                             class="card-photo">
                    @else
                        <div class="card-photo-placeholder">{{ $icon }}</div>
                    @endif

                    <div class="card-status-badge {{ $item->getStatusCssClass() }}">
                        {{ $item->getStatusAttribute() }}
                    </div>
                </div>

                {{-- Body --}}
                <div class="card-body">
                    <div class="card-name">{{ $item->name }}</div>

                    <div class="card-tags">
                        <span class="card-tag">📍 {{ $item->location }}</span>
                        @if ($sunLabel)
                            <span class="card-tag card-tag-sun">{{ $sunLabel }}</span>
                        @endif
                    </div>

                    <div class="card-spacer"></div>

                    @if ($item->isDue())
                        <form action="{{ route('items.action', $item) }}" method="POST" class="card-action-form">
                            @csrf
                            <input type="hidden" name="action_type" value="{{ $item->getDueLabel() }}">
                            <input type="hidden" name="performed_by" value="Manual">
                            <button type="submit" class="btn-water">
                                💧 {{ $item->getDueLabel() }} Now
                            </button>
                        </form>
                    @else
                        @if ($item->last_action_at)
                            <div style="font-size:0.78rem; color:#9ca3af; margin-top:0.4rem;">
                                Last {{ $item->getActionPastTense() }} {{ $item->last_action_at->diffForHumans() }}
                            </div>
                        @endif
                    @endif

                    {{-- Footer links --}}
                    <div class="card-footer">
                        <a href="{{ route('items.show', $item) }}">View</a>
                        <span class="card-footer-sep">|</span>
                        <a href="{{ route('items.edit', $item) }}">Edit</a>
                        <span class="card-footer-sep">|</span>
                        <form action="{{ route('items.destroy', $item) }}" method="POST" style="display:contents">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="delete-link"
                                    onclick="return confirm('Delete {{ addslashes($item->name) }}?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

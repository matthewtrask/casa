@extends('layouts.app')
@use('Illuminate\Support\Facades\Storage')

@section('title', $item->name)

@section('styles')
<style>
    /* ── Hero card ── */
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 6px 20px rgba(0,0,0,0.07);
    }

    /* ── Header: photo + name + actions ── */
    .detail-header {
        display: flex;
        gap: 1.5rem;
        align-items: flex-start;
        margin-bottom: 1.75rem;
    }

    .detail-photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        object-position: center;
        border-radius: 14px;
        flex-shrink: 0;
        box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        display: block;
    }

    .detail-photo-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 14px;
        flex-shrink: 0;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
    }

    .detail-header-info {
        flex: 1;
        min-width: 0;
    }

    .detail-name {
        font-size: 1.75rem;
        font-weight: 800;
        color: #111827;
        margin-bottom: 0.4rem;
        line-height: 1.2;
    }

    .detail-subtitle {
        font-size: 0.9rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    /* Uniform action buttons */
    .detail-actions {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }

    .detail-actions form {
        display: contents;
    }

    .detail-actions .btn {
        height: 40px;
        padding: 0 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.875rem;
        border-radius: 8px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        border: none;
    }

    .btn-outline {
        background: white;
        color: #374151;
        border: 1.5px solid #e5e7eb !important;
        text-decoration: none;
    }

    .btn-outline:hover {
        background: #f9fafb;
        border-color: #d1d5db !important;
        color: #111827;
    }

    /* ── Info grid ── */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .info-block {
        background: #f9fafb;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        border-left: 3px solid #22863a;
    }

    .info-block-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #9ca3af;
        margin-bottom: 0.35rem;
    }

    .info-block-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
    }

    /* ── Status banner ── */
    .status-banner {
        border-radius: 10px;
        padding: 0.9rem 1.25rem;
        text-align: center;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 1.25rem;
    }

    .status-banner.status-ok {
        background: #d1fae5;
        color: #065f46;
    }

    .status-banner.status-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-banner.status-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ── Notes ── */
    .notes-block {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        color: #78350f;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .notes-block strong {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #b45309;
        margin-bottom: 0.35rem;
    }

    /* ── Water now section ── */
    .water-now-section {
        border-top: 1px solid #f3f4f6;
        padding-top: 1.25rem;
    }

    .btn-water-large {
        display: block;
        width: 100%;
        background: #22863a;
        color: white;
        border: none;
        border-radius: 10px;
        padding: 0.8rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
        text-align: center;
    }

    .btn-water-large:hover { background: #1a6b2c; }

    .note-toggle {
        font-size: 0.8rem;
        color: #9ca3af;
        background: none;
        border: none;
        padding: 0.25rem 0;
        cursor: pointer;
        margin-top: 0.6rem;
        display: block;
        text-align: center;
        width: 100%;
        font-weight: normal;
        transition: color 0.15s;
    }

    .note-toggle:hover { color: #6b7280; }

    .note-field {
        display: none;
        margin-top: 0.75rem;
    }

    .note-field textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        font-size: 0.9rem;
        font-family: inherit;
        resize: vertical;
        min-height: 70px;
    }

    .note-field textarea:focus {
        outline: none;
        border-color: #22863a;
        box-shadow: 0 0 0 3px rgba(34,134,58,0.1);
    }

    /* ── History card ── */
    .history-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 6px 20px rgba(0,0,0,0.07);
    }

    .history-card h2 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 1.25rem;
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.35rem;
        top: 0.5rem;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
        border-radius: 1px;
    }

    .timeline-entry {
        position: relative;
        margin-bottom: 1.1rem;
        padding-bottom: 1.1rem;
        border-bottom: 1px solid #f9fafb;
    }

    .timeline-entry:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .timeline-dot {
        position: absolute;
        left: -1.15rem;
        top: 0.25rem;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22863a;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #d1fae5;
    }

    .timeline-performer {
        font-weight: 700;
        color: #1f2937;
        font-size: 0.9rem;
    }

    .timeline-action {
        display: inline-block;
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.1rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-left: 0.4rem;
        font-weight: 600;
    }

    .timeline-date {
        font-size: 0.78rem;
        color: #9ca3af;
        margin-top: 0.2rem;
    }

    .timeline-notes {
        font-size: 0.85rem;
        color: #6b7280;
        margin-top: 0.35rem;
        font-style: italic;
    }

    .empty-history {
        text-align: center;
        padding: 2rem;
        color: #9ca3af;
        font-size: 0.9rem;
    }

    @media (max-width: 600px) {
        .detail-header { flex-direction: column; gap: 1rem; }
        .detail-photo, .detail-photo-placeholder { width: 120px; height: 120px; }
    }
</style>
@endsection

@section('content')

<div class="detail-card">

    {{-- Header: photo + name + actions --}}
    <div class="detail-header">

        {{-- Profile photo --}}
        @if ($item->image_path)
            <img src="{{ Storage::url($item->image_path) }}"
                 alt="{{ $item->name }}"
                 class="detail-photo">
        @else
            <div class="detail-photo-placeholder">{{ $item->getCategoryEmoji() }}</div>
        @endif

        <div class="detail-header-info">
            <div class="detail-name">{{ $item->name }}</div>
            <div class="detail-subtitle">
                {{ ucfirst($item->category instanceof \App\Enums\ItemCategory ? $item->category->value : $item->category) }}
                @if ($item->location) · {{ $item->location }} @endif
            </div>

            {{-- Uniform action buttons --}}
            <div class="detail-actions">
                <a href="{{ route('items.edit', $item) }}" class="btn btn-secondary">✏️ Edit</a>
                <form action="{{ route('items.destroy', $item) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Delete {{ addslashes($item->name) }}? This cannot be undone.')">
                        🗑 Delete
                    </button>
                </form>
                <a href="{{ route('items.index', $item->category instanceof \App\Enums\ItemCategory ? ['category' => $item->category->value] : []) }}"
                   class="btn btn-outline">
                    ← Back
                </a>
            </div>
        </div>
    </div>

    {{-- Info blocks --}}
    <div class="info-grid">
        <div class="info-block">
            <div class="info-block-label">Location</div>
            <div class="info-block-value">📍 {{ $item->location }}</div>
        </div>

        <div class="info-block">
            <div class="info-block-label">{{ $item->isPlant() ? 'Watering' : 'Frequency' }}</div>
            <div class="info-block-value">
                @if ($item->isPlant()) 💧 @endif
                Every {{ $item->action_frequency_days }} {{ Str::plural('day', $item->action_frequency_days) }}
            </div>
        </div>

        @if ($item->isPlant() && $item->species)
            <div class="info-block">
                <div class="info-block-label">Species</div>
                <div class="info-block-value" style="font-style:italic; font-size:0.9rem;">{{ $item->species }}</div>
            </div>
        @endif

        @if ($item->isPlant() && $item->sunlight_needs)
            @php
                $sunlightLabel = match($item->sunlight_needs) {
                    'low'    => '🌑 Low (shade)',
                    'medium' => '⛅ Medium (indirect)',
                    'high'   => '🌤 High (bright)',
                    'direct' => '☀️ Direct (full sun)',
                    default  => ucfirst($item->sunlight_needs),
                };
            @endphp
            <div class="info-block">
                <div class="info-block-label">Sunlight</div>
                <div class="info-block-value">{{ $sunlightLabel }}</div>
            </div>
        @endif
    </div>

    {{-- Status banner --}}
    <div class="status-banner {{ $item->getStatusCssClass() }}">
        {{ $item->getStatusAttribute() }}
    </div>

    {{-- Notes --}}
    @if ($item->notes)
        <div class="notes-block">
            <strong>Notes</strong>
            {{ $item->notes }}
        </div>
    @endif

    {{-- Water / action button --}}
    @if ($item->isDue())
        <div class="water-now-section">
            <form action="{{ route('items.action', $item) }}" method="POST" id="action-form">
                @csrf
                <input type="hidden" name="action_type" value="{{ $item->getDueLabel() }}">
                <input type="hidden" name="performed_by" value="Manual">
                <textarea name="notes" id="action-notes" placeholder="Optional note…"
                          style="display:none; width:100%; border:1px solid #e5e7eb; border-radius:8px; padding:0.6rem 0.75rem; font-size:0.9rem; font-family:inherit; resize:vertical; min-height:70px; margin-bottom:0.6rem;"></textarea>
                <button type="submit" class="btn-water-large">
                    ✓ Mark as {{ $item->getActionPastTense() }}
                </button>
            </form>
            <button type="button" class="note-toggle" onclick="toggleNote(this)">
                + Add a note
            </button>
        </div>
    @endif

</div>

{{-- Action history --}}
<div class="history-card">
    <h2>📋 Action History</h2>

    @if ($item->actionLogs->isEmpty())
        <div class="empty-history">No actions logged yet.</div>
    @else
        <div class="timeline">
            @foreach ($item->actionLogs->sortByDesc('created_at') as $log)
                <div class="timeline-entry">
                    <div class="timeline-dot"></div>
                    <div>
                        <span class="timeline-performer">{{ $log->performed_by }}</span>
                        <span class="timeline-action">{{ $log->action_type }}</span>
                    </div>
                    <div class="timeline-date">{{ $log->created_at->format('M d, Y \a\t g:i A') }}</div>
                    @if ($log->notes)
                        <div class="timeline-notes">{{ $log->notes }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
function toggleNote(btn) {
    const textarea = document.getElementById('action-notes');
    const visible = textarea.style.display !== 'none';
    textarea.style.display = visible ? 'none' : 'block';
    btn.textContent = visible ? '+ Add a note' : '− Remove note';
    if (!visible) textarea.focus();
}
</script>
@endsection

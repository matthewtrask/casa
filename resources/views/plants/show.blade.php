@extends('layouts.app')

@section('title', $plant->name)

@section('styles')
<style>
    .plant-detail {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .plant-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 2rem;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 1rem;
    }

    .plant-header h1 {
        color: #333;
        font-size: 2rem;
        margin: 0;
    }

    .plant-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-block {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        border-left: 4px solid #22863a;
    }

    .info-block strong {
        display: block;
        color: #666;
        font-size: 0.85rem;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
    }

    .info-block span {
        display: block;
        font-size: 1.1rem;
        color: #333;
        font-weight: 600;
    }

    .water-status {
        padding: 1rem;
        border-radius: 4px;
        margin: 1.5rem 0;
        font-weight: 600;
        text-align: center;
        font-size: 1.1rem;
    }

    .status-ok {
        background: #d4edda;
        color: #155724;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
    }

    .status-critical {
        background: #f8d7da;
        color: #721c24;
    }

    .actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .watering-history {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .watering-history h2 {
        margin-bottom: 1.5rem;
        color: #333;
    }

    .log-entry {
        padding: 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 1rem;
        background: #f8f9fa;
    }

    .log-entry strong {
        color: #22863a;
    }

    .log-date {
        color: #666;
        font-size: 0.9rem;
    }

    .log-notes {
        color: #555;
        margin-top: 0.5rem;
        font-style: italic;
    }

    .empty-logs {
        color: #666;
        text-align: center;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="plant-detail">
    <div class="plant-header">
        <h1>🌿 {{ $plant->name }}</h1>
        <div class="actions">
            <a href="{{ route('plants.edit', $plant) }}" class="btn btn-secondary">Edit</a>
            <form action="{{ route('plants.destroy', $plant) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
            <a href="{{ route('plants.index') }}" class="btn btn-secondary">Back to Plants</a>
        </div>
    </div>

    <div class="plant-info-grid">
        <div class="info-block">
            <strong>Species</strong>
            <span>{{ $plant->species }}</span>
        </div>

        <div class="info-block">
            <strong>Location</strong>
            <span>📍 {{ $plant->location }}</span>
        </div>

        <div class="info-block">
            <strong>Sunlight Needs</strong>
            <span>☀️ {{ ucfirst($plant->sunlight_needs) }}</span>
        </div>

        <div class="info-block">
            <strong>Water Frequency</strong>
            <span>Every {{ $plant->water_frequency_days }} day(s)</span>
        </div>
    </div>

    <div class="water-status {{ $plant->getWaterStatusCssClass() }}">
        {{ $plant->getWaterStatusAttribute() }}
    </div>

    @if ($plant->notes)
        <div style="background: #f0f8ff; padding: 1rem; border-radius: 4px; margin: 1.5rem 0;">
            <strong>Notes:</strong>
            <p style="margin-top: 0.5rem; color: #333;">{{ $plant->notes }}</p>
        </div>
    @endif

    @if ($plant->isDueForWater())
        <form action="{{ route('plants.water', $plant) }}" method="POST" style="margin-top: 2rem;">
            @csrf
            <div class="form-group">
                <label for="watered_by">Watered By</label>
                <input type="text" id="watered_by" name="watered_by" value="{{ old('watered_by', 'Manual') }}" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" placeholder="How did the plant look? Any observations?">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: auto;">💧 Mark as Watered</button>
        </form>
    @endif
</div>

<div class="watering-history">
    <h2>📋 Watering History</h2>

    @if ($plant->wateringLogs->isEmpty())
        <div class="empty-logs">
            <p>No watering logs yet. Time to water this plant!</p>
        </div>
    @else
        @foreach ($plant->wateringLogs->sortByDesc('created_at') as $log)
            <div class="log-entry">
                <strong>{{ $log->watered_by }}</strong>
                <div class="log-date">{{ $log->created_at->format('M d, Y \a\t g:i A') }}</div>
                @if ($log->notes)
                    <div class="log-notes">{{ $log->notes }}</div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection

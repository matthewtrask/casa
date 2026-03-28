@extends('layouts.app')

@section('title', 'All Plants')

@section('styles')
<style>
    .plants-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .plant-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        border-left: 4px solid #22863a;
    }

    .plant-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .plant-card h3 {
        margin-bottom: 0.5rem;
        color: #333;
        font-size: 1.3rem;
    }

    .plant-card .species {
        color: #666;
        font-style: italic;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .plant-card .location {
        background: #f0f0f0;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        display: inline-block;
        font-size: 0.85rem;
        margin-bottom: 1rem;
    }

    .plant-card .sunlight {
        display: inline-block;
        margin-left: 0.5rem;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.85rem;
        background: #e8f4f8;
        color: #0c5460;
    }

    .water-status {
        padding: 0.75rem;
        border-radius: 4px;
        margin: 1rem 0;
        font-weight: 600;
        text-align: center;
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

    .plant-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .plant-actions a, .plant-actions form {
        flex: 1;
        min-width: 100px;
    }

    .plant-actions button {
        width: 100%;
    }

    .plant-actions .btn {
        width: 100%;
        text-align: center;
        padding: 0.5rem;
        font-size: 0.9rem;
    }

    .water-button {
        background: #17a2b8;
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    .water-button:hover {
        background: #138496;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .empty-state h2 {
        color: #333;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="header">
    <h1>🌿 My Plants</h1>
    <a href="{{ route('plants.create') }}" class="btn btn-primary">+ Add Plant</a>
</div>

@if ($plants->isEmpty())
    <div class="empty-state">
        <h2>No plants yet!</h2>
        <p>Start tracking your plants by adding one.</p>
        <a href="{{ route('plants.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Add Your First Plant</a>
    </div>
@else
    <div class="plants-grid">
        @foreach ($plants as $plant)
            <div class="plant-card">
                <h3>{{ $plant->name }}</h3>
                <div class="species">{{ $plant->species }}</div>

                <div>
                    <span class="location">📍 {{ $plant->location }}</span>
                    <span class="sunlight">☀️ {{ ucfirst($plant->sunlight_needs) }}</span>
                </div>

                <div class="water-status {{ $plant->getWaterStatusCssClass() }}">
                    {{ $plant->getWaterStatusAttribute() }}
                </div>

                <div style="font-size: 0.9rem; color: #666; margin: 1rem 0;">
                    @if ($plant->last_watered_at)
                        Last watered: {{ $plant->last_watered_at->diffForHumans() }}
                    @else
                        Never watered
                    @endif
                </div>

                <div class="plant-actions">
                    <a href="{{ route('plants.show', $plant) }}" class="btn btn-info">View</a>
                    <a href="{{ route('plants.edit', $plant) }}" class="btn btn-secondary">Edit</a>
                    <form action="{{ route('plants.destroy', $plant) }}" method="POST" style="flex: 1;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="width: 100%;" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>

                @if ($plant->isDueForWater())
                    <form action="{{ route('plants.water', $plant) }}" method="POST" style="margin-top: 1rem;">
                        @csrf
                        <input type="hidden" name="watered_by" value="Manual">
                        <button type="submit" class="water-button">💧 Mark as Watered</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
@endif
@endsection

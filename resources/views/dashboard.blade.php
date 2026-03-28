@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        margin: 0;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .category-card {
        background: white;
        border-radius: 14px;
        padding: 1.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 6px 20px rgba(0,0,0,0.07);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .category-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.08), 0 12px 32px rgba(0,0,0,0.1);
    }

    .category-card h2 {
        font-size: 1.5rem;
        margin-top: 0;
        margin-bottom: 1rem;
        color: #333;
    }

    .category-stats {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .stat-box {
        flex: 1;
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 4px;
        text-align: center;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #22863a;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #666;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    .items-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .items-list li {
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .items-list li:last-child {
        border-bottom: none;
    }

    .item-name {
        flex: 1;
    }

    .item-location {
        font-size: 0.85rem;
        color: #666;
        margin-left: 0.5rem;
    }

    .due-badge {
        display: inline-block;
        background: #f8d7da;
        color: #721c24;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .no-items {
        color: #666;
        font-style: italic;
        padding: 1rem 0;
        text-align: center;
    }

    .category-link {
        display: inline-block;
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background: #22863a;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .category-link:hover {
        background: #1a6b2a;
    }

    .summary-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 14px;
        margin-top: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 20px rgba(102,126,234,0.3);
    }

    .summary-box h2 {
        margin-top: 0;
        font-size: 1.8rem;
    }

    .summary-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .summary-stat {
        text-align: center;
    }

    .summary-number {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .summary-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1>🏠 Dashboard</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary">+ Add Item</a>
</div>

@php
    $totalItems = 0;
    $totalDue = 0;
    foreach ($groupedItems as $category => $data) {
        $totalItems += $data['count'];
        $totalDue += $data['due_count'];
    }
@endphp

<div class="summary-box">
    <h2>Household Status</h2>
    <div class="summary-stats">
        <div class="summary-stat">
            <div class="summary-number">{{ $totalItems }}</div>
            <div class="summary-label">Total Items</div>
        </div>
        <div class="summary-stat">
            <div class="summary-number">{{ $totalDue }}</div>
            <div class="summary-label">Items Due</div>
        </div>
        <div class="summary-stat">
            <div class="summary-number">{{ $totalItems - $totalDue }}</div>
            <div class="summary-label">Current</div>
        </div>
    </div>
</div>

<div class="category-grid">
    @php
        $categoryEmojis = [
            'plant' => '🌿',
            'chore' => '🧹',
            'maintenance' => '🔧',
            'pet' => '🐾',
            'other' => '📋',
        ];

        $categoryLabels = [
            'plant' => 'Plants',
            'chore' => 'Chores',
            'maintenance' => 'Maintenance',
            'pet' => 'Pets',
            'other' => 'Other',
        ];
    @endphp

    @foreach (['plant', 'chore', 'maintenance', 'pet', 'other'] as $category)
        <div class="category-card">
            <h2>{{ $categoryEmojis[$category] }} {{ $categoryLabels[$category] }}</h2>

            <div class="category-stats">
                <div class="stat-box">
                    <div class="stat-number">{{ $groupedItems[$category]['count'] }}</div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #dc3545;">{{ $groupedItems[$category]['due_count'] }}</div>
                    <div class="stat-label">Due</div>
                </div>
            </div>

            @if ($groupedItems[$category]['all']->isEmpty())
                <div class="no-items">No items in this category</div>
            @else
                <ul class="items-list">
                    @foreach ($groupedItems[$category]['all'] as $item)
                        <li style="@if ($item->isDue()) background-color: #fff3cd; padding: 0.5rem; margin: -0.75rem 0 0.75rem 0; border-radius: 4px; border-left: 3px solid #856404; @endif">
                            <div class="item-name">
                                <a href="{{ route('items.show', $item) }}" style="text-decoration: none; color: #333; font-weight: 500;">
                                    {{ $item->name }}
                                </a>
                                <span class="item-location">{{ $item->location }}</span>
                            </div>
                            @if ($item->isDue())
                                <span class="due-badge">DUE</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <a href="{{ route('items.index', ['category' => $category]) }}" class="category-link">View All</a>
        </div>
    @endforeach
</div>
@endsection

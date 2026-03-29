@extends('layouts.app')
@section('title', 'Home')

@section('styles')
<style>
    .greeting { margin-bottom: 28px; }
    .greeting-text {
        font-family: var(--font-display);
        font-size: 30px;
        font-weight: 600;
        letter-spacing: -.5px;
        line-height: 1.2;
    }
    .greeting-sub { font-size: 14px; color: var(--text-muted); margin-top: 4px; }
    .urgency-banner {
        background: var(--critical-soft);
        border: 1px solid #fecaca;
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .urgency-banner-icon { font-size: 28px; flex-shrink: 0; }
    .urgency-banner-text strong { display: block; color: #7f1d1d; font-size: 15px; font-weight: 600; margin-bottom: 2px; }
    .urgency-banner-text span { font-size: 13px; color: #991b1b; }
    html[data-theme="dark"] .urgency-banner { border-color: rgba(220,38,38,.35); }
    html[data-theme="dark"] .urgency-banner-text strong { color: #fca5a5; }
    html[data-theme="dark"] .urgency-banner-text span { color: #fca5a5; opacity: .8; }
    .cat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 32px;
    }
    @media (min-width: 600px) { .cat-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 900px) { .cat-grid { grid-template-columns: repeat(5, 1fr); } }
    .cat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 16px;
        text-decoration: none;
        color: var(--text);
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: box-shadow var(--ease), transform var(--ease);
        position: relative;
        overflow: hidden;
    }
    .cat-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--cat-color, #64748b);
    }
    .cat-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
    .cat-card-emoji { font-size: 26px; line-height: 1; }
    .cat-card-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .cat-card-count { font-family: var(--font-display); font-size: 32px; font-weight: 600; line-height: 1; }
    .cat-card-due { font-size: 11px; font-weight: 600; color: var(--critical); background: var(--critical-soft); padding: 2px 8px; border-radius: 999px; width: fit-content; }
    html[data-theme="dark"] .cat-card-due { color: #fca5a5; }
    .cat-card-ok { font-size: 12px; color: var(--ok); font-weight: 500; }
    .section-title { font-family: var(--font-display); font-size: 18px; font-weight: 600; margin-bottom: 14px; letter-spacing: -.2px; }
    .activity-list { display: flex; flex-direction: column; }
    .activity-item {
        background: var(--surface);
        border: 1px solid var(--border);
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
    }
    .activity-item:first-child { border-radius: var(--radius-sm) var(--radius-sm) 0 0; }
    .activity-item:last-child { border-radius: 0 0 var(--radius-sm) var(--radius-sm); }
    .activity-item:only-child { border-radius: var(--radius-sm); }
    .activity-item + .activity-item { border-top: none; }
    .activity-emoji { font-size: 18px; flex-shrink: 0; }
    .activity-name { font-weight: 500; flex: 1; }
    .activity-meta { font-size: 12px; color: var(--text-muted); }
    .empty-dash { text-align: center; padding: 56px 24px; }
    .empty-dash-icon { font-size: 52px; margin-bottom: 14px; }
    .empty-dash h3 { font-family: var(--font-display); font-size: 22px; color: var(--text); margin-bottom: 8px; }
    .empty-dash p { font-size: 14px; color: var(--text-muted); }
</style>
@endsection

@section('content')
@php
    $items = $items ?? collect();
    $catConfig = [
        'plant'       => ['label' => 'Plants',      'emoji' => '🌿', 'color' => '#16a34a'],
        'chore'       => ['label' => 'Chores',       'emoji' => '🧹', 'color' => '#3b82f6'],
        'maintenance' => ['label' => 'Maintenance',  'emoji' => '🔧', 'color' => '#f59e0b'],
        'pet'         => ['label' => 'Pets',         'emoji' => '🐾', 'color' => '#8b5cf6'],
        'other'       => ['label' => 'Other',        'emoji' => '📌', 'color' => '#64748b'],
    ];
    $hour      = (int) now()->format('G');
    $greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', auth()->user()->name)[0];
    $totalDue  = $items->filter(fn($i) => $i->isDue())->count();
@endphp

<div class="greeting">
    <div class="greeting-text">{{ $greeting }}, {{ $firstName }} 🏠</div>
    <div class="greeting-sub">{{ now()->format('l, F j') }}</div>
</div>

@if ($totalDue > 0)
<div class="urgency-banner">
    <div class="urgency-banner-icon">⚠️</div>
    <div class="urgency-banner-text">
        <strong>{{ $totalDue }} {{ Str::plural('item', $totalDue) }} need{{ $totalDue === 1 ? 's' : '' }} attention</strong>
        <span>Check each category below to see what's due.</span>
    </div>
</div>
@endif

<div class="cat-grid">
    @foreach ($catConfig as $cat => $cfg)
    @php
        $catItems = $items->where('category', $cat);
        $due      = $catItems->filter(fn($i) => $i->isDue())->count();
    @endphp
    <a href="{{ route('items.index', ['category' => $cat]) }}"
       class="cat-card"
       style="--cat-color: {{ $cfg['color'] }}">
        <div class="cat-card-emoji">{{ $cfg['emoji'] }}</div>
        <div class="cat-card-label">{{ $cfg['label'] }}</div>
        <div class="cat-card-count">{{ $catItems->count() }}</div>
        @if ($due > 0)
            <div class="cat-card-due">{{ $due }} due</div>
        @elseif ($catItems->count() > 0)
            <div class="cat-card-ok">✓ All good</div>
        @endif
    </a>
    @endforeach
</div>

@if (isset($recentLogs) && $recentLogs->count() > 0)
    <div class="section-title">Recent activity</div>
    <div class="activity-list">
        @foreach ($recentLogs as $log)
        @php $emoji = $catConfig[$log->trackableItem->category ?? 'other']['emoji'] ?? '📌'; @endphp
        <div class="activity-item">
            <span class="activity-emoji">{{ $emoji }}</span>
            <span class="activity-name">{{ $log->trackableItem->name ?? '—' }}</span>
            <span class="activity-meta">{{ $log->action_type }} · {{ $log->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
@elseif ($items->isEmpty())
    <div class="card">
        <div class="empty-dash">
            <div class="empty-dash-icon">🏡</div>
            <h3>Welcome to Casa</h3>
            <p>Start by adding your first plant, chore, or household item.</p>
            <div style="margin-top:24px">
                <a href="{{ route('items.create') }}" class="btn btn-primary btn-lg">Add your first item</a>
            </div>
        </div>
    </div>
@endif
@endsection

@extends('layouts.app')
@section('title', 'Edit ' . $item->name)

@section('styles')
<style>
    .edit-title { font-family: var(--font-display); font-size: 24px; font-weight: 600; letter-spacing: -.4px; margin-bottom: 6px; }
    .edit-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-bottom: 16px; }
    .form-card-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 16px; }
    .cat-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 13px; font-weight: 600;
        background: var(--surface-2);
        border: 1px solid var(--border);
        margin-bottom: 20px;
    }
    .freq-row { display: flex; align-items: center; gap: 12px; }
    .freq-input { width: 80px; flex-shrink: 0; }
    .freq-label { font-size: 14px; color: var(--text-muted); }
    .sunlight-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .sunlight-opt { position: relative; }
    .sunlight-opt input { position: absolute; opacity: 0; width: 0; height: 0; }
    .sunlight-opt label {
        display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 10px 6px; border: 2px solid var(--border); border-radius: var(--radius-sm);
        cursor: pointer; font-size: 11px; font-weight: 600; color: var(--text-muted);
        text-align: center; transition: all var(--ease); min-height: 64px; justify-content: center;
    }
    .sunlight-opt input:checked + label { border-color: var(--maintenance); background: var(--maintenance-soft); color: #78350f; }
    .sunlight-opt-emoji { font-size: 20px; }
    .sunlight-opt-text { text-transform: uppercase; letter-spacing: .4px; }
    .submit-row { display: flex; gap: 10px; align-items: center; margin-top: 8px; }

    .photo-upload { border: 2px dashed var(--border); border-radius: var(--radius-sm); padding: 20px; text-align: center; cursor: pointer; position: relative; background: var(--surface-2); transition: all var(--ease); }
    .photo-upload:hover { border-color: var(--text); }
    .photo-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-current { margin-bottom: 12px; }
    .photo-current img { width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--radius-sm); }
    .photo-preview { display: none; margin-top: 12px; }
    .photo-preview img { width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--radius-sm); }
</style>
@endsection

@section('content')
@php
    $catConfig = [
        'plant'       => ['emoji' => '🌿', 'label' => 'Plant'],
        'chore'       => ['emoji' => '🧹', 'label' => 'Chore'],
        'maintenance' => ['emoji' => '🔧', 'label' => 'Maintenance'],
        'pet'         => ['emoji' => '🐾', 'label' => 'Pet'],
        'other'       => ['emoji' => '📌', 'label' => 'Other'],
    ];
    $cfg = $catConfig[$item->category] ?? $catConfig['other'];
@endphp

<div class="edit-title">Edit {{ $item->name }}</div>
<div class="edit-subtitle">
    <span class="cat-badge">{{ $cfg['emoji'] }} {{ $cfg['label'] }}</span>
</div>

<form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
@csrf @method('PUT')
<input type="hidden" name="category" value="{{ $item->category }}">

<div class="form-card">
    <div class="form-card-title">Basic info</div>
    <div class="form-group">
        <label class="form-label" for="name">Name</label>
        <input type="text" id="name" name="name" class="form-input"
               value="{{ old('name', $item->name) }}" required>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="location">Location</label>
        <input type="text" id="location" name="location" class="form-input"
               value="{{ old('location', $item->location) }}">
        @error('location') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    @if ($item->isPlant())
    <div class="form-group">
        <label class="form-label" for="species">Species</label>
        <input type="text" id="species" name="species" class="form-input"
               value="{{ old('species', $item->species) }}" placeholder="e.g. Monstera deliciosa">
    </div>
    @endif
    <div class="form-group" style="margin-bottom:0">
        <label class="form-label" for="action_frequency_days">How often?</label>
        <div class="freq-row">
            <input type="number" id="action_frequency_days" name="action_frequency_days"
                   class="form-input freq-input"
                   value="{{ old('action_frequency_days', $item->action_frequency_days) }}" min="1" max="365" required>
            <span class="freq-label">days between each action</span>
        </div>
        @error('action_frequency_days') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

@if ($item->isPlant())
<div class="form-card">
    <div class="form-card-title">Sunlight needs</div>
    <div class="sunlight-grid">
        @foreach (['low' => ['🌑', 'Low'], 'medium' => ['🌤', 'Medium'], 'high' => ['☀️', 'High'], 'direct' => ['🌞', 'Direct']] as $val => [$sun_emoji, $sun_label])
        <div class="sunlight-opt">
            <input type="radio" name="sunlight_needs" id="sun-{{ $val }}" value="{{ $val }}"
                   {{ old('sunlight_needs', $item->sunlight_needs) === $val ? 'checked' : '' }}>
            <label for="sun-{{ $val }}">
                <span class="sunlight-opt-emoji">{{ $sun_emoji }}</span>
                <span class="sunlight-opt-text">{{ $sun_label }}</span>
            </label>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="form-card">
    <div class="form-card-title">Notes</div>
    <div class="form-group" style="margin-bottom:0">
        <textarea name="notes" class="form-input" rows="3">{{ old('notes', $item->notes) }}</textarea>
    </div>
</div>

<div class="form-card">
    <div class="form-card-title">📷 Photo</div>
    @if ($item->image_path)
    <div class="photo-current">
        <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
    </div>
    @endif
    <div class="photo-upload">
        <input type="file" name="photo" accept="image/*" id="photo-input" capture="environment">
        <div style="font-size:13px; color:var(--text-muted);">{{ $item->image_path ? '📷 Replace photo' : '📷 Add a photo' }}</div>
    </div>
    <div class="photo-preview" id="photo-preview">
        <img id="photo-preview-img" src="" alt="Preview">
    </div>
</div>

<div class="submit-row">
    <button type="submit" class="btn btn-primary btn-lg" style="flex:1">Save changes</button>
    <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">Cancel</a>
</div>
</form>

<script>
document.getElementById('photo-input').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('photo-preview-img').src = e.target.result;
        document.getElementById('photo-preview').style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection

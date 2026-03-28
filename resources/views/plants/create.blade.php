@extends('layouts.app')

@section('title', 'Add New Plant')

@section('content')
<div class="header">
    <h1>🌱 Add New Plant</h1>
</div>

<form action="{{ route('plants.store') }}" method="POST">
    @csrf

    <div class="form-group">
        <label for="name">Plant Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="species">Species *</label>
        <input type="text" id="species" name="species" value="{{ old('species') }}" placeholder="e.g., Monstera Deliciosa" required>
        @error('species')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="location">Location (Room) *</label>
        <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="e.g., Living Room" required>
        @error('location')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="water_frequency_days">Watering Frequency (Days) *</label>
        <input type="number" id="water_frequency_days" name="water_frequency_days" value="{{ old('water_frequency_days', 7) }}" min="1" max="365" required>
        @error('water_frequency_days')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="sunlight_needs">Sunlight Needs *</label>
        <select id="sunlight_needs" name="sunlight_needs" required>
            <option value="">-- Select Sunlight Level --</option>
            <option value="low" @selected(old('sunlight_needs') == 'low')>Low (Shade)</option>
            <option value="medium" @selected(old('sunlight_needs') == 'medium')>Medium (Partial Sun)</option>
            <option value="high" @selected(old('sunlight_needs') == 'high')>High (Bright)</option>
            <option value="direct" @selected(old('sunlight_needs') == 'direct')>Direct (Full Sun)</option>
        </select>
        @error('sunlight_needs')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="image_path">Image Path (URL)</label>
        <input type="text" id="image_path" name="image_path" value="{{ old('image_path') }}" placeholder="e.g., /images/monstera.jpg">
        @error('image_path')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
        @error('notes')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Plant</button>
        <a href="{{ route('plants.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

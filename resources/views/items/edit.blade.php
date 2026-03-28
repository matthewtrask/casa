@extends('layouts.app')

@section('title', 'Edit ' . $item->name)

@section('styles')
<style>
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .conditional-field {
        display: none;
    }

    .conditional-field.show {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="header">
    <h1>✏️ Edit Item</h1>
</div>

<form action="{{ route('items.update', $item) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="name">Item Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" required>
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" name="category" required onchange="updateConditionalFields()">
            <option value="">-- Select Category --</option>
            <option value="plant" @selected(old('category', $item->category) == 'plant')>🌿 Plant</option>
            <option value="chore" @selected(old('category', $item->category) == 'chore')>🧹 Chore</option>
            <option value="maintenance" @selected(old('category', $item->category) == 'maintenance')>🔧 Maintenance</option>
            <option value="pet" @selected(old('category', $item->category) == 'pet')>🐾 Pet</option>
            <option value="other" @selected(old('category', $item->category) == 'other')>📋 Other</option>
        </select>
        @error('category')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group conditional-field" id="species-field">
        <label for="species">Species (Plants only)</label>
        <input type="text" id="species" name="species" value="{{ old('species', $item->species) }}" placeholder="e.g., Monstera Deliciosa">
        @error('species')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="location">Location (Room) *</label>
            <input type="text" id="location" name="location" value="{{ old('location', $item->location) }}" placeholder="e.g., Living Room" required>
            @error('location')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="action_frequency_days">Action Frequency (Days) *</label>
            <input type="number" id="action_frequency_days" name="action_frequency_days" value="{{ old('action_frequency_days', $item->action_frequency_days) }}" min="1" max="365" required>
            @error('action_frequency_days')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group conditional-field" id="sunlight-field">
        <label for="sunlight_needs">Sunlight Needs (Plants only)</label>
        <select id="sunlight_needs" name="sunlight_needs">
            <option value="">-- Select Sunlight Level --</option>
            <option value="low" @selected(old('sunlight_needs', $item->sunlight_needs) == 'low')>Low (Shade)</option>
            <option value="medium" @selected(old('sunlight_needs', $item->sunlight_needs) == 'medium')>Medium (Partial Sun)</option>
            <option value="high" @selected(old('sunlight_needs', $item->sunlight_needs) == 'high')>High (Bright)</option>
            <option value="direct" @selected(old('sunlight_needs', $item->sunlight_needs) == 'direct')>Direct (Full Sun)</option>
        </select>
        @error('sunlight_needs')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="image_path">Image Path (URL)</label>
        <input type="text" id="image_path" name="image_path" value="{{ old('image_path', $item->image_path) }}" placeholder="e.g., /images/monstera.jpg">
        @error('image_path')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $item->notes) }}</textarea>
        @error('notes')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('items.show', $item) }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function updateConditionalFields() {
    const category = document.getElementById('category').value;
    const speciesField = document.getElementById('species-field');
    const sunlightField = document.getElementById('sunlight-field');

    if (category === 'plant') {
        speciesField.classList.add('show');
        sunlightField.classList.add('show');
    } else {
        speciesField.classList.remove('show');
        sunlightField.classList.remove('show');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateConditionalFields);
</script>
@endsection

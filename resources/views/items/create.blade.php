@extends('layouts.app')

@section('title', 'Add New Item')

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

    .plant-lookup-wrapper {
        position: relative;
    }

    .plant-lookup-search {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .plant-lookup-search input {
        flex: 1;
        margin: 0;
    }

    .plant-lookup-search button {
        padding: 0.6rem 1rem;
        background: #22863a;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .plant-lookup-search button:hover {
        background: #1a6b2c;
    }

    .plant-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 100;
        max-height: 220px;
        overflow-y: auto;
    }

    .plant-suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
    }

    .plant-suggestion-item:last-child {
        border-bottom: none;
    }

    .plant-suggestion-item:hover {
        background: #f0f8f0;
    }

    .plant-suggestion-item .common { font-weight: 600; color: #333; }
    .plant-suggestion-item .scientific { font-size: 0.85rem; color: #666; font-style: italic; }

    .autofill-banner {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        display: none;
    }

    .photo-id-zone {
        border: 2px dashed #c3e6cb;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        background: #f8fff9;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }

    .photo-id-zone:hover, .photo-id-zone.dragover {
        border-color: #22863a;
        background: #edfaef;
    }

    .photo-id-zone p { margin: 0.5rem 0 0; color: #555; font-size: 0.9rem; }
    .photo-id-zone .icon { font-size: 2rem; }

    .photo-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 6px;
        margin: 0.75rem auto 0;
        display: none;
    }

    .id-results {
        margin-top: 0.75rem;
        display: none;
    }

    .id-result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 0.5rem;
        background: white;
        cursor: pointer;
        transition: background 0.15s;
    }

    .id-result-item:hover { background: #f0f8f0; border-color: #22863a; }

    .id-result-item .result-name strong { display: block; color: #333; }
    .id-result-item .result-name em { font-size: 0.85rem; color: #666; }
    .id-result-item .result-score {
        font-size: 0.85rem;
        font-weight: 700;
        color: #22863a;
        white-space: nowrap;
        margin-left: 1rem;
    }

    .id-spinner {
        color: #555;
        font-size: 0.9rem;
        padding: 0.5rem;
        display: none;
    }

    .or-divider {
        text-align: center;
        color: #999;
        font-size: 0.85rem;
        margin: 0.75rem 0;
        position: relative;
    }

    .or-divider::before, .or-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 42%;
        height: 1px;
        background: #ddd;
    }

    .or-divider::before { left: 0; }
    .or-divider::after { right: 0; }
</style>
@endsection

@section('content')
<div class="header">
    <h1>➕ Add New Item</h1>
</div>

<form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" id="create-form">
    @csrf

    <div class="form-group">
        <label for="name" id="name-label">Item Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        @error('name')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="category">Category *</label>
        <select id="category" name="category" required onchange="updateConditionalFields()">
            <option value="">-- Select Category --</option>
            <option value="plant"       @selected(old('category', $preselectedCategory ?? '') == 'plant')>🌿 Plant</option>
            <option value="chore"       @selected(old('category', $preselectedCategory ?? '') == 'chore')>🧹 Chore</option>
            <option value="maintenance" @selected(old('category', $preselectedCategory ?? '') == 'maintenance')>🔧 Maintenance</option>
            <option value="pet"         @selected(old('category', $preselectedCategory ?? '') == 'pet')>🐾 Pet</option>
            <option value="other"       @selected(old('category', $preselectedCategory ?? '') == 'other')>📋 Other</option>
        </select>
        @error('category')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group conditional-field" id="species-field">
        <label>Species Lookup</label>
        <div class="autofill-banner" id="autofill-banner">
            ✅ Care details filled in — feel free to adjust!
        </div>

        {{-- Photo identification --}}
        <div class="photo-id-zone" id="photo-drop-zone" onclick="document.getElementById('photo-upload-input').click()">
            <div class="icon">📷</div>
            <strong>Snap or drop a photo to identify</strong>
            <p>PlantNet will identify the species automatically</p>
            <img id="photo-preview" class="photo-preview" src="" alt="Preview">
        </div>
        <input type="file" id="photo-upload-input" accept="image/*,.heic,.heif" capture="environment" style="display:none">

        <div class="id-spinner" id="id-spinner">🔍 Identifying plant…</div>

        <div class="id-results" id="id-results">
            <strong style="font-size:0.9rem;color:#555;">Top matches — click to use:</strong>
            <div id="id-results-list"></div>
        </div>

        <div class="or-divider">or search by name</div>

        {{-- Text search --}}
        <div class="plant-lookup-wrapper">
            <div class="plant-lookup-search">
                <input type="text" id="plant-search-input" placeholder="Search by common or scientific name…" autocomplete="off">
                <button type="button" onclick="searchPlants()">🔍 Look up</button>
            </div>
            <div class="plant-suggestions" id="plant-suggestions" style="display:none;"></div>
        </div>

        <input type="hidden" id="species" name="species" value="{{ old('species') }}">
        <small style="color:#666;">Select a match above or fill in care details manually below.</small>
        @error('species')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="location">Location (Room) *</label>
            <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="e.g., Living Room" required>
            @error('location')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group" id="frequency-group">
            <label for="action_frequency_days" id="frequency-label">Action Frequency (Days) *</label>
            <input type="number" id="action_frequency_days" name="action_frequency_days" value="{{ old('action_frequency_days', 7) }}" min="1" max="3650" required>
            @error('action_frequency_days')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="form-group conditional-field" id="sunlight-field">
        <label for="sunlight_needs">Sunlight Needs</label>
        <select id="sunlight_needs" name="sunlight_needs">
            <option value="">-- Select Sunlight Level --</option>
            <option value="low" @selected(old('sunlight_needs') == 'low')>🌑 Low (Shade)</option>
            <option value="medium" @selected(old('sunlight_needs') == 'medium')>⛅ Medium (Partial Sun)</option>
            <option value="high" @selected(old('sunlight_needs') == 'high')>🌤 High (Bright)</option>
            <option value="direct" @selected(old('sunlight_needs') == 'direct')>☀️ Direct (Full Sun)</option>
        </select>
        @error('sunlight_needs')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group conditional-field" id="last-watered-field">
        <label for="last_action_at" id="last-action-label">💧 When did you last water it? <span style="font-weight:400;color:#888;">(optional)</span></label>
        <input type="date" id="last_action_at" name="last_action_at"
               value="{{ old('last_action_at') }}"
               max="{{ date('Y-m-d') }}">
        <small style="color:#888;" id="last-action-hint">Leave blank if you're not sure — you can log it later.</small>
        @error('last_action_at')
            <div class="error-message">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="photo">Photo</label>
        <input type="file" id="photo-form-input" name="photo" accept="image/*,.heic,.heif" style="display:none">
        <div id="photo-form-preview" style="display:none; margin-bottom:0.5rem;">
            <img id="photo-form-img" src="" alt="Photo preview" style="max-width:200px; max-height:200px; border-radius:6px;">
            <br><small style="color:#666;">Photo will be saved with this plant.</small>
        </div>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('photo-form-input').click()" style="width:auto;">
            📷 Choose photo
        </button>
        @error('photo')
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
        <button type="submit" class="btn btn-primary">Add Item</button>
        <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
function updateConditionalFields() {
    const category = document.getElementById('category').value;
    const speciesField    = document.getElementById('species-field');
    const sunlightField   = document.getElementById('sunlight-field');
    const frequencyGroup  = document.getElementById('frequency-group');
    const frequencyLabel  = document.getElementById('frequency-label');
    const freqInput       = document.getElementById('action_frequency_days');
    const lastWateredField = document.getElementById('last-watered-field');
    const lastActionLabel = document.getElementById('last-action-label');
    const lastActionHint  = document.getElementById('last-action-hint');
    const nameLabel       = document.getElementById('name-label');
    const header          = document.querySelector('.header h1');

    // Reset
    speciesField.classList.remove('show');
    sunlightField.classList.remove('show');
    lastWateredField.classList.remove('show');
    frequencyGroup.style.display = '';
    freqInput.required = true;

    if (category === 'plant') {
        speciesField.classList.add('show');
        sunlightField.classList.add('show');
        lastWateredField.classList.add('show');
        frequencyLabel.textContent = '💧 Watering Frequency (Days) *';
        lastActionLabel.innerHTML  = '💧 When did you last water it? <span style="font-weight:400;color:#888;">(optional)</span>';
        lastActionHint.textContent = 'Leave blank if you\'re not sure — you can log it later.';
        nameLabel.textContent = 'Item Name *';
        if (header) header.textContent = '🌿 Add a Plant';

    } else if (category === 'maintenance') {
        // Maintenance is a log — hide frequency, show date prominently
        frequencyGroup.style.display = 'none';
        freqInput.required = false;
        freqInput.value = 3650; // far future — maintenance items never show as "due"
        lastWateredField.classList.add('show');
        lastActionLabel.innerHTML  = '📅 When did this happen? <span style="font-weight:400;color:#888;">(optional, defaults to today)</span>';
        lastActionHint.textContent = 'Leave blank to use today\'s date.';
        nameLabel.textContent = 'What was done? *';
        if (header) header.textContent = '🔧 Log Maintenance Task';

        // Default date to today if blank
        const dateInput = document.getElementById('last_action_at');
        if (!dateInput.value) dateInput.value = new Date().toISOString().split('T')[0];

    } else {
        frequencyLabel.textContent = 'Action Frequency (Days) *';
        nameLabel.textContent = 'Item Name *';
        if (header) header.textContent = '➕ Add New Item';
    }
}

// Plant lookup
let searchTimeout = null;

async function searchPlants() {
    const query = document.getElementById('plant-search-input').value.trim();
    if (query.length < 2) return;

    const suggestionsEl = document.getElementById('plant-suggestions');
    suggestionsEl.style.display = 'block';
    suggestionsEl.innerHTML = '<div style="padding:0.75rem 1rem;color:#666;">Searching…</div>';

    try {
        const res = await fetch(`/plants/search?q=${encodeURIComponent(query)}`);
        const plants = await res.json();

        if (!plants.length) {
            suggestionsEl.innerHTML = '<div style="padding:0.75rem 1rem;color:#666;">No plants found. Try a different name.</div>';
            return;
        }

        suggestionsEl.innerHTML = plants.map(p => `
            <div class="plant-suggestion-item" onclick="selectPlant(${p.id}, '${escapeJs(p.common_name)}', '${escapeJs(p.scientific_name)}')">
                <div class="common">${p.common_name}</div>
                <div class="scientific">${p.scientific_name}</div>
            </div>
        `).join('');
    } catch (e) {
        suggestionsEl.innerHTML = '<div style="padding:0.75rem 1rem;color:#c00;">Lookup failed. Check your API key.</div>';
    }
}

async function selectPlant(id, commonName, scientificName) {
    // Close dropdown
    document.getElementById('plant-suggestions').style.display = 'none';
    document.getElementById('plant-search-input').value = `${commonName} (${scientificName})`;

    // Set the hidden species field
    document.getElementById('species').value = scientificName;

    // Also set the item name if blank
    const nameInput = document.getElementById('name');
    if (!nameInput.value) nameInput.value = commonName;

    // Fetch care details
    try {
        const res = await fetch(`/plants/care/${id}`);
        const care = await res.json();

        if (care.action_frequency_days) {
            document.getElementById('action_frequency_days').value = care.action_frequency_days;
        }
        if (care.sunlight_needs) {
            document.getElementById('sunlight_needs').value = care.sunlight_needs;
        }
        if (care.notes) {
            const notesEl = document.getElementById('notes');
            if (!notesEl.value) notesEl.value = care.notes;
        }

        document.getElementById('autofill-banner').style.display = 'block';
    } catch (e) {
        console.warn('Could not fetch care details:', e);
    }
}

function escapeJs(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

// Close suggestions when clicking outside
document.addEventListener('click', (e) => {
    const wrapper = document.querySelector('.plant-lookup-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('plant-suggestions').style.display = 'none';
    }
});

// ── Photo identification ──────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    updateConditionalFields();

    document.getElementById('plant-search-input').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); searchPlants(); }
    });

    // File input change
    document.getElementById('photo-upload-input').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) handlePhotoFile(file);
    });

    // Drag and drop
    const dropZone = document.getElementById('photo-drop-zone');
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) handlePhotoFile(file);
    });
});

async function handlePhotoFile(file) {
    // Show preview using original file
    const preview = document.getElementById('photo-preview');
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';

    // Convert HEIC to JPEG client-side before uploading
    const uploadFile = await maybeConvertToJpeg(file);

    // Pre-populate the form's photo input so it saves with the plant
    populateFormPhoto(uploadFile);

    identifyPhoto(uploadFile);
}

function populateFormPhoto(file) {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('photo-form-input').files = dt.files;

    const img = document.getElementById('photo-form-img');
    img.src = URL.createObjectURL(file);
    document.getElementById('photo-form-preview').style.display = 'block';
}

// Also handle manual photo selection from the form input
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('photo-form-input').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const img = document.getElementById('photo-form-img');
            img.src = URL.createObjectURL(file);
            document.getElementById('photo-form-preview').style.display = 'block';
        }
    });
});

/**
 * If the file is HEIC/HEIF, draw it to a canvas and export as JPEG.
 * Safari on iOS can decode HEIC natively; falls back to original on failure.
 */
function maybeConvertToJpeg(file) {
    const isHeic = /\.(heic|heif)$/i.test(file.name)
        || file.type === 'image/heic'
        || file.type === 'image/heif';

    if (!isHeic) return Promise.resolve(file);

    return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);

        img.onload = () => {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            canvas.getContext('2d').drawImage(img, 0, 0);
            URL.revokeObjectURL(url);

            canvas.toBlob((blob) => {
                if (blob) {
                    const jpegName = file.name.replace(/\.(heic|heif)$/i, '.jpg');
                    resolve(new File([blob], jpegName, { type: 'image/jpeg' }));
                } else {
                    resolve(file); // fallback
                }
            }, 'image/jpeg', 0.9);
        };

        img.onerror = () => {
            URL.revokeObjectURL(url);
            resolve(file); // fallback — send as-is and let server try
        };

        img.src = url;
    });
}

async function identifyPhoto(file) {
    const spinner = document.getElementById('id-spinner');
    const resultsEl = document.getElementById('id-results');
    const resultsList = document.getElementById('id-results-list');

    spinner.style.display = 'block';
    resultsEl.style.display = 'none';
    resultsList.innerHTML = '';

    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content
        || '{{ csrf_token() }}');

    try {
        const res = await fetch('/plants/identify', { method: 'POST', body: formData });
        const data = await res.json();

        spinner.style.display = 'none';

        if (data.error) {
            resultsList.innerHTML = `<div style="color:#c00;padding:0.5rem">${data.error}</div>`;
            resultsEl.style.display = 'block';
            return;
        }

        resultsList.innerHTML = data.results.map(r => {
            const commonName = r.common_names?.[0] ?? r.scientific_name;
            const careAttr = r.care ? `data-care='${JSON.stringify(r.care)}'` : '';
            const idAttr = r.perenual_id ? `data-perenual-id="${r.perenual_id}"` : '';
            return `
                <div class="id-result-item"
                     onclick="applyIdentification('${escapeJs(r.scientific_name)}', '${escapeJs(commonName)}', this)"
                     ${careAttr} ${idAttr}>
                    <div class="result-name">
                        <strong>${commonName}</strong>
                        <em>${r.scientific_name}</em>
                    </div>
                    <div class="result-score">${r.score}% match</div>
                </div>
            `;
        }).join('');

        resultsEl.style.display = 'block';
    } catch (e) {
        spinner.style.display = 'none';
        resultsList.innerHTML = '<div style="color:#c00;padding:0.5rem">Identification failed. Please try again.</div>';
        resultsEl.style.display = 'block';
    }
}

async function applyIdentification(scientificName, commonName, el) {
    // Highlight selected immediately so it feels responsive
    document.querySelectorAll('.id-result-item').forEach(i => i.style.background = '');
    el.style.background = '#d4edda';

    // Fill in species + name
    document.getElementById('species').value = scientificName;
    document.getElementById('plant-search-input').value = `${commonName} (${scientificName})`;

    const nameInput = document.getElementById('name');
    if (!nameInput.value) nameInput.value = commonName;

    // Use pre-loaded care data if available, otherwise fetch from Perenual
    const careJson = el.getAttribute('data-care');
    const perenualId = el.getAttribute('data-perenual-id');

    if (careJson) {
        applyCareData(JSON.parse(careJson));
    } else {
        // Look up Perenual by scientific name
        try {
            el.style.opacity = '0.7';
            const searchRes = await fetch(`/plants/search?q=${encodeURIComponent(scientificName)}`);
            const plants = await searchRes.json();

            if (plants.length) {
                const careRes = await fetch(`/plants/care/${plants[0].id}`);
                const care = await careRes.json();
                applyCareData(care);
                // Cache it on the element for any re-clicks
                el.setAttribute('data-care', JSON.stringify(care));
            }
        } catch (e) {
            console.warn('Could not fetch care details from Perenual:', e);
        } finally {
            el.style.opacity = '1';
        }
    }

    document.getElementById('autofill-banner').style.display = 'block';
}

function applyCareData(care) {
    if (care.action_frequency_days) {
        document.getElementById('action_frequency_days').value = care.action_frequency_days;
    }
    if (care.sunlight_needs) {
        document.getElementById('sunlight_needs').value = care.sunlight_needs;
    }
    if (care.notes) {
        const notesEl = document.getElementById('notes');
        if (!notesEl.value) notesEl.value = care.notes;
    }
}
</script>
@endsection

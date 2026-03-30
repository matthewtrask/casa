@extends('layouts.app')
@section('title', 'Add Item')

@push('scripts')
<script type="module">
    import { heicTo } from 'https://unpkg.com/heic-to@1.4.2/dist/heic-to.js';
    window._heicTo = heicTo;
</script>
@endpush

@section('styles')
<style>
    .create-title { font-family: var(--font-display); font-size: 24px; font-weight: 600; letter-spacing: -.4px; margin-bottom: 24px; }

    .cat-picker { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 24px; }
    @media (min-width: 500px) { .cat-picker { grid-template-columns: repeat(5, 1fr); } }
    .cat-btn {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 14px 8px;
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface);
        cursor: pointer;
        font-family: var(--font-body);
        transition: all var(--ease);
        min-height: 80px;
        justify-content: center;
    }
    .cat-btn:hover { border-color: var(--text); background: var(--surface-2); }
    .cat-btn.selected { border-color: var(--cat-color, #2c2825); background: color-mix(in srgb, var(--cat-color, #2c2825) 8%, white); }
    .cat-btn-emoji { font-size: 24px; line-height: 1; }
    .cat-btn-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .4px; }

    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 16px;
    }
    .form-card-title { font-size: 13px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 16px; }

    .photo-upload {
        border: 2px dashed var(--border);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        transition: border-color var(--ease), background var(--ease);
        position: relative;
        background: var(--surface-2);
    }
    .photo-upload:hover { border-color: var(--text); background: var(--surface-3); }
    .photo-upload input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
    .photo-upload-icon { font-size: 24px; flex-shrink: 0; }
    .photo-upload-text { font-size: 14px; font-weight: 500; color: var(--text); margin-bottom: 2px; }
    .photo-upload-hint { font-size: 12px; color: var(--text-muted); }

    .photo-preview { display: none; align-items: center; gap: 12px; }
    .photo-preview img { width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-sm); flex-shrink: 0; }
    .photo-preview-change { font-size: 13px; color: var(--text-muted); cursor: pointer; text-decoration: underline; }

    /* Plant identification results */
    .identify-status { margin-top: 12px; font-size: 13px; color: var(--text-muted); display: none; align-items: center; gap: 8px; }
    .identify-spinner { width: 14px; height: 14px; border: 2px solid var(--border); border-top-color: var(--text); border-radius: 50%; animation: spin .7s linear infinite; flex-shrink: 0; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .identify-results { margin-top: 14px; display: none; flex-direction: column; gap: 8px; }
    .identify-result-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; margin-bottom: 4px; }
    .identify-card {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--surface-2);
        cursor: pointer;
        transition: border-color var(--ease), background var(--ease);
    }
    .identify-card:hover { border-color: var(--text); background: var(--surface-3); }
    .identify-card.selected { border-color: #3a7232; background: rgba(58,114,50,.08); }
    .identify-card-names { flex: 1; }
    .identify-card-common { font-size: 14px; font-weight: 600; }
    .identify-card-scientific { font-size: 12px; color: var(--text-muted); font-style: italic; }
    .identify-card-meta { text-align: right; flex-shrink: 0; }
    .identify-card-confidence { font-size: 12px; font-weight: 600; color: var(--text-muted); }
    .identify-card-water { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .identify-error { margin-top: 10px; font-size: 13px; color: #dc2626; display: none; }

    .freq-row { display: flex; align-items: center; gap: 12px; }
    .freq-input { width: 80px; flex-shrink: 0; }
    .freq-label { font-size: 14px; color: var(--text-muted); }

    .sunlight-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .sunlight-opt { position: relative; }
    .sunlight-opt input { position: absolute; opacity: 0; width: 0; height: 0; }
    .sunlight-opt label {
        display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 10px 6px;
        border: 2px solid var(--border);
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
        text-align: center;
        transition: all var(--ease);
        min-height: 64px;
        justify-content: center;
    }
    .sunlight-opt input:checked + label { border-color: var(--maintenance); background: var(--maintenance-soft); color: #78350f; }
    .sunlight-opt label:hover { border-color: var(--border-strong); }
    .sunlight-opt-emoji { font-size: 20px; }
    .sunlight-opt-text { text-transform: uppercase; letter-spacing: .4px; }

    .submit-row { display: flex; gap: 10px; align-items: center; margin-top: 8px; }

    /* Fields hidden by default, shown per-category */
    .plant-field { display: none; }
    .species-field { display: none; }
</style>
@endsection

@section('content')
<div class="create-title">Add a new item</div>

<form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" id="create-form">
@csrf

{{-- Category picker --}}
<div class="form-group">
    <label class="form-label">What are you adding?</label>
    <div class="cat-picker">
        @foreach ([
            'plant'       => ['🌿', 'Plant',       '#16a34a'],
            'chore'       => ['🧹', 'Chore',        '#3b82f6'],
            'maintenance' => ['🔧', 'Maintenance',  '#f59e0b'],
            'pet'         => ['🐾', 'Pet',          '#8b5cf6'],
            'other'       => ['📌', 'Other',        '#64748b'],
        ] as $cat => [$emoji, $label, $color])
        <button type="button"
                class="cat-btn {{ (old('category', request('category')) === $cat) ? 'selected' : '' }}"
                style="--cat-color: {{ $color }}"
                onclick="selectCategory('{{ $cat }}')">
            <span class="cat-btn-emoji">{{ $emoji }}</span>
            <span class="cat-btn-label">{{ $label }}</span>
        </button>
        @endforeach
    </div>
    <input type="hidden" name="category" id="category-input" value="{{ old('category', request('category', '')) }}">
    @error('category') <p class="form-error">{{ $message }}</p> @enderror
</div>

{{-- Basic info --}}
<div class="form-card">
    <div class="form-card-title">Basic info</div>
    <div class="form-group">
        <label class="form-label" id="name-label" for="name">Nickname</label>
        <input type="text" id="name" name="name" class="form-input"
               value="{{ old('name') }}" placeholder="e.g. Art Blakey, Miles Davis, Vacuuming" required>
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group species-field" id="species-field">
        <label class="form-label" id="species-label" for="species">Breed / Species</label>
        <input type="text" id="species" name="species" class="form-input"
               value="{{ old('species') }}" id="species-input" placeholder="e.g. Golden Retriever, Maine Coon">
    </div>
    <div class="form-group">
        <label class="form-label" id="location-label" for="location">Location</label>
        <input type="text" id="location" name="location" class="form-input"
               value="{{ old('location') }}" id="location-input" placeholder="e.g. Living room, Kitchen, Basement">
        @error('location') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label class="form-label" for="action_frequency_days">How often?</label>
        <div class="freq-row">
            <input type="number" id="action_frequency_days" name="action_frequency_days"
                   class="form-input freq-input"
                   value="{{ old('action_frequency_days', 7) }}" min="1" max="365" required>
            <span class="freq-label" id="freq-label">days between each action</span>
        </div>
        @error('action_frequency_days') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Plant-only: sunlight --}}
<div class="form-card plant-field" id="sunlight-card">
    <div class="form-card-title">Sunlight needs</div>
    <div class="sunlight-grid">
        @foreach (['low' => ['🌑', 'Low'], 'medium' => ['🌤', 'Medium'], 'high' => ['☀️', 'High'], 'direct' => ['🌞', 'Direct']] as $val => [$sun_emoji, $sun_label])
        <div class="sunlight-opt">
            <input type="radio" name="sunlight_needs" id="sun-{{ $val }}" value="{{ $val }}"
                   {{ old('sunlight_needs') === $val ? 'checked' : '' }}>
            <label for="sun-{{ $val }}">
                <span class="sunlight-opt-emoji">{{ $sun_emoji }}</span>
                <span class="sunlight-opt-text">{{ $sun_label }}</span>
            </label>
        </div>
        @endforeach
    </div>
</div>

{{-- Notes --}}
<div class="form-card">
    <div class="form-card-title">Notes</div>
    <div class="form-group" style="margin-bottom:0">
        <textarea name="notes" class="form-input" rows="3"
                  placeholder="Anything useful to remember...">{{ old('notes') }}</textarea>
    </div>
</div>

{{-- Photo --}}
<div class="form-card">
    <div class="form-card-title">📷 Photo</div>
    <div class="photo-upload" id="photo-drop">
        <input type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif,.heic,.heif,.jpg,.jpeg,.png" id="photo-input" capture="environment">
        <div class="photo-upload-icon">📷</div>
        <div>
            <div class="photo-upload-text">Tap to take a photo or upload</div>
            <div class="photo-upload-hint">JPEG · PNG · HEIC supported</div>
        </div>
    </div>
    <div class="photo-preview" id="photo-preview">
        <img id="photo-preview-img" src="" alt="Preview">
        <div>
            <div style="font-size:13px; font-weight:500; margin-bottom:4px;">Photo selected</div>
            <span class="photo-preview-change" id="photo-change-btn">Tap to change</span>
        </div>
    </div>

    {{-- Identification status + results — only shown for plant category --}}
    <div class="identify-status" id="identify-status">
        <div class="identify-spinner"></div>
        <span>Identifying plant…</span>
    </div>
    <div class="identify-error" id="identify-error"></div>
    <div class="identify-results" id="identify-results">
        <div class="identify-result-label">🔍 We think this might be…</div>
    </div>
</div>

<div class="submit-row">
    <button type="submit" class="btn btn-primary btn-lg" style="flex:1">Add Item</button>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
</div>
</form>

<script>
const catConfig = {
    plant: {
        nameLabel: 'Nickname',
        namePlaceholder: 'e.g. Art Blakey, Miles Davis, Coltrane',
        locationLabel: 'Location',
        locationPlaceholder: 'e.g. Living Room, Kitchen, Patio',
        freqLabel: 'days between waterings',
        notesPh: 'Anything useful to remember...',
        submitLabel: 'Add Plant',
        showSpecies: true,
        speciesLabel: 'Plant type',
        speciesPh: 'e.g. Snake Plant · Sansevieria trifasciata',
    },
    pet: {
        nameLabel: "Pet's name",
        namePlaceholder: 'e.g. Buddy, Whiskers, Mr. Fluffington',
        locationLabel: 'Where do they sleep?',
        locationPlaceholder: 'e.g. Bedroom, Backyard, Crate',
        freqLabel: 'days between care check-ins',
        notesPh: 'Vet info, medications, diet, favourite treats...',
        submitLabel: 'Add Pet',
        showSpecies: true,
        speciesLabel: 'Breed / Species',
        speciesPh: 'e.g. Golden Retriever, Maine Coon, Betta Fish',
    },
    chore: {
        nameLabel: 'Chore name',
        namePlaceholder: 'e.g. Vacuuming, Dishes, Laundry',
        locationLabel: 'Where?',
        locationPlaceholder: 'e.g. Living Room, Kitchen, Bathroom',
        freqLabel: 'days between each chore',
        notesPh: 'Any specific instructions...',
        submitLabel: 'Add Chore',
        showSpecies: false,
    },
    maintenance: {
        nameLabel: 'Task name',
        namePlaceholder: 'e.g. HVAC Filter, Gutters, Water Heater',
        locationLabel: 'Location',
        locationPlaceholder: 'e.g. Basement, Garage, Roof',
        freqLabel: 'days between maintenance',
        notesPh: 'Brand, model, service instructions...',
        submitLabel: 'Add Task',
        showSpecies: false,
    },
    other: {
        nameLabel: 'Name',
        namePlaceholder: 'e.g. Medication, Subscription renewal',
        locationLabel: 'Location',
        locationPlaceholder: 'e.g. Kitchen, Office',
        freqLabel: 'days between each action',
        notesPh: 'Anything useful to remember...',
        submitLabel: 'Add Item',
        showSpecies: false,
    },
};

function selectCategory(cat) {
    document.getElementById('category-input').value = cat;
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
    applyCategory(cat);
}

function applyCategory(cat) {
    const cfg = catConfig[cat] ?? catConfig['other'];

    // Name
    document.getElementById('name-label').textContent = cfg.nameLabel;
    document.getElementById('name').placeholder = cfg.namePlaceholder;

    // Location
    document.getElementById('location-label').textContent = cfg.locationLabel;
    document.getElementById('location').placeholder = cfg.locationPlaceholder;

    // Species / Breed field
    const speciesField = document.getElementById('species-field');
    if (cfg.showSpecies) {
        speciesField.style.display = '';
        document.getElementById('species-label').textContent = cfg.speciesLabel;
        document.getElementById('species').placeholder = cfg.speciesPh;
    } else {
        speciesField.style.display = 'none';
    }

    // Frequency label
    document.getElementById('freq-label').textContent = cfg.freqLabel;

    // Notes placeholder
    document.querySelector('textarea[name="notes"]').placeholder = cfg.notesPh;

    // Submit button
    document.querySelector('.submit-row .btn-primary').textContent = cfg.submitLabel;

    // Plant-only fields (sunlight card)
    const isPlant = cat === 'plant';
    document.querySelectorAll('.plant-field').forEach(el => {
        el.style.display = isPlant ? '' : 'none';
    });
}

// Init on page load
(function() {
    const val = document.getElementById('category-input').value;
    if (val) applyCategory(val);
})();

// Photo + plant identification
const photoInput      = document.getElementById('photo-input');
const photoDrop       = document.getElementById('photo-drop');
const photoPreview    = document.getElementById('photo-preview');
const photoImg        = document.getElementById('photo-preview-img');
const identifyStatus  = document.getElementById('identify-status');
const identifyError   = document.getElementById('identify-error');
const identifyResults = document.getElementById('identify-results');

function currentCategory() {
    return document.getElementById('category-input').value;
}

function resetIdentify() {
    identifyStatus.style.display  = 'none';
    identifyError.style.display   = 'none';
    identifyResults.style.display = 'none';
    // Remove previously rendered cards (keep the label div)
    identifyResults.querySelectorAll('.identify-card').forEach(c => c.remove());
}

function applyResult(card) {
    const common   = card.dataset.common;
    const scientific = card.dataset.scientific;
    const water    = card.dataset.water;
    const sunlight = card.dataset.sunlight;

    const speciesInput = document.querySelector('[name="species"]');
    const freqInput    = document.querySelector('[name="action_frequency_days"]');
    const sunInput     = document.querySelector('[name="sunlight_needs"]');

    // Combine common + scientific into the species field.
    // Leave name blank — the user gives their plant a nickname (e.g. Art Blakey).
    if (speciesInput) {
        if (common && scientific && common !== scientific) {
            speciesInput.value = common + ' · ' + scientific;
        } else {
            speciesInput.value = common || scientific || '';
        }
    }
    if (freqInput    && water)    freqInput.value   = water;
    if (sunInput     && sunlight) sunInput.value    = sunlight;

    identifyResults.querySelectorAll('.identify-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
}

async function identifyPlant(file) {
    resetIdentify();
    identifyStatus.style.display = 'flex';

    const data = new FormData();
    data.append('photo', file);
    data.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    try {
        const res  = await fetch('{{ route("plants.identify") }}', {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            body: data,
        });
        const text = await res.text();
        let json;
        try {
            json = JSON.parse(text);
        } catch {
            identifyStatus.style.display = 'none';
            identifyError.textContent = `Server returned HTTP ${res.status} — ${text.substring(0, 120)}`;
            identifyError.style.display = 'block';
            return;
        }

        identifyStatus.style.display = 'none';

        if (!res.ok || json.error) {
            const msg = json.error
                ?? json.message
                ?? Object.values(json.errors ?? {})[0]?.[0]
                ?? 'Could not identify — fill in details manually.';
            identifyError.textContent   = msg;
            identifyError.style.display = 'block';
            return;
        }

        (json.results ?? []).forEach((r, i) => {
            const common     = r.common_names?.[0] ?? r.scientific_name;
            const water      = r.care?.watering_frequency_days ?? '';
            const sunlight   = r.care?.sunlight ?? '';
            const sunMap     = { 'full sun': 'high', 'part shade': 'medium', 'full shade': 'low' };
            const sunVal     = sunMap[sunlight.toLowerCase()] ?? '';

            const card = document.createElement('div');
            card.className              = 'identify-card';
            card.dataset.common         = common;
            card.dataset.scientific     = r.scientific_name;
            card.dataset.water          = water;
            card.dataset.sunlight       = sunVal;
            card.innerHTML = `
                <div class="identify-card-names">
                    <div class="identify-card-common">${common}</div>
                    <div class="identify-card-scientific">${r.scientific_name}</div>
                </div>
                <div class="identify-card-meta">
                    <div class="identify-card-confidence">${r.score}% match</div>
                    ${water ? `<div class="identify-card-water">💧 every ${water}d</div>` : ''}
                </div>`;

            card.addEventListener('click', () => applyResult(card));

            // Auto-apply top result
            if (i === 0) {
                identifyResults.appendChild(card);
                applyResult(card);
            } else {
                identifyResults.appendChild(card);
            }
        });

        identifyResults.style.display = 'flex';
    } catch(e) {
        identifyStatus.style.display = 'none';
        identifyError.textContent    = 'Identification failed: ' + (e?.message ?? e);
        identifyError.style.display  = 'block';
    }
}

photoInput.addEventListener('change', async function() {
    let file = this.files[0];
    if (!file) return;

    // Convert HEIC/HEIF to JPEG using heic-to (libheif compiled to WebAssembly).
    // Works in all browsers — Chrome, Firefox, Safari — no native HEIC support needed.
    const isHeic = /heic|heif/i.test(file.type) || /\.(heic|heif)$/i.test(file.name);
    let heicConverted = false;
    if (isHeic) {
        try {
            identifyStatus.style.display = 'flex';
            identifyStatus.querySelector('span').textContent = 'Converting photo…';

            const heicTo = window._heicTo;
            if (!heicTo) throw new Error('heic-to library not loaded');

            // heic-to decodes HEIC but may return PNG — run through canvas to
            // guarantee JPEG output and compress to a manageable file size.
            const decoded = await heicTo({ blob: file, toType: 'image/jpeg', quality: 0.75 });
            const blob    = await new Promise((resolve, reject) => {
                const img    = new Image();
                const objUrl = URL.createObjectURL(decoded);
                img.onload = () => {
                    // Scale down if the image is very large (max 2048px on longest side)
                    let w = img.naturalWidth, h = img.naturalHeight;
                    const maxDim = 2048;
                    if (w > maxDim || h > maxDim) {
                        const ratio = Math.min(maxDim / w, maxDim / h);
                        w = Math.round(w * ratio);
                        h = Math.round(h * ratio);
                    }
                    const canvas  = document.createElement('canvas');
                    canvas.width  = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    URL.revokeObjectURL(objUrl);
                    canvas.toBlob(b => b ? resolve(b) : reject(new Error('canvas toBlob failed')), 'image/jpeg', 0.75);
                };
                img.onerror = () => { URL.revokeObjectURL(objUrl); reject(new Error('decoded image failed to load')); };
                img.src = objUrl;
            });
            file = new File([blob], file.name.replace(/\.(heic|heif)$/i, '.jpg'), { type: 'image/jpeg' });
            heicConverted = true;

            identifyStatus.style.display = 'none';
            identifyStatus.querySelector('span').textContent = 'Identifying plant…';

            // Replace the file in the input so the form also submits the converted JPEG
            const dt = new DataTransfer();
            dt.items.add(file);
            photoInput.files = dt.files;
        } catch (e) {
            identifyStatus.style.display = 'none';
            // Fall through — server will convert via ffmpeg, skip identification
            heicConverted = false;
        }
    }

    // Show thumbnail preview
    const reader = new FileReader();
    reader.onload = e => {
        photoImg.src = e.target.result;
        photoDrop.style.display    = 'none';
        photoPreview.style.display = 'flex';
    };
    reader.readAsDataURL(file);

    // Only identify if category is plant and we have a browser-converted JPEG.
    // If HEIC conversion fell through to the server we can't identify here
    // (the file in memory is still HEIC, which PlantNet won't accept).
    const canIdentify = !isHeic || heicConverted;
    if (canIdentify && (currentCategory() === 'plant' || currentCategory() === '')) {
        identifyPlant(file);
    }
});

// "Tap to change" reopens the file picker
document.getElementById('photo-change-btn').addEventListener('click', () => {
    photoInput.value = '';
    photoPreview.style.display = 'none';
    photoDrop.style.display    = 'flex';
    resetIdentify();
    photoInput.click();
});
</script>
@endsection

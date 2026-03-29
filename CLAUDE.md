# Casa — Agent Guide

A Laravel 11 house management app. Tracks plants, chores, maintenance, pets, and other recurring household tasks. Sends a daily Slack digest and identifies plants via photo upload.

## Architecture

### Single polymorphic model — NOT separate models per category

Everything is a `TrackableItem`. Category is an enum column (`plant`, `chore`, `maintenance`, `pet`, `other`). Do **not** create separate models for new categories.

```
TrackableItem        — name, location, category, action_frequency_days, last_action_at, image_path, notes, species, sunlight_needs
  └── ActionLog[]    — action_type, performed_by, notes, created_at
Setting              — key, value, label  (key-value store for app config, e.g. Slack channels)
User                 — standard Laravel auth
```

### Key files

| What | Where |
|---|---|
| Main model | `app/Models/TrackableItem.php` |
| Action logging | `app/Models/ActionLog.php` |
| CRUD controller | `app/Http/Controllers/TrackableItemController.php` |
| Action log controller | `app/Http/Controllers/ActionLogController.php` |
| Plant identification API | `app/Http/Controllers/PlantLookupController.php` |
| Settings controller | `app/Http/Controllers/SettingsController.php` |
| Daily Slack digest | `app/Console/Commands/SendDailyDigest.php` |
| PlantNet service | `app/Services/PlantNetService.php` |
| Perenual service | `app/Services/PerenualService.php` |
| Settings model | `app/Models/Setting.php` |
| Main layout | `resources/views/layouts/app.blade.php` |
| Items views | `resources/views/items/` |

### Routes

```
GET  /dashboard              → TrackableItemController@dashboard
GET  /items                  → index (filterable by ?category=plant)
POST /items                  → store
GET  /items/create           → create
GET  /items/{item}           → show
PUT  /items/{item}           → update
DEL  /items/{item}           → destroy
POST /items/{item}/action    → ActionLogController@store
GET  /settings               → SettingsController@index
POST /settings               → SettingsController@update
GET  /plants/search?q=       → PlantLookupController@search  (Perenual autocomplete)
GET  /plants/care/{id}       → PlantLookupController@care    (Perenual care details)
POST /plants/identify        → PlantLookupController@identify (PlantNet + Perenual)
```

## Running locally

Uses Laravel Sail (Docker). See `DEVELOPER_GUIDE.md` for full setup.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan casa:send-digest   # test Slack
```

## Slack integration

Uses the **bot token** approach (`SLACK_API_TOKEN=xoxb-...`), not incoming webhooks. Posts via `chat.postMessage`. Per-category channels are configured in the `settings` table (keys: `slack_channel_default`, `slack_channel_plant`, `slack_channel_chore`, etc.). Falls back to default channel if a category channel is not set.

## Photo uploads

Photos are stored to the configured filesystem disk (DO Spaces in production). HEIC files are converted to JPEG via Imagick in `TrackableItemController::storePhoto()`. The same conversion happens in `PlantNetService::prepareImage()` before sending to the PlantNet API.

**Validation rule for photos:** always use `mimes:jpeg,jpg,png,gif,webp,heic,heif` — never use the `image` rule, which excludes HEIC.

## Plant identification flow

1. User selects a photo on the create form (plant category)
2. JS POSTs to `/plants/identify` with the file
3. `PlantLookupController@identify` sends to PlantNet → gets top 3 species matches
4. Top result is enriched with Perenual care data (watering frequency, sunlight)
5. Results are shown as cards; clicking one auto-fills the form fields

## Legacy code — do not use or extend

The following files exist but are dead and scheduled for deletion. They predate the `TrackableItem` refactor:

- `app/Models/Plant.php`
- `app/Models/WateringLog.php`
- `app/Models/ModelFactory.php`
- `app/Http/Controllers/PlantController.php`
- `app/Http/Controllers/WateringController.php`
- `app/Console/Commands/SendPlantReminders.php`
- `app/Notifications/PlantCareNotification.php`
- `resources/views/plants/` (entire directory)
- `database/factories/PlantFactory.php`
- `database/factories/WateringLogFactory.php`

The migrations named `create_plants_table` and `create_watering_logs_table` actually create `trackable_items` and `action_logs` — the names are misleading but the tables are correct.

## Environment variables

```env
SLACK_API_TOKEN=xoxb-...          # Slack bot token
PERENUAL_API_KEY=...              # Plant search + care data
PLANTNET_API_KEY=...              # Plant photo identification
DO_SPACES_*                       # DigitalOcean Spaces for photo storage
```

## Adding a new category

1. Add value to the `category` enum in a new migration
2. Add to validation `in:` list in `TrackableItemController`
3. Add emoji + label to `TrackableItem::getCategoryEmoji()` and `getDueLabel()`
4. Add to `$categoryConfig` in `SendDailyDigest`
5. Add a `slack_channel_{category}` row to the `settings` table migration seed

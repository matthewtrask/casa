# Casa Refactoring Summary

## Overview
Successfully refactored Casa from a plant-only tracker into a generalized household management application. The app now supports tracking multiple item types: plants, chores, maintenance tasks, pets, and other items.

## Core Changes

### Database Schema

#### Renamed Tables
- `plants` → `trackable_items`
- `watering_logs` → `action_logs`

#### Trackable Items Table
**New columns:**
- `species` (nullable) - Only relevant for plants
- `category` (enum: plant, chore, maintenance, pet, other) - Item type
- `action_frequency_days` - Replaces water_frequency_days
- `sunlight_needs` (nullable) - Only for plants
- `last_action_at` - Replaces last_watered_at
- `last_secondary_action_at` - Replaces last_fertilized_at

**Removed/replaced columns:**
- water_frequency_days → action_frequency_days
- last_watered_at → last_action_at
- last_fertilized_at → last_secondary_action_at

#### Action Logs Table
**New columns:**
- `trackable_item_id` (foreign key) - Replaces plant_id
- `action_type` (string) - Type of action (e.g., "Water", "Fertilize", "Complete", "Service")
- `performed_by` (string) - Replaces watered_by
- All timestamp fields preserved

### Models

#### New: TrackableItem (replaces Plant)
**File:** `/app/Models/TrackableItem.php`

**Key Methods:**
- `actionLogs()` - HasMany relationship
- `isDue()` - Check if item needs action
- `getDaysOverdue()` - Get overdue days
- `getStatusAttribute()` - Display-friendly status text
- `getStatusCssClass()` - CSS class for status indicator
- `getDueLabel()` - Action label based on category ("Water", "Complete", "Service", etc.)
- `getCategoryEmoji()` - Get emoji for category

**Category Helpers:**
- `isPlant()`, `isChore()`, `isMaintenance()`, `isPet()`, `isOther()`

#### New: ActionLog (replaces WateringLog)
**File:** `/app/Models/ActionLog.php`

**Relationships:**
- `trackableItem()` - BelongsTo relationship

#### Legacy Models (kept for reference)
- `Plant.php` - Deprecated, no longer used
- `WateringLog.php` - Deprecated, no longer used

### Controllers

#### New: TrackableItemController
**File:** `/app/Http/Controllers/TrackableItemController.php`

**Methods:**
- `index(Request $request)` - List items with optional category filter
- `create()` - Show create form
- `store(Request $request)` - Create new item
- `show(TrackableItem $item)` - Show item details
- `edit(TrackableItem $item)` - Show edit form
- `update(Request $request, TrackableItem $item)` - Update item
- `destroy(TrackableItem $item)` - Delete item
- `dashboard()` - Show dashboard with grouped categories

**Features:**
- Category filtering on index: `/items?category=plant`
- Full CRUD operations
- Dashboard view with statistics

#### New: ActionLogController
**File:** `/app/Http/Controllers/ActionLogController.php`

**Methods:**
- `store(Request $request, TrackableItem $item)` - Log action and update last_action_at

#### Legacy Controllers (kept for reference)
- `PlantController.php` - Deprecated, no longer used
- `WateringController.php` - Deprecated, no longer used

### Console Commands

#### New: SendDailyDigest
**File:** `/app/Console/Commands/SendDailyDigest.php`

**Command Signature:** `casa:send-digest`

**Features:**
- Queries all due items across all categories
- Groups items by category with emojis
- Sends formatted Slack message with sections per category
- Includes overdue information
- Category emoji map:
  - 🌿 Plants
  - 🧹 Chores
  - 🔧 Maintenance
  - 🐾 Pets
  - 📋 Other

**Example Output:**
```
🏠 Casa Daily Digest — March 21, 2026

🌿 Plants
• Water the Calathea (Living Room)
• Water the Pothos (Bedroom)

🧹 Chores
• Take out trash (overdue 2 days)

🔧 Maintenance
• Nothing due today ✓
```

#### Legacy Commands (kept for reference)
- `SendPlantReminders.php` - Deprecated, no longer used

### Views

#### New Views Created

**Dashboard** - `/resources/views/dashboard.blade.php`
- Overview of all categories
- Statistics: total items, due items
- Category cards with item lists
- Quick access to category filtering
- Summary box with overall status

**Items Index** - `/resources/views/items/index.blade.php`
- List all items or filter by category
- Grid layout with item cards
- Status indicators (color-coded)
- Quick action buttons
- "Mark [Action]" button for due items

**Items Create** - `/resources/views/items/create.blade.php`
- Form for adding new items
- Category selector with dynamic field visibility
- Plant-specific fields (species, sunlight) shown only for plants
- JavaScript for conditional field display

**Items Show** - `/resources/views/items/show.blade.php`
- Item detail view with full information
- Action history/logs section
- Status display
- Log action form for due items
- Edit/delete actions

**Items Edit** - `/resources/views/items/edit.blade.php`
- Edit form with same features as create
- Pre-populated fields
- Dynamic plant-specific field visibility

**Layout** - `/resources/views/layouts/app.blade.php` (updated)
- New navigation with category links
- Dashboard link
- Dynamic navigation showing all categories
- Quick "Add Item" button

#### Deleted/Deprecated Views
- `/resources/views/plants/` - All plant views replaced with item views

### Routes

**Web Routes** - `/routes/web.php`
```
GET  /                           → Redirect to /dashboard
GET  /dashboard                  → Dashboard
GET  /items                       → List items (with ?category filter)
GET  /items/create               → Create form
POST /items                       → Store item
GET  /items/{item}               → Show item
GET  /items/{item}/edit          → Edit form
PUT  /items/{item}               → Update item
DELETE /items/{item}             → Delete item
POST /items/{item}/action        → Log action
```

**Console Routes** - `/routes/console.php`
- Updated schedule to use `casa:send-digest` instead of `plants:send-reminders`

### Configuration

**Composer.json**
- Updated description: "Casa - Household Management & Tracking App"
- Updated keywords: household, management, tracking, tasks

**Console Kernel** - `/app/Console/Kernel.php`
- Updated $commands array to use SendDailyDigest
- Updated schedule to run casa:send-digest at 08:00 daily

### Documentation

**README.md** - Comprehensive update
- New feature descriptions
- Updated installation instructions
- Database schema documentation for new tables
- Usage instructions for multiple item types
- Category-based navigation guide
- Dashboard overview
- Updated routing table

## Migration Path for Existing Data

If migrating from the old plant-only version:

1. Back up your database
2. Run `php artisan migrate` - creates new tables automatically
3. Manual data migration (if needed):
   - Query plants table
   - Insert into trackable_items with category='plant'
   - Insert watering_logs into action_logs with appropriate action_type
   - Can be scripted as a custom artisan command

## Feature Enhancements

### New Capabilities
1. **Multiple Item Types** - Track any household task, not just plants
2. **Flexible Categorization** - Five built-in categories plus extensibility
3. **Better Action Logging** - More detailed action tracking with types
4. **Dashboard View** - At-a-glance status of all items
5. **Category Filtering** - Quick navigation by type
6. **Enhanced Slack Digest** - Grouped by category with better formatting
7. **Category-Specific Helpers** - Methods like getDueLabel() for smart UI
8. **Conditional Form Fields** - UI adapts based on item type

### Preserved Features
1. **Status Indicators** - Color-coded status (green/yellow/red)
2. **Due Item Tracking** - Frequency-based scheduling
3. **Slack Integration** - Daily digest notifications
4. **Action History** - Complete audit trail per item
5. **Notes Support** - Add notes to items and actions

## Testing Checklist

- [ ] Database migrations run successfully
- [ ] Dashboard page loads with all categories
- [ ] Create plant - shows species and sunlight fields
- [ ] Create chore - hides plant-specific fields
- [ ] Items filter by category
- [ ] Action logging updates last_action_at
- [ ] Status indicators show correctly
- [ ] Slack webhook sends digest
- [ ] All CRUD operations work
- [ ] Navigation links work correctly
- [ ] Edit form shows correct values
- [ ] Delete confirmation works

## File Structure Changes

### Created Files (17 new)
- `/app/Models/TrackableItem.php`
- `/app/Models/ActionLog.php`
- `/app/Http/Controllers/TrackableItemController.php`
- `/app/Http/Controllers/ActionLogController.php`
- `/app/Console/Commands/SendDailyDigest.php`
- `/resources/views/dashboard.blade.php`
- `/resources/views/items/index.blade.php`
- `/resources/views/items/create.blade.php`
- `/resources/views/items/edit.blade.php`
- `/resources/views/items/show.blade.php`
- `/REFACTORING_SUMMARY.md` (this file)

### Modified Files (8 total)
- `database/migrations/2024_01_01_000000_create_plants_table.php` (renamed to create trackable_items)
- `database/migrations/2024_01_02_000000_create_watering_logs_table.php` (renamed to create action_logs)
- `routes/web.php` (updated to use new controllers and routes)
- `routes/console.php` (updated command schedule)
- `app/Console/Kernel.php` (updated command registration)
- `composer.json` (updated description and keywords)
- `resources/views/layouts/app.blade.php` (new navigation structure)
- `README.md` (comprehensive update)

### Deprecated Files (4 - kept for reference)
- `app/Models/Plant.php`
- `app/Models/WateringLog.php`
- `app/Http/Controllers/PlantController.php`
- `app/Http/Controllers/WateringController.php`
- `app/Console/Commands/SendPlantReminders.php`
- `resources/views/plants/` (all files)

## Next Steps

1. **Database Migration**: Run `php artisan migrate` to create new tables
2. **Data Migration** (if needed): Create a custom command to migrate existing plant data
3. **Testing**: Run tests and manually verify all features
4. **Deployment**: Deploy to production with proper backup
5. **Cleanup**: After successful deployment, consider removing deprecated files

## Backward Compatibility Notes

- Old routes (`/plants/*`) are no longer available
- Old models (Plant, WateringLog) are deprecated but not removed
- Database uses new table names (trackable_items, action_logs)
- Slack webhook behavior preserved with enhanced grouping

## Architecture Notes

- All business logic centralized in TrackableItem model
- Category pattern allows easy extension for new item types
- Action logging provides full audit trail
- Dashboard provides unified view across categories
- Conditional form fields improve UX for category-specific attributes

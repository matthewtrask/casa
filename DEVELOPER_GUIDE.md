# Casa Developer Guide

## Quick Reference

### File Locations

**Models:**
- `app/Models/TrackableItem.php` - Main model for all trackable items
- `app/Models/ActionLog.php` - Action history log model

**Controllers:**
- `app/Http/Controllers/TrackableItemController.php` - CRUD + dashboard
- `app/Http/Controllers/ActionLogController.php` - Action logging

**Console Commands:**
- `app/Console/Commands/SendDailyDigest.php` - Daily Slack digest

**Views:**
- `resources/views/items/index.blade.php` - List items
- `resources/views/items/create.blade.php` - Create form
- `resources/views/items/edit.blade.php` - Edit form
- `resources/views/items/show.blade.php` - Item details
- `resources/views/dashboard.blade.php` - Dashboard
- `resources/views/layouts/app.blade.php` - Main layout

**Database:**
- `database/migrations/2024_01_01_000000_create_plants_table.php` - Creates trackable_items
- `database/migrations/2024_01_02_000000_create_watering_logs_table.php` - Creates action_logs

### Common Tasks

#### Adding a New Category

1. Update enum in migration (if modifying database):
   ```php
   $table->enum('category', ['plant', 'chore', 'maintenance', 'pet', 'other', 'new_category']);
   ```

2. Add validation in TrackableItemController:
   ```php
   'category' => 'required|in:plant,chore,maintenance,pet,other,new_category',
   ```

3. Add helper method to TrackableItem:
   ```php
   public function isNewCategory(): bool {
       return $this->category === 'new_category';
   }
   ```

4. Add to category maps in SendDailyDigest and dashboard:
   ```php
   $categoryEmojis['new_category'] = '🎉';
   $categoryLabels['new_category'] = 'New Category';
   ```

5. Update navigation in layouts/app.blade.php if needed

#### Logging a New Action Type

No code changes needed - ActionLog accepts any string for `action_type`. However, for consistency:

1. Document in model:
   ```php
   // Supported action types: Water, Fertilize, Complete, Service, Feed, Care, Groom, Vet, etc.
   ```

2. Consider adding constants to TrackableItem or ActionLog models

#### Querying Due Items

```php
// Get all due items
$due = TrackableItem::all()->filter(fn($item) => $item->isDue());

// Get due items by category
$duePlants = TrackableItem::where('category', 'plant')
    ->get()
    ->filter(fn($item) => $item->isDue());

// Check if specific item is due
if ($item->isDue()) {
    // Item needs action
}

// Get days overdue (negative if not overdue)
$daysOverdue = $item->getDaysOverdue();
```

#### Creating an Item Programmatically

```php
TrackableItem::create([
    'name' => 'My Plant',
    'species' => 'Monstera Deliciosa',
    'location' => 'Living Room',
    'category' => 'plant',
    'action_frequency_days' => 7,
    'sunlight_needs' => 'medium',
    'notes' => 'Likes humid environment',
]);
```

#### Logging an Action

```php
$item = TrackableItem::find($id);

$item->actionLogs()->create([
    'action_type' => 'Water',
    'performed_by' => 'User Name',
    'notes' => 'Added fertilizer',
]);

$item->update(['last_action_at' => now()]);
```

#### Sending Slack Message Manually

```php
php artisan casa:send-digest
```

### Route Structure

```
/                           GET    → Dashboard redirect
/dashboard                  GET    → Dashboard overview
/items                      GET    → List all items (filterable)
/items?category=plant       GET    → Filter by category
/items/create               GET    → Create form
/items                      POST   → Store new item
/items/{id}                 GET    → Show item details
/items/{id}/edit            GET    → Edit form
/items/{id}                 PUT    → Update item
/items/{id}                 DELETE → Delete item
/items/{id}/action          POST   → Log action
```

### Model Methods Reference

#### TrackableItem

**Relationships:**
- `actionLogs()` - HasMany ActionLog

**Status Methods:**
- `isDue()` → bool - Check if item needs action
- `getDaysOverdue()` → int - Days overdue (negative if not due)
- `getStatusAttribute()` → string - Display text
- `getStatusCssClass()` → string - CSS class (status-ok/warning/critical)
- `getDueLabel()` → string - Action label per category
- `getCategoryEmoji()` → string - Emoji for category

**Category Helpers:**
- `isPlant()` → bool
- `isChore()` → bool
- `isMaintenance()` → bool
- `isPet()` → bool
- `isOther()` → bool

**Attributes:**
- `$fillable` - name, species, location, action_frequency_days, category, sunlight_needs, last_action_at, last_secondary_action_at, notes, image_path
- `$casts` - last_action_at, last_secondary_action_at as datetime

#### ActionLog

**Relationships:**
- `trackableItem()` - BelongsTo TrackableItem

**Attributes:**
- `$fillable` - trackable_item_id, action_type, performed_by, notes
- `$casts` - created_at, updated_at as datetime

### Validation Rules

#### TrackableItem Store/Update

```php
[
    'name' => 'required|string|max:255',
    'species' => 'nullable|string|max:255',
    'location' => 'required|string|max:255',
    'action_frequency_days' => 'required|integer|min:1|max:365',
    'category' => 'required|in:plant,chore,maintenance,pet,other',
    'sunlight_needs' => 'nullable|in:low,medium,high,direct',
    'notes' => 'nullable|string',
    'image_path' => 'nullable|string|max:255',
]
```

#### ActionLog Store

```php
[
    'action_type' => 'required|string|max:255',
    'performed_by' => 'required|string|max:255',
    'notes' => 'nullable|string',
]
```

### Blade Template Helpers

#### Getting Category Info

```blade
{{ $item->getCategoryEmoji() }}  <!-- Get emoji for category -->
{{ $item->getDueLabel() }}       <!-- Get action label ("Water", "Complete", etc.) -->
{{ $item->isPlant() ? 'Yes' : 'No' }}  <!-- Category check -->
```

#### Status Display

```blade
<div class="{{ $item->getStatusCssClass() }}">
    {{ $item->getStatusAttribute() }}
</div>
```

#### Conditional Fields

```blade
@if ($item->isPlant())
    <p>Species: {{ $item->species }}</p>
    <p>Sunlight: {{ $item->sunlight_needs }}</p>
@endif
```

### JavaScript Utilities

**Create/Edit Form - Dynamic Fields:**
```javascript
// updateConditionalFields() is called onchange of category select
// Shows species and sunlight fields only for plants
```

### Database Queries

```php
// All items
TrackableItem::all();

// By category
TrackableItem::where('category', 'plant')->get();

// Due items
TrackableItem::get()->filter(fn($item) => $item->isDue());

// With action logs
TrackableItem::with('actionLogs')->get();

// Order by due date
TrackableItem::all()
    ->sortBy(fn($item) => $item->last_action_at?->addDays($item->action_frequency_days))
    ->reverse();
```

### Testing

```bash
# Run tests
php artisan test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Reset database
php artisan migrate:refresh --seed

# Test Slack digest
php artisan casa:send-digest
```

### Environment Configuration

```env
# Required for Slack notifications
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=casa
DB_USERNAME=root
DB_PASSWORD=password
```

### Extending the System

#### Adding a New Item Type Attribute

1. Create migration to add column to trackable_items
2. Add to TrackableItem $fillable
3. Add to $casts if needed
4. Add to validation rules in controller
5. Add form field to create/edit views with conditional display
6. Update documentation

#### Custom Actions

To support custom action types beyond the standard ones:

1. ActionLog accepts any string in action_type field
2. Consider adding helper method to TrackableItem if category-specific
3. Update SendDailyDigest if needs special display

#### Adding Features to Dashboard

1. Update `dashboard()` method in TrackableItemController
2. Add data to $groupedItems array or create new variable
3. Update dashboard.blade.php template
4. Update CSS in dashboard styles section

### Performance Notes

- actionLogs are loaded with `with('actionLogs')` on show page
- Dashboard loads all items per category (consider pagination for large datasets)
- Status checks (isDue) are evaluated in PHP, not database queries
- For large datasets, consider scope queries: `TrackableItem::whereDue()`

### Debugging

```php
// Get item with all relationships
$item = TrackableItem::with('actionLogs')->find($id);

// Check if due
dd($item->isDue(), $item->getStatusAttribute());

// View action history
dd($item->actionLogs->map(fn($log) => [
    'type' => $log->action_type,
    'by' => $log->performed_by,
    'date' => $log->created_at,
]));
```

### Common Issues & Solutions

**Issue: Form fields not showing for plants**
- Check JavaScript updateConditionalFields() is called on DOMContentLoaded
- Verify form has correct category select id

**Issue: Slack webhook not sending**
- Check SLACK_WEBHOOK_URL in .env
- Verify webhook URL is correct and active in Slack workspace
- Check Laravel logs: storage/logs/laravel.log
- Test manually: php artisan casa:send-digest

**Issue: Items not showing as due**
- Verify action_frequency_days is set correctly
- Check last_action_at is null or a valid timestamp
- Test with: php artisan tinker → TrackableItem::find(1)->isDue()

**Issue: Migration fails**
- Ensure database exists and is accessible
- Check db credentials in .env
- Drop and recreate database if needed

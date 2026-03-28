# Casa Refactoring - Deployment Steps

## Pre-Deployment

### 1. Backup Existing Database
```bash
# MySQL backup
mysqldump -u root -p casa > casa_backup_$(date +%Y%m%d_%H%M%S).sql

# Or using Laravel
php artisan db:backup  # if you have a backup package installed
```

### 2. Review Changes
- [ ] Read REFACTORING_SUMMARY.md for all changes
- [ ] Review DEVELOPER_GUIDE.md for architecture
- [ ] Verify all files are in place (see file listing below)
- [ ] Check .env configuration

### 3. Install Dependencies (if needed)
```bash
composer install --no-dev  # For production
```

## Deployment Steps

### Step 1: Backup & Branch
```bash
# Create backup branch in git
git branch backup-before-refactor

# Or commit your current state
git add .
git commit -m "Pre-refactoring backup"
```

### Step 2: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Run Migrations
```bash
# This creates the new tables
php artisan migrate

# Verify tables were created
php artisan tinker
>>> DB::table('trackable_items')->count()
>>> DB::table('action_logs')->count()
```

### Step 4: Migrate Data (Optional - if upgrading from plant-only version)

If you have existing plant data and want to migrate it:

```bash
# This is a manual process - create a migration or artisan command
# Option 1: Using tinker
php artisan tinker
```

```php
// Migrate plants to trackable_items
\App\Models\Plant::all()->each(function($plant) {
    \App\Models\TrackableItem::create([
        'name' => $plant->name,
        'species' => $plant->species,
        'location' => $plant->location,
        'category' => 'plant',
        'action_frequency_days' => $plant->water_frequency_days,
        'sunlight_needs' => $plant->sunlight_needs,
        'last_action_at' => $plant->last_watered_at,
        'last_secondary_action_at' => $plant->last_fertilized_at,
        'notes' => $plant->notes,
        'image_path' => $plant->image_path,
    ]);
});

// Migrate watering logs to action logs
\App\Models\WateringLog::all()->each(function($log) {
    \App\Models\ActionLog::create([
        'trackable_item_id' => $log->plant_id, // Assumes 1:1 mapping
        'action_type' => 'Water',
        'performed_by' => $log->watered_by,
        'notes' => $log->notes,
        'created_at' => $log->created_at,
        'updated_at' => $log->updated_at,
    ]);
});
```

### Step 5: Test Core Functionality

```bash
# Test artisan command
php artisan casa:send-digest

# Test Laravel serve
php artisan serve
```

Then in browser:
- [ ] Visit http://localhost:8000/dashboard
- [ ] Verify dashboard loads
- [ ] Verify navigation shows all categories
- [ ] Create a test plant item
- [ ] Create a test chore item
- [ ] Verify fields show/hide based on category
- [ ] Log an action for test item
- [ ] Verify last_action_at updates
- [ ] Edit test item
- [ ] Delete test item

### Step 6: Test Slack Integration (if configured)

```bash
# Send test digest manually
php artisan casa:send-digest

# Check Slack workspace for message
```

If no items are due:
```php
php artisan tinker
$item = \App\Models\TrackableItem::first();
$item->update(['last_action_at' => now()->subDays(10)]);
```

Then run command again.

### Step 7: Verify Scheduler Setup

```bash
# Check if schedule is registered
php artisan schedule:list

# Should see: casa:send-digest 0 8 * * *

# For production, ensure cron is set up:
# * * * * * cd /path/to/casa && php artisan schedule:run >> /dev/null 2>&1
```

### Step 8: Clear and Warm Cache

```bash
php artisan optimize:clear
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Post-Deployment

### Verification Checklist

- [ ] Dashboard accessible and loads correctly
- [ ] All navigation links work
- [ ] Items CRUD operations work
- [ ] Category filtering works
- [ ] Status indicators display correctly
- [ ] Slack digest sends successfully
- [ ] Database queries perform well
- [ ] No 404 or 500 errors in logs
- [ ] Application logs are clean

### Monitoring

```bash
# Check application logs for errors
tail -f storage/logs/laravel.log

# Monitor with specific patterns
tail -f storage/logs/laravel.log | grep -i error
```

## Rollback Plan

If something goes wrong:

### Option 1: Rollback Database Only
```bash
# Drop new tables
php artisan migrate:rollback --step=2

# Restore from backup
mysql -u root -p casa < casa_backup_YYYYMMDD_HHMMSS.sql

# Restart old version
git checkout your-old-branch
```

### Option 2: Full Rollback
```bash
# Revert to backup branch
git reset --hard backup-before-refactor

# Restore database
mysql -u root -p casa < casa_backup_YYYYMMDD_HHMMSS.sql

# Clear cache
php artisan cache:clear

# Restart application
php artisan serve
```

## Troubleshooting

### Issue: Migration Error
```bash
# Check migration status
php artisan migrate:status

# Check database
mysql -u root -p
> use casa;
> show tables;

# If tables exist, you can skip:
php artisan migrate --force
```

### Issue: 404 Routes
```bash
# Clear route cache
php artisan route:clear

# Verify routes
php artisan route:list | grep items
```

### Issue: Blank Dashboard
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test in tinker
php artisan tinker
>>> \App\Models\TrackableItem::count()
>>> dd(\App\Models\TrackableItem::all());
```

### Issue: Slack Not Sending
```bash
# Check webhook URL
php artisan tinker
>>> env('SLACK_WEBHOOK_URL')

# Test command manually
php artisan casa:send-digest --verbose

# Check logs for errors
tail -f storage/logs/laravel.log
```

## Performance Considerations

For large datasets (1000+ items):

1. Add pagination to index view
```php
$items = $query->paginate(25);
```

2. Optimize queries with select()
```php
TrackableItem::select('id', 'name', 'category', 'last_action_at')->get()
```

3. Add indexes to migrations
```php
$table->index('category');
$table->index('last_action_at');
```

4. Cache dashboard data
```php
$groupedItems = cache()->remember('dashboard_items', 300, function() {
    // dashboard logic
});
```

## Documentation Links

- **REFACTORING_SUMMARY.md** - Complete list of changes
- **DEVELOPER_GUIDE.md** - Developer reference
- **README.md** - User documentation

## Completion Checklist

- [ ] Database migrated successfully
- [ ] All routes working
- [ ] Dashboard accessible
- [ ] CRUD operations functional
- [ ] Category filtering works
- [ ] Slack digest configured and tested
- [ ] Console scheduler verified
- [ ] Logs checked for errors
- [ ] Performance acceptable
- [ ] Team notified of changes
- [ ] Documentation updated
- [ ] Backup created and verified

## Support

If you encounter issues:

1. Check logs: `storage/logs/laravel.log`
2. Review DEVELOPER_GUIDE.md for common issues
3. Test in tinker: `php artisan tinker`
4. Check database: `php artisan migrate:status`

## Final Notes

- The old Plant and WateringLog models are deprecated but left for reference
- You can delete old views in `resources/views/plants/` after confirming migration is successful
- Old controllers (PlantController, WateringController) can be deleted
- Old command (SendPlantReminders) can be deleted
- Consider running `php artisan optimize` for production

Estimated deployment time: 15-30 minutes (including testing)

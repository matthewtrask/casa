# Casa - Useful Commands Reference

## Application Setup

```bash
# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Create database
mysql -u root -p -e "CREATE DATABASE casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed

# Run dev server
php artisan serve
```

## Database Commands

```bash
# Show migration status
php artisan migrate:status

# Run specific migration
php artisan migrate --path=database/migrations/2024_01_01_000000_create_plants_table.php

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Refresh all migrations (drops all tables and re-runs)
php artisan migrate:refresh

# Refresh with seeding
php artisan migrate:refresh --seed
```

## Artisan Commands

```bash
# List all available commands
php artisan list

# Show command details
php artisan help plants:send-reminders

# Run the plant reminders command
php artisan plants:send-reminders

# Display Laravel version
php artisan --version
```

## Caching & Cache Clearing

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear
```

## Development Utilities

```bash
# Show all routes
php artisan route:list

# Show routes with 'plant' in name
php artisan route:list | grep plant

# Interactive shell (Tinker)
php artisan tinker

# Monitor database connection
php artisan db:monitor

# Check environment
php artisan env
```

## Testing

```bash
# Run all tests
php artisan test

# Run tests with output
php artisan test --verbose

# Run specific test file
php artisan test tests/Feature/PlantControllerTest.php

# Generate coverage report
php artisan test --coverage
```

## Scheduling (for development)

```bash
# Run the scheduler once (useful for testing)
php artisan schedule:run

# List all scheduled tasks
php artisan schedule:list
```

## Production Deployment

```bash
# Clear config cache (improves performance)
php artisan config:cache

# Cache all routes (improves performance)
php artisan route:cache

# Cache views (improves performance)
php artisan view:cache

# Optimize class loading
php artisan optimize

# Run migrations in production
php artisan migrate --force

# Run seeders in production
php artisan db:seed --force
```

## Tinker - Interactive Shell Examples

```bash
# Start tinker
php artisan tinker

# Inside tinker, you can:

# Get all plants
App\Models\Plant::all()

# Get first plant
$plant = App\Models\Plant::first()

# Check if plant needs water
$plant->isDueForWater()

# Get water status
$plant->getWaterStatusAttribute()

# Get watering logs
$plant->wateringLogs

# Create a plant
App\Models\Plant::create([
    'name' => 'Test Plant',
    'species' => 'Test Species',
    'location' => 'Test Room',
    'water_frequency_days' => 7,
    'sunlight_needs' => 'medium'
])

# Log a watering
$plant->wateringLogs()->create([
    'watered_by' => 'John',
    'notes' => 'Plant looked healthy'
])

# Update last watered
$plant->update(['last_watered_at' => now()])

# Count plants
App\Models\Plant::count()

# Delete a plant
$plant->delete()

# Exit tinker
exit
```

## Cron Setup for Scheduler

### Linux/Mac

```bash
# Edit crontab
crontab -e

# Add this line (runs scheduler every minute):
* * * * * cd /path/to/casa && php artisan schedule:run >> /dev/null 2>&1

# View crontab
crontab -l

# Remove crontab
crontab -r
```

### Windows Task Scheduler

Create a task that runs every minute:
```
C:\path\to\php.exe "C:\path\to\casa\artisan" schedule:run
```

## Common Troubleshooting Commands

```bash
# Check database connection
php artisan db:monitor

# Show environment
php artisan env

# Verify migrations
php artisan migrate:status

# Check Laravel logs
tail -f storage/logs/laravel.log

# List all model relationships
php artisan tinker
>>> App\Models\Plant::all()->map(fn($p) => [$p->id, $p->name, $p->wateringLogs()->count()])

# Test Slack webhook
php artisan plants:send-reminders

# Verify routes
php artisan route:list --name=plants
```

## Docker Commands (if using Docker)

```bash
# Build container
docker-compose build

# Start services
docker-compose up -d

# Stop services
docker-compose down

# Run migrations in container
docker-compose exec app php artisan migrate

# Run tinker in container
docker-compose exec app php artisan tinker

# View logs
docker-compose logs -f app
```

## File Permissions

```bash
# Make artisan executable
chmod +x artisan

# Fix storage permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Fix everything
sudo chown -R www-data:www-data /path/to/casa
sudo chmod -R 755 /path/to/casa
sudo chmod -R 755 storage bootstrap/cache
```

## Environment Configuration

```bash
# Copy example env
cp .env.example .env

# Edit env file
nano .env  # or use your preferred editor

# Key env variables:
# APP_NAME=Casa
# APP_ENV=local
# APP_DEBUG=true
# APP_URL=http://localhost:8000
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=casa
# DB_USERNAME=root
# DB_PASSWORD=your_password
# SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK
```

## Performance Optimization

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache view
php artisan view:cache

# Optimize autoloading
composer dump-autoload -o

# Clear all caches and re-optimize
php artisan optimize
```

---

For more information, run any command with `--help`:
```bash
php artisan migrate --help
php artisan route:list --help
```

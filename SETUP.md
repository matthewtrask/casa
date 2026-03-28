# Casa - Quick Start Guide

## What You Got

A complete, production-ready Laravel 11 household plant care tracker with:

✅ Full CRUD operations for plants and watering logs
✅ Clean, minimal HTML UI (no frontend framework bloat)
✅ MySQL database with migrations
✅ Slack webhook notifications
✅ Console command for daily reminders
✅ Color-coded plant status indicators
✅ Watering history tracking

---

## 5-Minute Setup

### 1. Install Composer Dependencies

```bash
cd /sessions/zealous-lucid-dirac/mnt/personal/casa
composer install
```

This will install Laravel 11, Guzzle, and all dependencies.

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=casa
DB_USERNAME=root
DB_PASSWORD=yourpassword
```

### 3. Create Database

```bash
mysql -u root -p -e "CREATE DATABASE casa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Run Migrations

```bash
php artisan migrate
```

This creates the `plants` and `watering_logs` tables.

### 5. (Optional) Seed Sample Data

```bash
php artisan db:seed
```

Creates 3 sample plants (Monstera, Snake Plant, Pothos).

### 6. Start Development Server

```bash
php artisan serve
```

Visit **http://localhost:8000**

---

## Features Tour

### Web Interface

- **Home** (`/plants`) - List all plants with status
- **Add Plant** - Create new plant with details
- **Plant Details** - View watering history, mark as watered
- **Edit Plant** - Update plant information
- **Delete Plant** - Remove plant from tracking

### Status Indicators

| Color | Meaning |
|-------|---------|
| 🟢 Green | Plant is OK, no watering needed yet |
| 🟡 Yellow | Overdue by 1-3 days |
| 🔴 Red | Overdue by 4+ days |

### Console Commands

Test the reminder command:

```bash
php artisan plants:send-reminders
```

(Requires `SLACK_WEBHOOK_URL` in `.env`)

---

## Slack Notifications Setup

### 1. Create Slack Incoming Webhook

1. Go to your Slack workspace
2. Settings → Apps → Custom Integrations → Incoming Webhooks
3. Create New Webhook
4. Select channel (e.g., #notifications)
5. Copy webhook URL

### 2. Add to .env

```
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### 3. Set Up Scheduler

The app sends daily reminders at 8 AM using Laravel's scheduler.

**On Linux/Mac (using cron):**

```bash
crontab -e
```

Add this line:

```
* * * * * cd /sessions/zealous-lucid-dirac/mnt/personal/casa && php artisan schedule:run >> /dev/null 2>&1
```

**On Windows:**

Use Windows Task Scheduler to run this command every minute:

```
php artisan schedule:run
```

### 4. Test It

```bash
php artisan plants:send-reminders
```

You should receive a Slack message listing plants that need water today.

---

## Database Schema

### plants table

```sql
CREATE TABLE plants (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  species VARCHAR(255),
  location VARCHAR(255),
  water_frequency_days INT,
  sunlight_needs ENUM('low', 'medium', 'high', 'direct'),
  last_watered_at TIMESTAMP NULL,
  last_fertilized_at TIMESTAMP NULL,
  notes LONGTEXT NULL,
  image_path VARCHAR(255) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### watering_logs table

```sql
CREATE TABLE watering_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  plant_id BIGINT NOT NULL,
  watered_by VARCHAR(255),
  notes LONGTEXT NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
);
```

---

## File Structure

```
casa/
├── app/
│   ├── Models/
│   │   ├── Plant.php                 # Plant model with isDueForWater()
│   │   └── WateringLog.php           # Watering log model
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PlantController.php   # Full CRUD
│   │   │   └── WateringController.php
│   │   ├── Kernel.php
│   │   └── Middleware/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── SendPlantReminders.php # Daily reminders
│   │   └── Kernel.php
│   ├── Notifications/
│   │   └── PlantCareNotification.php
│   └── Providers/
├── config/
│   ├── app.php
│   ├── database.php
│   ├── services.php
│   └── cache.php
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000000_create_plants_table.php
│   │   └── 2024_01_02_000000_create_watering_logs_table.php
│   ├── factories/
│   │   ├── PlantFactory.php
│   │   └── WateringLogFactory.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   ├── web.php                       # Web routes
│   ├── api.php
│   └── console.php                   # Scheduler config
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php             # Base layout
│   └── plants/
│       ├── index.blade.php           # List plants
│       ├── create.blade.php          # Add plant form
│       ├── show.blade.php            # Plant details
│       └── edit.blade.php            # Edit form
├── public/
│   ├── index.php                     # Entry point
│   └── .htaccess                     # Apache rewrite rules
├── bootstrap/
│   └── app.php                       # Bootstrap file
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── .env.example                      # Environment template
├── .gitignore
├── composer.json
├── artisan                           # CLI tool
├── README.md                         # Full documentation
└── SETUP.md                          # This file
```

---

## Troubleshooting

### "SQLSTATE[HY000]: General error: 1030"

Usually a MySQL connection issue. Check:
- MySQL is running: `mysql -u root -p`
- Database exists: `SHOW DATABASES;`
- `.env` credentials are correct

### "Class 'SendPlantReminders' not found"

Rebuild autoloader:
```bash
composer dump-autoload
php artisan clear-cache
```

### Slack webhook not sending

1. Test manually: `php artisan plants:send-reminders`
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify webhook URL in `.env`
4. Ensure plants are due for water (check database)

### Permission denied errors

Make storage writable:
```bash
chmod -R 755 storage bootstrap/cache
```

---

## Common Tasks

### Reset Everything

```bash
php artisan migrate:refresh --seed
```

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Check Routes

```bash
php artisan route:list
```

### Test Database Connection

```bash
php artisan db:monitor
```

---

## Next Steps

1. Add plants via the web interface
2. Set up Slack notifications
3. Configure the scheduler to run daily
4. Customize styling in `resources/views/layouts/app.blade.php`
5. Extend models with additional features

---

## Code Highlights

### Plant Model - Check if Due for Water

```php
public function isDueForWater(): bool
{
    if ($this->last_watered_at === null) {
        return true;
    }

    $dueDate = $this->last_watered_at->addDays($this->water_frequency_days);
    return $dueDate->isPast() || $dueDate->isToday();
}
```

### Send Reminders Command

```php
php artisan plants:send-reminders
```

Finds all plants due for water and posts to Slack webhook.

### Routes

```php
Route::resource('plants', PlantController::class);
Route::post('/plants/{plant}/water', [WateringController::class, 'store'])->name('plants.water');
```

---

## Support

Refer to `README.md` for full documentation.

Happy plant parenting! 🌿

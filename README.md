# Casa - Household Management & Tracking App

Casa is a generalized Laravel 11 application for managing and tracking various household items and tasks. Originally a plant care tracker, it has been refactored to support multiple categories: plants, chores, maintenance tasks, pets, and more. It includes automated Slack notifications for items that need attention.

## Features

- **Unified Item Tracking**: Track multiple types of household items with a generalized system
  - Plants (with species and sunlight needs tracking)
  - Chores (recurring household tasks)
  - Maintenance (home maintenance reminders)
  - Pets (feeding, grooming, vet visits)
  - Other custom items
- **Category-based Filtering**: View items by category or all at once
- **Action Logging**: Track all actions performed on each item with notes
- **Visual Status Indicators**: Color-coded status (green/yellow/red) for item care status
- **Dashboard View**: Overview of all categories with due items highlighted
- **Daily Slack Digest**: Automated daily notifications grouped by category
- **Responsive Web Interface**: Clean, simple, and intuitive UI

## Requirements

- PHP 8.2 or higher
- MySQL 5.7 or higher
- Composer
- Node.js & npm (optional, for frontend assets)

## Installation

### 1. Clone or Download the Repository

```bash
git clone <repository-url> casa
cd casa
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment Variables

```bash
cp .env.example .env
```

Edit `.env` and configure:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=casa
DB_USERNAME=root
DB_PASSWORD=yourpassword

APP_KEY=  # Will be generated in next step
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Create Database

Create a new MySQL database:

```bash
mysql -u root -p -e "CREATE DATABASE casa;"
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. (Optional) Seed Sample Data

```bash
php artisan db:seed
```

## Usage

### Start the Development Server

```bash
php artisan serve
```

Then visit `http://localhost:8000` in your browser.

### Managing Items

- **View Dashboard**: See overview of all categories and due items
- **Add an Item**: Click "Add Item" in navigation or dashboard
  - Select category (Plant, Chore, Maintenance, Pet, Other)
  - Plant-specific fields (species, sunlight) appear only for plants
- **View Item Details**: Click on an item to see full details and action history
- **Edit an Item**: Click "Edit" on item details page
- **Log an Action**: Click "Mark [Action]" button (updates last_action_at)
- **Delete an Item**: Click "Delete" (confirm when prompted)

### Item Status Indicators

- **Green (OK)**: Item doesn't need action yet
- **Yellow (Warning)**: Item is overdue by 1-3 days
- **Red (Critical)**: Item is overdue by 4+ days

### Category-based Navigation

The navigation bar includes quick links to each category:
- **Dashboard**: Overall view of all items grouped by category
- **Plants**: All plant items
- **Chores**: All chore items
- **Maintenance**: All maintenance items
- **Pets**: All pet items
- **+ Add Item**: Quick add button

### Slack Notifications

To enable Slack notifications:

1. Create a Slack webhook URL:
   - Go to your Slack workspace settings
   - Create an Incoming Webhook for the channel where you want notifications
   - Copy the webhook URL

2. Add the webhook URL to your `.env`:
   ```
   SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK
   ```

3. Set up the scheduler (see below)

### Scheduling Daily Digest

Casa includes a console command that sends a Slack notification each morning (8 AM) listing all items that need attention, grouped by category.

#### On Linux/Mac (using cron):

1. Open your crontab:
   ```bash
   crontab -e
   ```

2. Add this line to run Laravel's scheduler every minute:
   ```bash
   * * * * * cd /path/to/casa && php artisan schedule:run >> /dev/null 2>&1
   ```

3. Replace `/path/to/casa` with the actual path to your Casa installation.

#### On Windows:

Create a scheduled task to run:
```bash
php artisan schedule:run
```
every minute using Windows Task Scheduler.

#### Manual Testing:

To test the digest command manually:
```bash
php artisan casa:send-digest
```

## Database Schema

### Trackable Items Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string | Item name (e.g., "Calathea Plant") |
| species | string | Species/type (e.g., "Calathea Orbifolia") - nullable, for plants only |
| location | string | Room location in house |
| action_frequency_days | int | Days between required actions |
| category | enum | plant/chore/maintenance/pet/other |
| sunlight_needs | enum | low/medium/high/direct - nullable, for plants only |
| last_action_at | timestamp | When item was last actioned |
| last_secondary_action_at | timestamp | For secondary actions (e.g., fertilizing) |
| notes | text | Additional care/task notes |
| image_path | string | URL to item image |
| created_at | timestamp | Record creation time |
| updated_at | timestamp | Last update time |

### Action Logs Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| trackable_item_id | bigint | Foreign key to trackable_items |
| action_type | string | Type of action (e.g., "Water", "Complete", "Service") |
| performed_by | string | Person who performed action |
| notes | text | Notes about the action |
| created_at | timestamp | When action was performed |
| updated_at | timestamp | Last update time |

## API Routes

### Web Routes

- `GET /` - Redirect to dashboard
- `GET /dashboard` - Show dashboard
- `GET /items` - List all items (filterable by category)
- `GET /items?category=plant` - List items by category
- `GET /items/create` - Show create form
- `POST /items` - Create a new item
- `GET /items/{id}` - Show item details
- `GET /items/{id}/edit` - Show edit form
- `PUT /items/{id}` - Update an item
- `DELETE /items/{id}` - Delete an item
- `POST /items/{id}/action` - Log an action for an item

### Console Commands

- `php artisan casa:send-digest` - Send daily Slack digest
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Seed sample data

## Models & Controllers

### Models

- **TrackableItem**: Main model for all trackable household items
  - Methods: isDue(), getDaysOverdue(), getStatusAttribute(), getStatusCssClass(), getDueLabel()
  - Category helpers: isPlant(), isChore(), isMaintenance(), isPet(), isOther()
  - Relationship: hasMany(ActionLog)

- **ActionLog**: Individual action records for trackable items
  - Relationship: belongsTo(TrackableItem)

### Controllers

- **TrackableItemController**: Full CRUD operations with category filtering
  - Methods: index(), create(), store(), show(), edit(), update(), destroy(), dashboard()

- **ActionLogController**: Handle action logging
  - Methods: store() - logs action and updates last_action_at

## Troubleshooting

### Database Connection Error

Ensure MySQL is running and `.env` has correct database credentials:
```bash
php artisan migrate --verbose
```

### Slack Webhook Not Working

1. Verify webhook URL in `.env` is correct
2. Test with: `php artisan casa:send-digest`
3. Check Laravel logs: `storage/logs/laravel.log`

### Storage Directory Permissions

If you get permission errors, ensure storage directory is writable:
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## Development

### Running Tests

```bash
php artisan test
```

### Clearing Cache/Data

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Reset Database

```bash
php artisan migrate:refresh --seed
```

## Migration from Plant-only Version

If migrating from the original plant-only version:

1. Backup your database
2. Run migrations (tables will be renamed automatically)
3. Data migration can be done via a custom command if needed
4. Update any custom code referencing Plant/WateringLog models to use TrackableItem/ActionLog

## License

MIT License - feel free to use this project for personal or commercial purposes.

## Support

For issues or questions, please create an issue in the repository or contact the maintainers.

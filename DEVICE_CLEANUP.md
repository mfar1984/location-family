# Device Cleanup Feature

## Overview
Automatically marks devices as inactive or deletes them if they haven't sent location pings for a specified period (default: 7 days).

## Features

### 1. Automatic Scheduled Cleanup
- Runs daily at 2:00 AM
- Marks devices inactive after 7 days of no pings
- Configured in `app/Console/Kernel.php`

### 2. Manual CLI Command
Run cleanup manually with various options:

```bash
# Mark devices inactive after 7 days (default)
php artisan devices:cleanup-inactive

# Custom inactivity period (e.g., 14 days)
php artisan devices:cleanup-inactive --days=14

# Permanently delete instead of marking inactive
php artisan devices:cleanup-inactive --delete

# Dry run (preview without making changes)
php artisan devices:cleanup-inactive --dry-run
```

### 3. Admin API Endpoint
**POST** `/api/admin/devices/cleanup-inactive`

**Headers:**
- Cookie: laravel_session (admin user)

**Body (JSON):**
```json
{
  "days": 7,
  "delete": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Inactive devices marked as inactive",
  "days": 7,
  "action": "marked_inactive",
  "devices_processed": 2,
  "devices": [
    {
      "device_id": "device-old-1234567890",
      "name": "Old Device",
      "user_email": "user@example.com",
      "last_seen": "2026-03-01T10:30:00Z"
    }
  ]
}
```

## Device Model Methods

### `isInactive($days = 7)`
Check if device hasn't sent pings for specified days.

### `getLastPingTime()`
Get the timestamp of the last location ping.

## Configuration

Edit `app/Console/Kernel.php` to change schedule:
```php
// Run daily at 2:00 AM
$schedule->command('devices:cleanup-inactive --days=7')
         ->dailyAt('02:00');

// Or run weekly on Sundays
$schedule->command('devices:cleanup-inactive --days=14')
         ->weekly()
         ->sundays()
         ->at('03:00');
```

## Notes
- Inactive devices won't appear in dashboard
- Location pings are preserved when marking inactive
- Use `--delete` flag to permanently remove devices and their pings
- Requires Laravel scheduler to be running: `php artisan schedule:work`

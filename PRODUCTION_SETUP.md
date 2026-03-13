# Production Setup Guide - Laravel Scheduler

## Ringkasan
Untuk automatic cleanup devices dalam production, kita perlu setup Laravel Scheduler. Ada 2 cara:

---

## Cara 1: Cron Job (Paling Mudah)

### Step 1: Login ke Server
```bash
ssh user@hajj.sibu.org.my
```

### Step 2: Edit Crontab
```bash
crontab -e
```

### Step 3: Tambah Line Ini
```bash
* * * * * cd /var/www/hajj.sibu.org.my && php artisan schedule:run >> /dev/null 2>&1
```

**Nota:**
- `* * * * *` = Run setiap minit
- Ganti `/var/www/hajj.sibu.org.my` dengan path sebenar project
- Laravel akan handle semua scheduled tasks automatically

### Step 4: Verify Setup
```bash
# Check cron job
crontab -l

# Check scheduled tasks
cd /var/www/hajj.sibu.org.my
php artisan schedule:list
```

---

## Cara 2: Supervisor (Recommended untuk Production)

Supervisor lebih reliable dan automatic restart kalau ada error.

### Step 1: Install Supervisor
```bash
sudo apt-get install supervisor
```

### Step 2: Create Config File
```bash
sudo nano /etc/supervisor/conf.d/laravel-scheduler.conf
```

### Step 3: Paste Configuration
```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=php /var/www/hajj.sibu.org.my/artisan schedule:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/hajj.sibu.org.my/storage/logs/scheduler.log
stopwaitsecs=3600
```

### Step 4: Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler
```

### Step 5: Check Status
```bash
sudo supervisorctl status laravel-scheduler
```

---

## Scheduled Tasks Yang Akan Run

Selepas setup, tasks ini akan run automatically:

### 1. Device Cleanup (Daily at 2:00 AM)
```
php artisan devices:cleanup-inactive --days=7
```
- Marks devices inactive after 7 days no pings
- Runs every day at 2:00 AM

### 2. Test Data Refresh (Every 3 Minutes)
```
php artisan test:refresh-data
```
- **REMOVE THIS IN PRODUCTION!**
- Hanya untuk development/demo

---

## Remove Test Data Refresh untuk Production

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // REMOVE THIS BLOCK IN PRODUCTION:
    // $schedule->command('test:refresh-data')
    //          ->everyThreeMinutes()
    //          ->withoutOverlapping()
    //          ->runInBackground();
    
    // KEEP THIS:
    $schedule->command('devices:cleanup-inactive --days=7')
             ->dailyAt('02:00')
             ->withoutOverlapping();
}
```

---

## Verify Scheduler is Working

### Check Logs
```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/scheduler.log  # If using Supervisor
```

### Manual Test
```bash
# Run scheduler manually to test
php artisan schedule:run

# Check if cleanup command works
php artisan devices:cleanup-inactive --dry-run
```

---

## Troubleshooting

### Cron Job Not Running?
```bash
# Check cron service status
sudo service cron status

# Restart cron service
sudo service cron restart

# Check cron logs
grep CRON /var/log/syslog
```

### Supervisor Not Working?
```bash
# Check supervisor status
sudo supervisorctl status

# Restart supervisor
sudo service supervisor restart

# View logs
sudo tail -f /var/log/supervisor/supervisord.log
```

### Permissions Issues?
```bash
# Fix Laravel permissions
cd /var/www/hajj.sibu.org.my
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Summary

**Untuk Production, pilih salah satu:**

1. **Cron Job** (mudah, standard)
   - Tambah 1 line dalam crontab
   - Run `* * * * * cd /path/to/project && php artisan schedule:run`

2. **Supervisor** (recommended, lebih reliable)
   - Install supervisor
   - Create config file
   - Start service

**Kedua-dua cara akan run automatic cleanup daily at 2:00 AM.**

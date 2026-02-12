# Post-Installation Guide

## Immediate Actions Required

### 1. Change Default Passwords
```bash
# Login to the application with admin credentials
# Navigate to Profile > Change Password
# Update passwords for all test users
```

### 2. Configure Email Settings

Update `.env` file with your email configuration:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@akuh.com"
MAIL_FROM_NAME="AMP System"
```

### 3. Import Remaining Templates

Run the import command to load all templates from your Excel file:
```bash
php artisan amp:import-templates /path/to/Access_matrix_Review_Allocation.xlsx
```

### 4. Configure Two-Factor Authentication

Enable 2FA for all admin users:
1. Login as admin
2. Navigate to Settings > Security
3. Enable Two-Factor Authentication
4. Scan QR code with authenticator app

### 5. Set Up Scheduled Tasks

Add to your crontab:
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### 6. Configure Queue Worker

For production, set up supervisor to run queue workers:
```bash
sudo nano /etc/supervisor/conf.d/amp-worker.conf
```

Add:
```ini
[program:amp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
```

## Verification Steps

### Test Database Connection
```bash
php artisan tinker
>>> \DB::connection()->getPdo();
>>> \App\Models\Department::count();
```

### Test API Endpoints
```bash
# Get departments
curl https://access-management-portal.test/api/departments

# Login (get token)
curl -X POST https://access-management-portal.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@akuh.com","password":"password"}'
```

### Run Tests
```bash
php artisan test
```

## Production Deployment Checklist

- [ ] Update APP_ENV to 'production' in .env
- [ ] Set APP_DEBUG to false
- [ ] Configure proper database credentials
- [ ] Set up SSL certificate
- [ ] Configure production email settings
- [ ] Set up backup schedule
- [ ] Configure monitoring (Telescope in production mode)
- [ ] Set up error tracking (Sentry/Bugsnag)
- [ ] Configure rate limiting
- [ ] Set up CDN for assets
- [ ] Enable queue workers
- [ ] Configure cron jobs
- [ ] Set proper file permissions
- [ ] Configure firewall rules
- [ ] Set up log rotation

## Troubleshooting

### Clear All Caches
```bash
php artisan optimize:clear
```

### Reset Database
```bash
php artisan migrate:fresh --seed
```

### Fix Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review error messages carefully
- Consult Laravel documentation: https://laravel.com/docs
- Contact ICT support team

---

**Installation Date:** $(date)
**Version:** 1.0.0
# Quick Reference Commands

## Daily Development
```bash
# Start development
cd ~/Herd/access-management-portal
npm run dev

# Run queue worker (separate terminal)
php artisan queue:work

# Watch logs
tail -f storage/logs/laravel.log
```

## Database
```bash
# Reset database
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=DepartmentSeeder

# Check migrations status
php artisan migrate:status

# Create new migration
php artisan make:migration create_table_name
```

## Testing
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter AccessRequestTest

# Run with coverage
php artisan test --coverage
```

## Cache Management
```bash
# Clear all caches
php artisan optimize:clear

# Cache routes (production)
php artisan route:cache

# Cache config (production)
php artisan config:cache
```

## Artisan Helpers
```bash
# List all routes
php artisan route:list

# List all artisan commands
php artisan list

# Interactive shell
php artisan tinker

# Generate API documentation
php artisan l5-swagger:generate
```

## Git Workflow
```bash
# Create feature branch
git checkout -b feature/template-management

# Commit changes
git add .
git commit -m "feat: add template management"

# Push to remote
git push origin feature/template-management
```

## Before Committing
```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Clear caches
php artisan optimize:clear
```
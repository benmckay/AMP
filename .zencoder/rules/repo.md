---
description: Repository Information Overview
alwaysApply: true
---

# Access Management Portal (AMP) Information

## Summary
The Access Management Portal (AMP) is a secure, web-based Laravel application for Aga Khan University Hospital (AKUH). It digitizes user access requests for critical systems like EHR, PeopleSoft, and PACs, replacing manual paper-based workflows with a streamlined, auditable process.

## Structure
- **app/**: Core PHP application logic (Models, Controllers, Providers, Services).
- **bootstrap/**: Framework bootstrapping and cache files.
- **config/**: Application configuration files (database, auth, services, etc.).
- **database/**: Migrations, factories, and seeders for database management.
- **public/**: Web server entry point (`index.php`) and static assets.
- **resources/**: Frontend assets (Blade templates, CSS, JS).
- **routes/**: Application route definitions (`api.php`, `web.php`, `console.php`).
- **storage/**: Generated files, logs, and framework cache.
- **tests/**: PHPUnit Feature and Unit tests.

## Language & Runtime
**Language**: PHP  
**Version**: ^8.2  
**Build System**: Vite (for frontend), Composer (for backend)  
**Package Manager**: Composer (PHP), npm (JS)

## Dependencies
**Main Dependencies**:
- `laravel/framework`: ^12.0
- `laravel/sanctum`: ^4.0 (JWT/API Authentication)
- `livewire/livewire`: ^3.7 (Full-stack framework)
- `spatie/laravel-permission`: ^6.24 (RBAC)
- `pragmarx/google2fa-laravel`: Two-Factor Authentication
- `barryvdh/laravel-dompdf`: PDF generation
- `darkaonline/l5-swagger`: API Documentation

**Development Dependencies**:
- `phpunit/phpunit`: ^11.5.3
- `laravel/sail`: Docker development environment
- `laravel/telescope`: Application monitoring
- `barryvdh/laravel-debugbar`: Debug toolbar

## Build & Installation
```bash
# Backend Installation
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Frontend Installation
npm install
npm run build
```

## Testing

**Framework**: PHPUnit
**Test Location**: `tests/`
**Naming Convention**: `*Test.php`
**Configuration**: `phpunit.xml`

**Run Command**:

```bash
php artisan test
```

## Usage & Operations
**Key Commands**:
```bash
# Start development server
php artisan serve

# Run queue worker
php artisan queue:listen

# Watch frontend assets
npm run dev

# Clear configuration cache
php artisan config:clear
```

# Access Management Portal - Complete Setup Guide

## Prerequisites

1. **Laravel Herd** installed (Windows/macOS)
2. **PostgreSQL** database running
3. **Composer** installed
4. **Node.js** and npm installed
5. **Git** for version control

## Step 1: Initial Laravel Setup

### Create Project (if not already created)
```bash
cd ~/Herd
composer create-project laravel/laravel access-management-portal
cd access-management-portal
```

### Install Required Packages
```bash
# Core security packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require pragmarx/google2fa-laravel

# Utilities
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf

# Development tools
composer require --dev laravel/telescope
composer require --dev barryvdh/laravel-debugbar
composer require --dev laravel/pint
```

## Step 2: Environment Configuration

### Configure .env file
```env
APP_NAME="Access Management Portal"
APP_ENV=local
APP_DEBUG=true
APP_URL=https://access-management-portal.test
APP_TIMEZONE=Africa/Nairobi

# PostgreSQL Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=amp
DB_USERNAME=postgres
DB_PASSWORD=

# Mail Configuration (Herd Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@akuh.com"
MAIL_FROM_NAME="AMP System"

# Queue Configuration
QUEUE_CONNECTION=database

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Sanctum
SANCTUM_STATEFUL_DOMAINS=access-management-portal.test
```

## Step 3: Database Setup

### Create PostgreSQL Database
```bash
# Using psql
psql -U postgres -c "CREATE DATABASE amp;"

# Or using DBngin on macOS (via Herd)
# Just open DBngin and create database "amp"
```

### Test Database Connection
```bash
php artisan migrate:install
```

## Step 4: Copy Migration Files

Copy all migration files to `database/migrations/` in this order:

1. `2024_01_01_000001_create_departments_table.php`
2. `2024_01_01_000002_create_templates_table.php`
3. `2024_01_01_000003_create_department_users_table.php`
4. `2024_01_01_000004_create_systems_table.php`
5. `2024_01_01_000005_create_access_requests_table.php`

### Run Migrations
```bash
php artisan migrate
```

## Step 5: Copy Model Files

Copy model files to `app/Models/`:

- `Department.php`
- `Template.php`
- `DepartmentUser.php`
- `AccessRequest.php`
- `System.php`

## Step 6: Copy Seeder Files

Copy seeder files to `database/seeders/`:

- `DepartmentSeeder.php`
- `TemplateSeeder.php`

### Run Seeders
```bash
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=TemplateSeeder
```

## Step 7: Copy Controller Files

Copy controller files to `app/Http/Controllers/API/`:

- `DepartmentController.php`
- `TemplateController.php`
- `AccessRequestController.php`

## Step 8: Copy API Routes

Replace content in `routes/api.php` with the provided API routes file.

## Step 9: Configure Laravel Sanctum

### Publish Sanctum Configuration
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### Update `app/Http/Kernel.php`
Add to `$middlewareGroups['api']`:
```php
\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
```

### Run Sanctum Migration
```bash
php artisan migrate
```

## Step 10: Configure Spatie Permission

### Publish Configuration
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### Create Roles Seeder
Create `database/seeders/RolesAndPermissionsSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view-requests',
            'create-requests',
            'approve-requests',
            'fulfill-requests',
            'manage-templates',
            'manage-users',
            'view-audit-logs',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $requester = Role::create(['name' => 'requester']);
        $requester->givePermissionTo(['view-requests', 'create-requests']);

        $approver = Role::create(['name' => 'approver']);
        $approver->givePermissionTo(['view-requests', 'approve-requests']);

        $hr = Role::create(['name' => 'hr']);
        $hr->givePermissionTo(['view-requests', 'create-requests', 'approve-requests']);

        $ictAdmin = Role::create(['name' => 'ict_admin']);
        $ictAdmin->givePermissionTo([
            'view-requests', 'create-requests', 'fulfill-requests',
            'manage-templates', 'manage-users', 'view-audit-logs', 'view-reports'
        ]);

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $auditor = Role::create(['name' => 'auditor']);
        $auditor->givePermissionTo(['view-audit-logs', 'view-reports']);
    }
}
```

### Run Roles Seeder
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

## Step 11: Create Test Users

Create `database/seeders/TestUsersSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@akuh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // ICT Admin
        $ict = User::create([
            'name' => 'ICT Admin',
            'email' => 'ict@akuh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $ict->assignRole('ict_admin');

        // Requester (Physician Department)
        $requester = User::create([
            'name' => 'Dr. John Requester',
            'email' => 'requester@akuh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $requester->assignRole('requester');
        
        // Assign to Physician department as requester
        \DB::table('department_users')->insert([
            'user_id' => $requester->id,
            'department_id' => 1, // Physician department
            'role' => 'requester',
            'is_active' => true,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Approver (Physician Department)
        $approver = User::create([
            'name' => 'Dr. Sarah Approver',
            'email' => 'approver@akuh.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $approver->assignRole('approver');
        
        // Assign to Physician department as approver
        \DB::table('department_users')->insert([
            'user_id' => $approver->id,
            'department_id' => 1, // Physician department
            'role' => 'approver',
            'is_active' => true,
            'assigned_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✓ Created test users:');
        $this->command->info('  Admin: admin@akuh.com / password');
        $this->command->info('  ICT Admin: ict@akuh.com / password');
        $this->command->info('  Requester: requester@akuh.com / password');
        $this->command->info('  Approver: approver@akuh.com / password');
    }
}
```

### Run Test Users Seeder
```bash
php artisan db:seed --class=TestUsersSeeder
```

## Step 12: Configure CORS

Update `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['https://access-management-portal.test'],
'supports_credentials' => true,
```

## Step 13: Install Frontend Dependencies
```bash
npm install
npm install axios alpinejs chart.js
```

### Compile Assets
```bash
npm run dev
```

## Step 14: Clear Caches and Optimize
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

## Step 15: Test the Setup

### Test Database Connection
```bash
php artisan tinker
>>> \DB::connection()->getPdo();
>>> \App\Models\Department::count();
```

### Test API Endpoints
```bash
# Using curl or Postman
curl https://access-management-portal.test/api/departments
```

### Access Application
Open your browser and visit:
```
https://access-management-portal.test
```

## Step 16: Import Remaining Templates from Excel

To import all templates from your Excel file, create an import command:
```bash
php artisan make:command ImportTemplatesFromExcel
```

Update `app/Console/Commands/ImportTemplatesFromExcel.php`:
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Department;
use App\Models\Template;

class ImportTemplatesFromExcel extends Command
{
    protected $signature = 'amp:import-templates {file}';
    protected $description = 'Import templates from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $excel = Excel::toArray([], $filePath);
        
        foreach ($excel as $sheetName => $rows) {
            // Find department by sheet name
            $department = Department::where('code', strtoupper($sheetName))->first();
            
            if (!$department) {
                $this->warn("Department not found for sheet: {$sheetName}");
                continue;
            }

            $imported = 0;
            foreach ($rows as $row) {
                if (empty($row[0]) || empty($row[3])) continue; // Skip empty rows
                
                Template::updateOrCreate(
                    ['mnemonic' => $row[0]],
                    [
                        'name' => $row[3],
                        'department_id' => $department->id,
                        'category' => $department->name,
                        'is_active' => true,
                    ]
                );
                $imported++;
            }
            
            $this->info("✓ Imported {$imported} templates for {$sheetName}");
        }

        $this->info('✓ Template import completed!');
        return 0;
    }
}
```

### Run the Import
```bash
php artisan amp:import-templates /path/to/Access_matrix_Review_Allocation.xlsx
```

## Troubleshooting

### Database Connection Issues
```bash
# Check PostgreSQL is running
psql -U postgres -c "SELECT 1"

# Verify .env configuration
php artisan config:clear
php artisan config:cache
```

### Migration Errors
```bash
# Reset and re-run migrations
php artisan migrate:fresh --seed
```

### Permission Issues
```bash
# Clear permission cache
php artisan cache:forget spatie.permission.cache
```

### Herd Issues
```bash
# Restart Herd
herd restart

# Check site is linked
herd links
```

## Next Steps

1. **Test all API endpoints** using Postman or Thunder Client
2. **Create frontend views** using Laravel Blade
3. **Implement authentication** with MFA
4. **Add audit logging** for all actions
5. **Set up email notifications** for workflow
6. **Create comprehensive tests**
7. **Deploy to staging** for UAT

## Useful Commands
```bash
# Generate API documentation
php artisan l5-swagger:generate

# Run tests
php artisan test

# Format code
./vendor/bin/pint

# Monitor application
php artisan telescope:install
```

## Support & Documentation

- Laravel Documentation: https://laravel.com/docs
- Sanctum: https://laravel.com/docs/sanctum
- Spatie Permission: https://spatie.be/docs/laravel-permission
- PostgreSQL: https://www.postgresql.org/docs/

---

**Setup Complete!** 🎉

Your Access Management Portal is now ready for development.
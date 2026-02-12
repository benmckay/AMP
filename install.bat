@echo off
echo ==========================================
echo Access Management Portal Installation
echo Aga Khan University Hospital
echo ==========================================
echo.

REM Check if artisan exists
if not exist "artisan" (
    echo [ERROR] This script must be run from the Laravel project root directory
    exit /b 1
)

echo Step 1: Checking system requirements...
echo ----------------------------------------
php --version
echo.

echo Step 2: Installing PHP dependencies...
echo ----------------------------------------
call composer install --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install PHP dependencies
    exit /b 1
)
echo [OK] PHP dependencies installed
echo.

echo Step 3: Installing Node.js dependencies...
echo ----------------------------------------
call npm install
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install Node.js dependencies
    exit /b 1
)
echo [OK] Node.js dependencies installed
echo.

echo Step 4: Environment configuration...
echo ----------------------------------------
if not exist ".env" (
    copy .env.example .env
    echo [OK] .env file created
) else (
    echo [WARNING] .env file already exists
)

php artisan key:generate
echo [OK] Application key generated
echo.

echo Step 5: Database configuration...
echo ----------------------------------------
set /p DB_NAME="Enter database name [amp]: "
if "%DB_NAME%"=="" set DB_NAME=amp

set /p DB_USER="Enter database username [postgres]: "
if "%DB_USER%"=="" set DB_USER=postgres

set /p DB_PASS="Enter database password: "

REM Update .env file (Windows version)
powershell -Command "(gc .env) -replace 'DB_DATABASE=.*', 'DB_DATABASE=%DB_NAME%' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_USERNAME=.*', 'DB_USERNAME=%DB_USER%' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_PASSWORD=.*', 'DB_PASSWORD=%DB_PASS%' | Out-File -encoding ASCII .env"
powershell -Command "(gc .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=pgsql' | Out-File -encoding ASCII .env"

echo [OK] Database configuration updated
echo.

echo Step 6: Running migrations...
echo ----------------------------------------
php artisan migrate --force
echo [OK] Database migrations completed
echo.

echo Step 7: Publishing configurations...
echo ----------------------------------------
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force
echo [OK] Configurations published
echo.

echo Step 8: Running seeders...
echo ----------------------------------------
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=TemplateSeeder
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan db:seed --class=TestUsersSeeder
echo [OK] Seeders completed
echo.

echo Step 9: Setting up storage and caching...
echo ----------------------------------------
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo [OK] Storage and caching setup complete
echo.

echo Step 10: Compiling frontend assets...
echo ----------------------------------------
call npm run build
echo [OK] Frontend assets compiled
echo.

echo ==========================================
echo Installation Complete!
echo ==========================================
echo.
echo Test User Credentials:
echo ----------------------------------------
echo Admin: admin@akuh.com / password
echo ICT Admin: ict@akuh.com / password
echo Requester: requester@akuh.com / password
echo Approver: approver@akuh.com / password
echo ==========================================
echo.
echo Visit: https://access-management-portal.test
echo.

set /p START_SERVER="Start development server now? (y/n): "
if /i "%START_SERVER%"=="y" (
    echo.
    echo Starting server... Press Ctrl+C to stop
    php artisan serve --host=0.0.0.0 --port=8000
)
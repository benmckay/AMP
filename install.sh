#!/bin/bash

# ============================================================================
# Access Management Portal - Complete Installation Script
# ============================================================================

echo "=========================================="
echo "Access Management Portal Installation"
echo "Aga Khan University Hospital"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print success message
success() {
    echo -e "${GREEN}✓${NC} $1"
}

# Function to print error message
error() {
    echo -e "${RED}✗${NC} $1"
}

# Function to print warning message
warning() {
    echo -e "${YELLOW}!${NC} $1"
}

# Check if running in project directory
if [ ! -f "artisan" ]; then
    error "This script must be run from the Laravel project root directory"
    exit 1
fi

echo "Step 1: Checking system requirements..."
echo "----------------------------------------"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
success "PHP version: $PHP_VERSION"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    error "Composer is not installed. Please install Composer first."
    exit 1
fi
success "Composer is installed"

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    error "npm is not installed. Please install Node.js and npm first."
    exit 1
fi
success "npm is installed"

echo ""
echo "Step 2: Installing PHP dependencies..."
echo "----------------------------------------"
composer install --optimize-autoloader
success "PHP dependencies installed"

echo ""
echo "Step 3: Installing Node.js dependencies..."
echo "----------------------------------------"
npm install
success "Node.js dependencies installed"

echo ""
echo "Step 4: Environment configuration..."
echo "----------------------------------------"

# Check if .env exists
if [ ! -f ".env" ]; then
    cp .env.example .env
    success ".env file created"
else
    warning ".env file already exists, skipping..."
fi

# Generate application key
php artisan key:generate
success "Application key generated"

echo ""
echo "Step 5: Database configuration..."
echo "----------------------------------------"

# Prompt for database details
read -p "Enter database name [amp]: " DB_NAME
DB_NAME=${DB_NAME:-amp}

read -p "Enter database username [postgres]: " DB_USER
DB_USER=${DB_USER:-postgres}

read -sp "Enter database password: " DB_PASS
echo ""

# Update .env file
sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i.bak "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env

success "Database configuration updated"

echo ""
echo "Step 6: Creating database..."
echo "----------------------------------------"

# Create database
PGPASSWORD=$DB_PASS psql -U $DB_USER -h 127.0.0.1 -c "CREATE DATABASE $DB_NAME;" 2>/dev/null
if [ $? -eq 0 ]; then
    success "Database '$DB_NAME' created"
else
    warning "Database might already exist or creation failed"
fi

echo ""
echo "Step 7: Running migrations..."
echo "----------------------------------------"
php artisan migrate --force
success "Database migrations completed"

echo ""
echo "Step 8: Publishing package configurations..."
echo "----------------------------------------"

# Publish Sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force
success "Sanctum configuration published"

# Publish Spatie Permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force
success "Spatie Permission configuration published"

echo ""
echo "Step 9: Running seeders..."
echo "----------------------------------------"

# Run seeders
php artisan db:seed --class=DepartmentSeeder
success "Department seeder completed"

php artisan db:seed --class=TemplateSeeder
success "Template seeder completed"

php artisan db:seed --class=RolesAndPermissionsSeeder
success "Roles and permissions seeder completed"

php artisan db:seed --class=TestUsersSeeder
success "Test users seeder completed"

echo ""
echo "Step 10: Setting up storage and caching..."
echo "----------------------------------------"

# Create storage link
php artisan storage:link
success "Storage linked"

# Clear and cache configurations
php artisan config:cache
success "Configuration cached"

php artisan route:cache
success "Routes cached"

php artisan view:cache
success "Views cached"

echo ""
echo "Step 11: Setting file permissions..."
echo "----------------------------------------"

# Set permissions
chmod -R 775 storage bootstrap/cache
success "File permissions set"

echo ""
echo "Step 12: Compiling frontend assets..."
echo "----------------------------------------"
npm run build
success "Frontend assets compiled"

echo ""
echo "=========================================="
echo "Installation Complete!"
echo "=========================================="
echo ""
echo "Test User Credentials:"
echo "----------------------------------------"
echo "Admin User:"
echo "  Email: admin@akuh.com"
echo "  Password: password"
echo ""
echo "ICT Admin:"
echo "  Email: ict@akuh.com"
echo "  Password: password"
echo ""
echo "Requester:"
echo "  Email: requester@akuh.com"
echo "  Password: password"
echo ""
echo "Approver:"
echo "  Email: approver@akuh.com"
echo "  Password: password"
echo ""
echo "=========================================="
echo "Next Steps:"
echo "----------------------------------------"
echo "1. Visit: https://access-management-portal.test"
echo "2. Login with any of the test accounts above"
echo "3. Change default passwords immediately"
echo "4. Import remaining templates from Excel"
echo "5. Configure email settings in .env"
echo "=========================================="
echo ""

# Ask if user wants to start the server
read -p "Do you want to start the development server now? (y/n): " START_SERVER

if [ "$START_SERVER" = "y" ] || [ "$START_SERVER" = "Y" ]; then
    echo ""
    echo "Starting development server..."
    echo "Press Ctrl+C to stop the server"
    echo ""
    php artisan serve --host=0.0.0.0 --port=8000
fi
# Access Management Portal (AMP)

> A secure, web-based application for Aga Khan University Hospital (AKUH) designed to digitize user access requests for critical systems including EHR, PeopleSoft, and PACs.

## 📋 Project Overview

The Access Management Portal (AMP) replaces manual paper-based workflows with a streamlined, auditable, and compliant digital process for managing user access to critical hospital systems. This solution provides role-based access control, multi-factor authentication, and comprehensive audit logging.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Node.js & npm (for frontend assets)
- Laravel Valet, Sail, or XAMPP (for local development)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/benmckay/AMP.git
   cd AMP
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets & run server**
   ```bash
   npm run dev
   php artisan serve
   ```

Visit `http://localhost:8000` to access the application.

## 🏗️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 11.x with PHP 8.2+ |
| **Frontend** | Laravel Blade, Alpine.js, Livewire |
| **Database** | MySQL/PostgreSQL with Eloquent ORM |
| **Authentication** | Laravel Sanctum (JWT) + 2FA |
| **Authorization** | Spatie Laravel Permission (RBAC) |
| **API Documentation** | L5-Swagger |
| **Testing** | PHPUnit with Feature & Unit tests |
| **Hosting** | AWS EC2 (App) + AWS RDS (Database) |
| **CI/CD** | GitHub Actions |

## 📦 Core Dependencies

### Security
- `laravel/sanctum` - JWT Authentication
- `spatie/laravel-permission` - Role-based access control
- `pragmarx/google2fa-laravel` - Two-Factor Authentication

### Features
- `maatwebsite/excel` - Excel export
- `barryvdh/laravel-dompdf` - PDF generation
- `intervention/image` - Image processing
- `league/flysystem-aws-s3-v3` - AWS S3 integration

### Development
- `barryvdh/laravel-debugbar` - Debug toolbar
- `laravel/telescope` - Application monitoring
- `phpunit/phpunit` - Testing framework

## 📁 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/                # Authentication
│   │   ├── API/                 # API endpoints
│   │   ├── Dashboard/           # Dashboard views
│   │   └── Admin/               # Admin functions
│   ├── Middleware/              # Custom middleware
│   ├── Requests/                # Form validation
│   ├── Resources/               # API resources
│   └── Traits/                  # Reusable traits
├── Models/                      # Eloquent models
│   ├── User.php
│   ├── AccessRequest.php
│   ├── AuditLog.php
│   └── System.php
├── Services/                    # Business logic
└── Providers/                   # Service providers
database/
├── migrations/                  # Schema migrations
├── factories/                   # Model factories
└── seeders/                     # Database seeders
resources/
├── views/                       # Blade templates
├── css/                         # Stylesheets
└── js/                          # JavaScript
routes/
├── api.php                      # API routes
├── web.php                      # Web routes
└── console.php                  # Console commands
tests/
├── Feature/                     # Feature tests
└── Unit/                        # Unit tests
```

## 🔒 Key Features

- **Secure Authentication**: JWT-based API authentication with Laravel Sanctum
- **Two-Factor Authentication**: Google 2FA integration
- **Role-Based Access Control**: Fine-grained permissions with Spatie
- **Audit Logging**: Comprehensive activity tracking for compliance
- **Multi-System Support**: Request access for EHR, PeopleSoft, PACs
- **PDF/Excel Export**: Generate reports and documentation
- **Real-time Notifications**: User and administrator alerts
- **API Documentation**: Swagger/OpenAPI specification

## 🧪 Testing

Run the test suite:

```bash
# All tests
php artisan test

# Feature tests only
php artisan test --filter=Feature

# Unit tests only
php artisan test --filter=Unit

# With coverage
php artisan test --coverage
```

## 📚 Documentation

For detailed technical documentation, see [claude_md_amp.md](claude_md_amp.md).

## 🔄 Development Workflow

1. Create a feature branch: `git checkout -b feature/your-feature`
2. Make your changes and commit: `git commit -am 'Add feature'`
3. Write or update tests for your changes
4. Push to the branch: `git push origin feature/your-feature`
5. Create a Pull Request on GitHub

## 🚀 Deployment

### Production Deployment
The application is designed to run on AWS infrastructure:

1. **Application Server**: AWS EC2 instance with Laravel
2. **Database**: AWS RDS (MySQL/PostgreSQL)
3. **Storage**: AWS S3 for file uploads
4. **CI/CD**: GitHub Actions for automated testing and deployment

See deployment configuration in your AWS console and GitHub Actions workflows.

## 📝 Environment Variables

Key configuration variables in `.env`:

```env
APP_NAME=AMP
APP_ENV=production
APP_DEBUG=false
APP_URL=https://amp.example.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=amp
DB_USERNAME=postgres
DB_PASSWORD=

AWS_S3_BUCKET=amp-uploads
AWS_REGION=us-east-1

MAIL_FROM_ADDRESS=noreply@akuh.example.com
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is proprietary software for Aga Khan University Hospital.

## 👥 Authors

- **Ben McKay** - Initial development
- Development team at Aga Khan University Hospital

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

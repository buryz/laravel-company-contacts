# Laravel Company Contacts - Project Setup

## Overview
This Laravel 10.x project has been successfully configured with all required packages and basic authentication middleware for the Company Contacts application. The application provides a comprehensive contact management system with features like real-time search, contact grouping, tagging, QR code generation, and CSV export.

## System Requirements

- **PHP 8.4** or higher
- **MySQL 9.3** or higher
- **Composer 2.5** or higher
- **Node.js 24.4.1** or higher with npm
- **Git**

## Installation Instructions

### Step 1: Clone the Repository
```bash
git clone https://github.com/your-username/laravel-company-contacts.git
cd laravel-company-contacts
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install JavaScript Dependencies
```bash
npm install
```

### Step 4: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit the `.env` file to configure your database connection:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_company_contacts
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Database Setup
```bash
php artisan migrate
php artisan db:seed
```

This will create all necessary tables and populate them with sample data.

### Step 6: Build Assets
```bash
npm run build
```

### Step 7: Start the Development Server
```bash
php artisan serve
```

The application will be available at http://localhost:8000

## Default User Accounts

Two user accounts are created by default:

1. **Administrator**
   - Email: admin@example.com
   - Password: password123

2. **Demo User**
   - Email: user@example.com
   - Password: password123

## Installed Components

### Core Framework
- **Laravel 10.x** - Main framework
- **PHP 8.4** - Runtime environment
- **MySQL 9.3** - Database server

### Required Packages
- **Laravel Sanctum** - API authentication
- **SimpleSoftwareIO/simple-qrcode** - QR code generation
- **League/CSV** - CSV export functionality
- **Laravel Scout** - Full-text search capabilities
- **Laravel Breeze** - Authentication scaffolding

### Frontend Technologies
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - Lightweight JavaScript framework
- **Vite** - Frontend build tool

## Configuration Options

### Search Configuration
The search functionality uses Laravel Scout with Meilisearch. To configure Meilisearch:

1. Install Meilisearch server: https://docs.meilisearch.com/learn/getting_started/installation.html
2. Update your `.env` file:
```
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=your_master_key
```

3. Index your contacts:
```bash
php artisan scout:import "App\Models\Contact"
```

### QR Code Configuration
QR code generation is handled by SimpleSoftwareIO/simple-qrcode. You can customize the QR code format in `config/qrcode.php`.

### Export Configuration
CSV export is handled by League/CSV. Default export settings can be modified in the `ExportService` class.

## Testing

### Running Tests
```bash
php artisan test
```

### Available Test Suites
- Unit Tests: `php artisan test --testsuite=Unit`
- Feature Tests: `php artisan test --testsuite=Feature`

## API Documentation
API documentation is available at `/api/documentation` when the application is running. For more details, see the `API_DOCUMENTATION.md` file.

## Security Considerations
- All API endpoints that modify data require authentication
- CSRF protection is enabled for web routes
- Input validation is implemented for all forms
- Authorization policies control access to resources

## Performance Optimization
- Database indexes are added for frequently queried columns
- Eager loading is used to prevent N+1 query problems
- Caching is implemented for frequently accessed data
- Pagination is used for large data sets

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Verify your database credentials in the `.env` file
   - Ensure MySQL service is running

2. **Missing Dependencies**
   - Run `composer install` to install PHP dependencies
   - Run `npm install` to install JavaScript dependencies

3. **Permission Issues**
   - Ensure storage and bootstrap/cache directories are writable:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

4. **Search Not Working**
   - Verify Meilisearch is running
   - Reindex your contacts: `php artisan scout:import "App\Models\Contact"`

## Commands Reference
- Start development server: `php artisan serve`
- Run tests: `php artisan test`
- Build assets: `npm run build`
- Run migrations: `php artisan migrate`
- Seed database: `php artisan db:seed`
- Clear cache: `php artisan cache:clear`
- Generate API documentation: `php artisan l5-swagger:generate`
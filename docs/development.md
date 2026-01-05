# Development Setup

This guide provides comprehensive instructions for setting up and running the LaraChat-Prism development environment.

## Prerequisites

### System Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.4.0 | 8.4.16 |
| Composer | 2.0 | 2.x |
| Node.js | 18.0 | 20.x+ |
| Package Manager | npm or Bun | Bun |
| Database | SQLite | SQLite |
| Git | 2.0 | Latest |

### Required PHP Extensions

- curl
- dom
- fileinfo
- filter
- hash
- mbstring
- openssl
- pcre
- pdo
- session
- tokenizer
- xml

### Verify PHP Extensions

```bash
php -m | grep -E "curl|dom|fileinfo|filter|hash|mbstring|openssl|pdo|session|tokenizer|xml"
```

## Initial Setup

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/larachat-prism.git
cd larachat-prism
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

**Using npm:**
```bash
npm install
```

**Using Bun (recommended):**
```bash
bun install
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### 5. Storage Link

```bash
php artisan storage:link
```

### 6. Start Development Server

**Using the Laravel dev script (recommended):**

```bash
composer run dev
```

This runs:
- PHP built-in server on port 8000
- Vite dev server
- Queue listener
- Log viewer (pail)

**Or run services separately:**

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev

# Terminal 3 (optional): Queue worker
php artisan queue:listen --tries=1

# Terminal 4 (optional): Log viewer
php artisan pail
```

### 7. Access the Application

Open your browser and navigate to:
- **URL**: http://localhost:8000
- **Home**: Redirects to /chats

## Development Workflow

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ChatControllerTest.php

# Run tests with filtering
php artisan test --filter=testName

# Run tests in parallel
php artisan test --parallel
```

### Code Formatting

**PHP (Pint):**
```bash
# Format all PHP files
./vendor/bin/pint

# Check formatting without changes
./vendor/bin/pint --test
```

**JavaScript/TypeScript (Prettier):**
```bash
# Format code
npm run format

# Check formatting
npm run format:check
```

### Linting

```bash
# Lint all code
npm run lint

# Fix linting issues
npm run lint:fix
```

### Type Checking

```bash
# Check TypeScript types
npm run typecheck
```

### Rector (PHP Refactoring)

```bash
# Check for refactoring opportunities
./vendor/bin/rector check

# Apply refactoring
./vendor/bin/rector apply
```

### Pint + Rector

```bash
# Run both formatters
composer run format
```

## Build Commands

### Development Build

```bash
# With Vite dev server (hot reload)
npm run dev

# Or with Bun
bun run dev
```

### Production Build

```bash
# Build frontend assets
npm run build

# Build with SSR
npm run build:ssr

# Build and optimize
php artisan optimize
```

### Watching Changes

```bash
# Watch for file changes
npm run dev -- --watch

# Or with Bun
bun run dev -- --watch
```

## Docker Development (Optional)

### Using Laravel Sail

```bash
# Build containers
./vendor/bin/sail build

# Start containers
./vendor/bin/sail up -d

# Run commands
./vendor/bin/sail php artisan migrate
./vendor/bin/sail npm run dev
```

### Sail Alias

Add to your shell profile for convenience:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

## Directory Structure

```
larachat-prism/
├── app/                    # Application code
│   ├── Console/Commands/   # Artisan commands
│   ├── Enums/              # PHP enums
│   ├── Http/
│   │   ├── Controllers/    # Controllers
│   │   ├── Middleware/     # Middleware
│   │   └── Requests/       # Form requests
│   ├── Models/             # Eloquent models
│   ├── Policies/           # Authorization policies
│   └── Providers/          # Service providers
├── bootstrap/              # Laravel bootstrap files
├── config/                 # Configuration files
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── docs/                   # Documentation
├── public/                 # Public assets
├── resources/
│   ├── css/                # CSS files
│   └── js/                 # JavaScript/Vue files
├── routes/                 # Route definitions
├── storage/                # Storage files
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── .env                    # Environment file
├── .env.example            # Environment template
├── composer.json           # Composer config
├── package.json            # NPM config
├── vite.config.ts          # Vite config
└── README.md               # Project readme
```

## Common Development Tasks

### Creating a New Model

```bash
# Generate model with factory and migration
php artisan make:model Post -mf

# Generate controller for the model
php artisan make:controller PostController --resource
```

### Creating a New Component

```bash
# Create Vue component manually
touch resources/js/components/chat/MyComponent.vue
```

### Adding a New Route

Add to `routes/web.php`:

```php
Route::get('/my-page', function () {
    return Inertia::render('MyPage');
})->name('my.page');
```

### Creating a Migration

```bash
php artisan make:migration create_posts_table
```

### Running Specific Migrations

```bash
# Refresh migrations (rollback and migrate)
php artisan migrate:fresh

# Rollback last migration
php artisan migrate:rollback

# Rollback all and re-migrate
php artisan migrate:refresh
```

### Database Seeding

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

### Clearing Caches

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Tinker (Interactive PHP)

```bash
# Open tinker shell
php artisan tinker

# Example usage
> use App\Models\User
> User::count()
```

### Queue Worker

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with retries
php artisan queue:work --tries=3

# Process specific queue
php artisan queue:work --queue=emails
```

## Debugging

### Laravel Debugbar

Install Laravel Debugbar for development:

```bash
composer require --dev barryvdh/laravel-debugbar
```

### Telescope

Laravel Telescope is included for monitoring:

```bash
# Access Telescope
php artisan telescope:install

# Run migrations
php artisan migrate

# Visit /telescope
```

### Log Viewer (Pail)

View logs in real-time:

```bash
php artisan pail
```

### Browser Developer Tools

- **Network tab**: Monitor API requests
- **Console**: Check for JavaScript errors
- **Vue DevTools**: Inspect Vue component hierarchy

## Environment-Specific Configuration

### Local Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Staging

```env
APP_ENV=staging
APP_DEBUG=false
LOG_CHANNEL=daily
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=daily
QUEUE_CONNECTION=database
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Performance Optimization

### Development

- Use `php artisan serve` for quick iterations
- Enable Vite HMR for instant frontend updates
- Use SQLite for faster database operations

### Production

```bash
# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear development caches
php artisan optimize:clear
```

## Troubleshooting

### Common Issues

#### "Class not found" errors

```bash
# Regenerate autoloader
composer dump-autoload
```

#### Vite manifest not found

```bash
# Rebuild assets
npm run build

# Or clear Vite cache
npm run dev -- --force
```

#### Database connection issues

```bash
# Check database connection
php artisan db:connection

# Refresh migrations
php artisan migrate:fresh
```

#### Permission errors

```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache

# Fix ownership (Linux/Mac)
sudo chown -R $USER:www-data .
```

#### Port already in use

```bash
# Find process using port 8000
lsof -i :8000

# Kill the process
kill -9 <PID>

# Or use a different port
php artisan serve --port=8001
```

### Getting Help

- **Documentation**: Check the `/docs` directory
- **Laravel Docs**: https://laravel.com/docs
- **Inertia Docs**: https://inertiajs.com
- **Vue Docs**: https://vuejs.org
- **TailwindCSS Docs**: https://tailwindcss.com

## Deployment

### Easy Deployment Options

#### Laravel Cloud

Deploy directly with [Laravel Cloud](https://cloud.laravel.com/) for seamless integration.

#### Sevalla

[Sevalla.com](https://sevalla.com/) offers Laravel-focused hosting with a free trial.

### Manual Deployment

```bash
# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Optimize
php artisan optimize --force

# Restart queue workers
php artisan queue:restart
```

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
OCTANE_SERVER=frankenphp
QUEUE_CONNECTION=database
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Next Steps

- [Architecture Overview](./architecture.md) - Understand the system design
- [Technology Stack](./stack.md) - Learn about the technologies used
- [Database Schema](./database.md) - Understand data models
- [API Routes](./api-routes.md) - Explore all endpoints
- [Components](./components.md) - Vue components and composables
- [Configuration](./configuration.md) - Environment and AI provider setup

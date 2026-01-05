# Configuration Guide

This document provides detailed instructions for configuring LaraChat-Prism, including environment setup, AI providers, and theme customization.

## Environment Setup

### Initial Configuration

After cloning the repository, set up your environment:

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database file (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### Environment Variables

**File**: `.env`

```env
# Application
APP_NAME="AI Chat"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost

# Locale
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Server
PHP_CLI_SERVER_WORKERS=4
OCTANE_SERVER=frankenphp

# Database (SQLite default)
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Cache & Queue
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (log driver for development)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Frontend
VITE_APP_NAME="${APP_NAME}"
```

## Database Configuration

### SQLite (Default)

```env
DB_CONNECTION=sqlite
```

No additional configuration needed. The database file is located at `database/database.sqlite`.

### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larachat_prism
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Create Database**:
```sql
CREATE DATABASE larachat_prism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### PostgreSQL

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=larachat_prism
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

## AI Provider Configuration

The application supports multiple AI providers. Configure only the providers you need.

### OpenAI

```env
OPENAI_API_KEY="sk-your-openai-api-key"
OPENAI_URL="https://api.openai.com/v1"
OPENAI_ORGANIZATION="org-your-org-id"
OPENAI_PROJECT="proj-your-project-id"
```

### Anthropic

```env
ANTHROPIC_API_KEY="sk-ant-api03-your-key"
ANTHROPIC_API_VERSION="2023-06-01"
ANTHROPIC_DEFAULT_THINKING_BUDGET=1024
ANTHROPIC_BETA=""  # Comma-separated beta strings
```

### Google Gemini

```env
GEMINI_API_KEY="your-gemini-api-key"
GEMINI_URL="https://generativelanguage.googleapis.com/v1beta/models"
```

### Ollama (Local)

```env
OLLAMA_URL="http://localhost:11434"
```

### Groq

```env
GROQ_API_KEY="gsk-your-groq-api-key"
GROQ_URL="https://api.groq.com/openai/v1"
```

### Mistral

```env
MISTRAL_API_KEY="your-mistral-api-key"
MISTRAL_URL="https://api.mistral.ai/v1"
```

### DeepSeek

```env
DEEPSEEK_API_KEY="your-deepseek-api-key"
DEEPSEEK_URL="https://api.deepseek.com/v1"
```

### xAI (Grok)

```env
XAI_API_KEY="your-xai-api-key"
XAI_URL="https://api.x.ai/v1"
```

### VoyageAI

```env
VOYAGEAI_API_KEY="your-voyageai-api-key"
VOYAGEAI_URL="https://api.voyageai.com/v1"
```

### OpenRouter

```env
OPENROUTER_API_KEY="sk-or-your-openrouter-key"
OPENROUTER_URL="https://openrouter.ai/api/v1"
OPENROUTER_SITE_HTTP_REFERER="https://your-site.com"
OPENROUTER_SITE_X_TITLE="Your App Name"
```

### Bedrock (AWS)

```env
BEDROCK_URL="https://api.openai.com/v1"
BEDROCK_API_KEY="your-aws-access-key"
BEDROCK_ORGANIZATION="your-org-id"
BEDROCK_PROJECT="your-project-id"
```

## AI Models Configuration

### Adding New Models

Models are defined in `app/Enums/ModelName.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    case BEDROCK_CLAUDE_4_5_SONNET = 'us.anthropic.claude-sonnet-4-5-20250929-v1:0';

    /**
     * @return array{id: string, name: string, description: string, provider: string}[]
     */
    public static function getAvailableModels(): array
    {
        return array_map(
            fn (ModelName $model): array => $model->toArray(),
            self::cases()
        );
    }

    public function getName(): string
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => Provider::OpenRouter,
        };
    }

    /**
     * @return array{id: string, name: string, description: string, provider: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'provider' => $this->getProvider()->value,
        ];
    }
}
```

### Example: Adding OpenAI GPT-4o

```php
enum ModelName: string
{
    // ... existing cases ...
    case GPT_4O = 'gpt-4o';
    
    public function getName(): string
    {
        return match ($this) {
            // ... existing cases ...
            self::GPT_4O => 'GPT-4o',
        };
    }
    
    public function getDescription(): string
    {
        return match ($this) {
            // ... existing cases ...
            self::GPT_4O => 'OpenAI\'s most capable model',
        };
    }
    
    public function getProvider(): Provider
    {
        return match ($this) {
            // ... existing cases ...
            self::GPT_4O => Provider::OpenAI,
        };
    }
}
```

## Theme Customization

The application uses TailwindCSS 4.x with CSS variables for theming.

### Light Theme Colors

**File**: `resources/css/app.css`

```css
:root {
  --background: hsl(0 0% 100%);
  --foreground: hsl(0 0% 3.9%);
  --card: hsl(0 0% 100%);
  --card-foreground: hsl(0 0% 3.9%);
  --popover: hsl(0 0% 100%);
  --popover-foreground: hsl(0 0% 3.9%);
  --primary: hsl(0 0% 9%);
  --primary-foreground: hsl(0 0% 98%);
  --secondary: hsl(0 0% 92.1%);
  --secondary-foreground: hsl(0 0% 9%);
  --muted: hsl(0 0% 96.1%);
  --muted-foreground: hsl(0 0% 45.1%);
  --accent: hsl(0 0% 96.1%);
  --accent-foreground: hsl(0 0% 9%);
  --destructive: hsl(0 84.2% 60.2%);
  --destructive-foreground: hsl(0 0% 98%);
  --border: hsl(0 0% 92.8%);
  --input: hsl(0 0% 89.8%);
  --ring: hsl(0 0% 3.9%);
  --chart-1: hsl(12 76% 61%);
  --chart-2: hsl(173 58% 39%);
  --chart-3: hsl(197 37% 24%);
  --chart-4: hsl(43 74% 66%);
  --chart-5: hsl(27 87% 67%);
  --radius: 0.5rem;
}
```

### Dark Theme Colors

```css
.dark {
  --background: hsl(0 0% 3.9%);
  --foreground: hsl(0 0% 98%);
  --card: hsl(0 0% 3.9%);
  --card-foreground: hsl(0 0% 98%);
  --popover: hsl(0 0% 3.9%);
  --popover-foreground: hsl(0 0% 98%);
  --primary: hsl(0 0% 98%);
  --primary-foreground: hsl(0 0% 9%);
  --secondary: hsl(0 0% 14.9%);
  --secondary-foreground: hsl(0 0% 98%);
  --muted: hsl(0 0% 16.08%);
  --muted-foreground: hsl(0 0% 63.9%);
  --accent: hsl(0 0% 14.9%);
  --accent-foreground: hsl(0 0% 98%);
  --destructive: hsl(0 84% 60%);
  --destructive-foreground: hsl(0 0% 98%);
  --border: hsl(0 0% 14.9%);
  --input: hsl(0 0% 14.9%);
  --ring: hsl(0 0% 83.1%);
  --chart-1: hsl(220 70% 50%);
  --chart-2: hsl(160 60% 45%);
  --chart-3: hsl(30 80% 55%);
  --chart-4: hsl(280 65% 60%);
  --chart-5: hsl(340 75% 55%);
}
```

### Sidebar Colors

```css
:root {
  --sidebar-background: hsl(0 0% 98%);
  --sidebar-foreground: hsl(240 5.3% 26.1%);
  --sidebar-primary: hsl(0 0% 10%);
  --sidebar-primary-foreground: hsl(0 0% 98%);
  --sidebar-accent: hsl(0 0% 94%);
  --sidebar-accent-foreground: hsl(0 0% 30%);
  --sidebar-border: hsl(0 0% 91%);
  --sidebar-ring: hsl(217.2 91.2% 59.8%);
}

.dark {
  --sidebar-background: hsl(0 0% 7%);
  --sidebar-foreground: hsl(0 0% 95.9%);
  --sidebar-primary: hsl(360, 100%, 100%);
  --sidebar-primary-foreground: hsl(0 0% 100%);
  --sidebar-accent: hsl(0 0% 15.9%);
  --sidebar-accent-foreground: hsl(240 4.8% 95.9%);
  --sidebar-border: hsl(0 0% 15.9%);
  --sidebar-ring: hsl(217.2 91.2% 59.8%);
}
```

### Customizing with Tweak.cn

1. Visit [tweakcn.com](https://tweakcn.com)
2. Adjust colors visually
3. Copy the generated CSS variables
4. Update `resources/css/app.css`

### Theme Fonts

```css
@theme inline {
  --font-sans: Instrument Sans, ui-sans-serif, system-ui, sans-serif,
    'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol',
    'Noto Color Emoji';
}
```

## Application Settings

### Session Configuration

**File**: `config/session.php`

```php
return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt' => false,
    'cookie' => env('SESSION_COOKIE', 'larachat_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN', null),
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => true,
    'same_site' => 'lax',
];
```

### Cache Configuration

**File**: `config/cache.php`

```php
return [
    'default' => env('CACHE_DRIVER', 'database'),
    'stores' => [
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
    ],
];
```

### Octane Configuration

**File**: `config/octane.php`

```php
return [
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    'listen' => env('OCTANE_LISTEN', '127.0.0.1:8000'),
    'workers' => env('OCTANE_WORKERS', 4),
    'max_requests' => 500,
    'warm' => [
        'Illuminate\\Support\\Facades\\Cache',
        'Illuminate\\Support\\Facades\\Database',
    ],
    'flush' => [],
    'reload' => [],
    'stop' => [],
    'macros' => [],
    'table' => 'octane_jobs',
];
```

## Prism Configuration

**File**: `config/prism.php`

```php
return [
    'prism_server' => [
        'middleware' => [],
        'enabled' => env('PRISM_SERVER_ENABLED', false),
    ],
    'providers' => [
        'openai' => [
            'url' => env('OPENAI_URL', 'https://api.openai.com/v1'),
            'api_key' => env('OPENAI_API_KEY', ''),
            'organization' => env('OPENAI_ORGANIZATION', null),
            'project' => env('OPENAI_PROJECT', null),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'version' => env('ANTHROPIC_API_VERSION', '2023-06-01'),
            'default_thinking_budget' => env('ANTHROPIC_DEFAULT_THINKING_BUDGET', 1024),
        ],
        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
        ],
        // ... other providers
    ],
];
```

## Security Settings

### Authentication

```env
# Password hashing
BCRYPT_ROUNDS=12

# Session lifetime (minutes)
SESSION_LIFETIME=120
```

### CORS

**File**: `config/cors.php`

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

## Production Configuration

### Environment

```env
APP_ENV=production
APP_DEBUG=false
```

### Cache

```bash
# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

### Queue

```env
QUEUE_CONNECTION=database
```

### Session

```env
SESSION_DRIVER=database
```

## Troubleshooting

### "Provider not configured" errors

- Ensure the required API key is set in `.env`
- Verify the API key is valid
- Check that the provider service is operational

### Streaming not working

- Verify server supports Server-Sent Events (SSE)
- Check firewall settings for long-running connections
- Ensure proper CORS configuration

### Model not appearing in UI

- Confirm the model is added to `ModelName` enum
- Verify the provider is properly configured
- Check browser console for JavaScript errors

## Next Steps

- [Development Setup](./development.md) - Get started developing
- [Database Schema](./database.md) - Database configuration
- [Architecture Overview](./architecture.md) - System design

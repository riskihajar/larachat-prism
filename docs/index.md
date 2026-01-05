# LaraChat-Prism Documentation

Welcome to the comprehensive documentation for LaraChat-Prism, a modern AI chat application built with Laravel 12, Inertia.js 2.x, Vue 3, and TailwindCSS 4.x.

## Quick Links

- [Architecture Overview](./architecture.md) - Learn about the application architecture and design patterns
- [Technology Stack](./stack.md) - Detailed information about all technologies used
- [Database Schema](./database.md) - Database models, relationships, and migrations
- [API Routes](./api-routes.md) - Complete list of routes and endpoints
- [Components](./components.md) - Vue components, composables, and type definitions
- [Configuration](./configuration.md) - Environment setup and configuration options
- [Development](./development.md) - Setup instructions and development commands

## Overview

LaraChat-Prism provides a solid foundation for building AI-powered chat applications. It leverages Laravel's powerful ecosystem combined with the Prism PHP SDK to deliver real-time streaming responses, creating a dynamic and engaging user experience.

### Key Features

- **Real-time AI Responses**: Stream AI responses as they're generated using Server-Sent Events (SSE)
- **Reasoning Support**: Built-in support for AI models with reasoning capabilities (e.g., Claude thinking mode)
- **Multiple AI Providers**: Support for OpenAI, Anthropic, Google Gemini, Ollama, Groq, Mistral, DeepSeek, xAI, VoyageAI, OpenRouter, and Bedrock
- **Authentication System**: Complete user authentication and management with email verification
- **Appearance Settings**: Light/dark mode support with system preference detection
- **Custom Theming**: Shadcn integration allows easy theme customization via CSS variables
- **Chat Sharing**: Share conversations with other users (public/private visibility)

## Project Structure

```
larachat-prism/
├── app/
│   ├── Console/Commands/         # Auto-registered Artisan commands
│   ├── Enums/                    # PHP Enums (ModelName, Visibility)
│   ├── Http/
│   │   ├── Controllers/          # HTTP controllers
│   │   │   ├── Auth/             # Authentication controllers (8 files)
│   │   │   ├── Settings/         # Profile & Password controllers
│   │   │   ├── ChatController.php
│   │   │   └── ChatStreamController.php
│   │   ├── Middleware/           # Request middleware
│   │   │   ├── HandleAppearance.php
│   │   │   └── HandleInertiaRequests.php
│   │   └── Requests/             # Form request classes
│   ├── Models/                   # Eloquent models (User, Chat, Message)
│   ├── Policies/                 # Authorization policies
│   └── Providers/                # Service providers
├── bootstrap/
│   ├── app.php                   # Laravel 12 application configuration
│   └── providers.php             # Service provider registration
├── config/                       # Configuration files
├── database/
│   ├── factories/                # Model factories
│   ├── migrations/               # Database migrations
│   └── seeders/
├── docs/                         # Documentation files
├── resources/
│   ├── css/
│   │   └── app.css               # TailwindCSS 4.x theme configuration
│   ├── js/
│   │   ├── app.ts                # Inertia app initialization
│   │   ├── ssr.ts                # Server-side rendering entry
│   │   ├── components/           # Vue components
│   │   │   ├── chat/             # Chat-specific components
│   │   │   └── ui/               # shadcn-style UI components
│   │   ├── composables/          # Vue composables
│   │   ├── layouts/              # App layouts
│   │   ├── pages/                # Inertia pages
│   │   └── types/                # TypeScript type definitions
│   └── views/
│       ├── prompts/
│       │   └── system.blade.php  # System prompt for AI
│       └── app.blade.php         # Root template
├── routes/
│   ├── web.php                   # Main routes
│   ├── auth.php                  # Authentication routes
│   ├── settings.php              # Settings routes
│   └── console.php               # Console routes
├── tests/                        # Pest tests
├── vite.config.ts                # Vite + Vue + Tailwind configuration
├── composer.json
└── package.json
```

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.4+** with required extensions
- **Composer 2.x**
- **Node.js 18+** with npm or Bun
- **SQLite** (or MySQL/PostgreSQL if preferred)
- **Git**

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/larachat-prism.git
   cd larachat-prism
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install frontend dependencies**:
   ```bash
   npm install
   # or with Bun
   bun install
   ```

4. **Set up environment variables**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**:
   ```bash
   # For SQLite (default)
   touch database/database.sqlite

   # Run migrations
   php artisan migrate
   ```

6. **Start the development server**:
   ```bash
   # Using the Laravel dev script (runs all services)
   composer run dev

   # Or run services separately
   php artisan serve
   npm run dev
   ```

Visit `http://localhost:8000` in your browser to see the application.

## Configuration

### Environment Variables

The `.env` file contains all configuration for the application. Key variables include:

- `APP_NAME` - Application name
- `DB_CONNECTION` - Database driver (sqlite, mysql, pgsql)
- `OPENAI_API_KEY` - OpenAI API key
- `ANTHROPIC_API_KEY` - Anthropic API key
- `GEMINI_API_KEY` - Google Gemini API key
- And many more for other AI providers

See the [Configuration](./configuration.md) guide for detailed information.

### AI Provider Setup

The application supports multiple AI providers. You can configure any combination of providers:

- **OpenAI**: Set `OPENAI_API_KEY`
- **Anthropic**: Set `ANTHROPIC_API_KEY`
- **Google Gemini**: Set `GEMINI_API_KEY`
- **Ollama**: Set `OLLAMA_URL` (default: http://localhost:11434)
- **Groq**: Set `GROQ_API_KEY`
- **And more...**

## Usage

### Creating a Chat

1. Register a new account or log in
2. Click "New Chat" to start a conversation
3. Type your message and press Enter or click the send button
4. Watch as AI responses stream in real-time

### Switching Models

Use the model selector dropdown to choose between available AI models. Models are configured in `app/Enums/ModelName.php`.

### Chat Visibility

Chats can be set to:
- **Private**: Only visible to the owner
- **Public**: Visible to anyone with the link

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ChatControllerTest.php

# Run tests with filtering
php artisan test --filter=testName
```

### Code Formatting

```bash
# Format PHP code
./vendor/bin/pint

# Format JavaScript/TypeScript code
npm run format
```

### Linting

```bash
# Lint all code
npm run lint

# Fix linting issues
npm run lint:fix
```

## Documentation Sections

| Section | Description |
|---------|-------------|
| [Architecture](./architecture.md) | Detailed architecture overview, design patterns, and data flow |
| [Technology Stack](./stack.md) | Complete list of technologies and their versions |
| [Database](./database.md) | Database schema, models, relationships, and migrations |
| [API Routes](./api-routes.md) | All routes, endpoints, and controller actions |
| [Components](./components.md) | Vue components, composables, and TypeScript types |
| [Configuration](./configuration.md) | Environment setup, AI providers, and theme customization |
| [Development](./development.md) | Setup instructions, commands, and testing guide |

## Contributing

Contributions are welcome! Please read the contributing guidelines in the main README before submitting pull requests.

## License

LaraChat-Prism is open-sourced software licensed under the MIT license.

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com)
- [Vue.js Documentation](https://vuejs.org)
- [TailwindCSS Documentation](https://tailwindcss.com)
- [Prism PHP SDK Documentation](https://prismphp.com)

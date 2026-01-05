# Technology Stack

This document provides detailed information about all technologies used in LaraChat-Prism, including versions, purposes, and key dependencies.

## Overview

LaraChat-Prism is built on a modern, type-safe technology stack combining the robustness of Laravel with the reactivity of Vue.js and the utility-first approach of TailwindCSS.

## Backend Technologies

### PHP 8.4.16

The application runs on PHP 8.4, leveraging the latest language features:

- **Constructor Property Promotion**: Simplified constructor syntax
- **Named Arguments**: Improved code readability
- **Attributes**: Metadata for classes and methods
- **Union Types**: Type declarations with multiple types
- **Match Expression**: Enhanced switch statement

```php
// Example: Using modern PHP features
final class ChatController extends Controller
{
    public function __construct(
        public ChatStreamController $streamController,
    ) {}

    public function show(Chat $chat): Response
    {
        // Modern match expression
        $visibility = match ($chat->visibility) {
            'public' => Visibility::PUBLIC,
            'private' => Visibility::PRIVATE,
        };
    }
}
```

### Laravel 12.44.0

The core framework providing:

- **Routing**: RESTful and named routes
- **Middleware**: Request preprocessing
- **Eloquent ORM**: Database abstraction
- **Authentication**: Built-in auth scaffolding
- **Validation**: Form request validation
- **Queue System**: Async job processing
- **Caching**: Multiple cache backends

**Key Laravel Components**:

| Component | Version | Purpose |
|-----------|---------|---------|
| framework | 12.44.0 | Core Laravel framework |
| sanctum | 4.2.1 | API authentication |
| octane | 2.13.3 | High-performance server |
| nightwatch | 1.21.1 | Performance monitoring |
| tinker | v2.10.1 | Interactive REPL |
| sail | 1.51.0 | Docker development environment |

### Prism PHP SDK 0.94.0

The AI integration layer supporting multiple LLM providers:

```php
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

// Text generation with streaming
$stream = Prism::text()
    ->using(Provider::OpenAI, 'gpt-4o')
    ->withPrompt('Hello, world!')
    ->asStream();
```

**Supported Providers**:

| Provider | Status | Features |
|----------|--------|----------|
| OpenAI | ✅ | Text, Embeddings, Images |
| Anthropic | ✅ | Text, Claude features |
| Google Gemini | ✅ | Text, Multimodal |
| Ollama | ✅ | Local models |
| Groq | ✅ | Fast inference |
| Mistral | ✅ | Text generation |
| DeepSeek | ✅ | Cost-effective |
| xAI | ✅ | Grok models |
| VoyageAI | ✅ | Embeddings |
| OpenRouter | ✅ | Unified access |
| Bedrock | ✅ | AWS models |

### Laravel Octane 2.13.3

High-performance application server powered by FrankenPHP:

- **Application Preloading**: Faster cold starts
- **Stateless Workers**: Efficient request handling
- **Concurrent Requests**: Improved throughput

**Configuration** (`config/octane.php`):

```php
return [
    'server' => env('OCTANE_SERVER', 'frankenphp'),
    'listen' => env('OCTANE_LISTEN', '127.0.0.1:8000'),
    'workers' => env('OCTANE_WORKERS', 4),
    'max_requests' => 500,
    'warm' => [
        // Middleware to preload
    ],
];
```

## Frontend Technologies

### Vue.js 3.5.22

Progressive JavaScript framework with Composition API:

```vue
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

const props = defineProps<{
  message: string
}>()

const count = ref(0)
const doubleCount = computed(() => count.value * 2)

function increment() {
  count.value++
}
</script>
```

**Key Vue Features Used**:

- **Composition API**: `<script setup>` syntax
- **Reactivity**: ref, computed, watch
- **Components**: Single File Components (.vue)
- **TypeScript**: Native support
- **Custom Events**: defineEmits

### Inertia.js 2.2.8

Server-side rendering framework connecting Laravel and Vue:

```php
// Laravel controller
return Inertia::render('Chat/Show', [
    'chat' => $chat->load('messages'),
    'chatHistory' => $chatHistory,
]);
```

**Benefits**:

- No API layer needed
- Server-side routing
- Automatic data sharing
- Progressive enhancement
- SSR support

### TailwindCSS 4.1.14

Utility-first CSS framework with CSS-first configuration:

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme inline {
  --color-primary: var(--primary);
  --color-secondary: var(--secondary);
}

:root {
  --primary: hsl(0 0% 9%);
  --secondary: hsl(0 0% 92.1%);
}
```

**Features Used**:

- `@import` syntax (v4)
- CSS variables for theming
- `@theme` directive
- Dark mode support
- Custom utilities
- Typography plugin

### Shadcn-style Components

Custom UI components following shadcn/ui patterns:

```
resources/js/components/ui/
├── button/           # Button variants
├── textarea/         # Text input
├── dialog/           # Modal dialogs
├── sheet/            # Slide-out panels
├── avatar/           # User avatars
├── collapsible/      # Collapsible content
├── breadcrumb/       # Navigation breadcrumbs
├── sonner/           # Toast notifications
└── ...
```

**Component Patterns**:

```vue
<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()
</script>

<template>
  <div :class="cn('base-styles', props.class)">
    <slot />
  </div>
</template>
```

## Build Tools

### Vite 6.4.0

Next-generation frontend tooling:

```typescript
// vite.config.ts
export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.ts'],
      ssr: 'resources/js/ssr.ts',
    }),
    tailwindcss(),
    vue(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './resources/js'),
    },
  },
})
```

**Plugins**:

| Plugin | Purpose |
|--------|---------|
| @vitejs/plugin-vue | Vue.js support |
| @tailwindcss/vite | TailwindCSS integration |
| laravel-vite-plugin | Laravel integration |
| unplugin-vue-components | Auto-import components |
| motion-v/resolver | Animation resolution |

### TypeScript 5.9.3

Static type checking for JavaScript:

```typescript
// Type definitions
interface Message {
  id: string
  role: 'user' | 'assistant'
  parts: {
    text?: string
    thinking?: string
  }
  attachments: string[]
}

// Usage with type safety
function sendMessage(message: Message): void {
  // TypeScript ensures correct structure
}
```

### Bun

JavaScript package manager and runtime:

```bash
# Install dependencies
bun install

# Run development server
bun run dev

# Run tests
bun test
```

**Benefits**:

- Faster installs than npm
- Native TypeScript support
- Built-in bundler
- Smaller node_modules

## Database

### SQLite (Default)

Used for development and simplicity:

```env
DB_CONNECTION=sqlite
```

**Benefits**:

- Zero configuration
- File-based storage
- No server required
- Easy backups

**Also Supported**:

- **MySQL**: Set `DB_CONNECTION=mysql`
- **PostgreSQL**: Set `DB_CONNECTION=pgsql`

## Testing

### Pest 3.8.4

Elegant testing framework for PHP:

```php
// tests/Feature/ChatControllerTest.php
it('creates a new chat', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('chats.store'), [
            'message' => 'Hello, AI!',
            'visibility' => 'private',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('chats', [
        'user_id' => $user->id,
    ]);
});
```

**Plugins**:

- `pestphp/pest-plugin-laravel`: Laravel testing helpers

### Vue Test Utils

Component testing utilities (via project configuration):

```typescript
import { mount } from '@vue/test-utils'
import ChatContainer from './ChatContainer.vue'

describe('ChatContainer', () => {
  it('renders messages correctly', () => {
    const wrapper = mount(ChatContainer, {
      props: {
        messages: [{ role: 'user', parts: { text: 'Hello' } }],
      },
    })
    
    expect(wrapper.text()).toContain('Hello')
  })
})
```

## Development Tools

### Laravel Pint 1.26.0

Code style fixer for PHP:

```bash
# Format all PHP files
./vendor/bin/pint
```

### ESLint 9.38.0

JavaScript/TypeScript linting:

```bash
# Lint code
npm run lint

# Fix issues
npm run lint:fix
```

**Configuration**: Extends `@antfu/eslint-config`

### Prettier 10.x

Code formatter for JavaScript/TypeScript:

```bash
# Format code
npm run format

# Check formatting
npm run format:check
```

## Additional Dependencies

### UI Libraries

| Package | Version | Purpose |
|---------|---------|---------|
| lucide-vue-next | 0.515.0 | Icon library |
| highlight.js | 11.11.1 | Code syntax highlighting |
| vue-markdown-render | 2.3.0 | Markdown rendering |
| vue-sonner | 2.0.9 | Toast notifications |
| clsx | 2.1.1 | Conditional class names |
| tailwind-merge | 3.3.1 | Tailwind class merging |
| class-variance-authority | 0.7.1 | Component variants |

### Animation

| Package | Version | Purpose |
|---------|---------|---------|
| motion-v | 1.7.3 | Animation library |
| tw-animate-css | 1.4.0 | Tailwind animations |

### Vue Utilities

| Package | Version | Purpose |
|---------|---------|---------|
| @vueuse/core | 12.8.2 | Vue composition utilities |
| @laravel/stream-vue | 0.3.9 | Server-Sent Events |
| reka-ui | 2.5.1 | Accessible UI components |

## Environment Variables

### Required

| Variable | Description |
|----------|-------------|
| `APP_NAME` | Application name |
| `APP_ENV` | Environment (local/production) |
| `APP_KEY` | Application encryption key |
| `DB_CONNECTION` | Database driver |

### AI Providers

| Variable | Provider | Required |
|----------|----------|----------|
| `OPENAI_API_KEY` | OpenAI | For OpenAI models |
| `ANTHROPIC_API_KEY` | Anthropic | For Claude models |
| `GEMINI_API_KEY` | Google Gemini | For Gemini models |
| `OLLAMA_URL` | Ollama | For local models |
| `GROQ_API_KEY` | Groq | For Groq models |
| `MISTRAL_API_KEY` | Mistral | For Mistral models |
| `DEEPSEEK_API_KEY` | DeepSeek | For DeepSeek models |
| `XAI_API_KEY` | xAI | For Grok models |

## Version Summary

| Technology | Version |
|------------|---------|
| PHP | 8.4.16 |
| Laravel | 12.44.0 |
| Prism SDK | 0.94.0 |
| Vue.js | 3.5.22 |
| Inertia.js | 2.2.8 |
| TailwindCSS | 4.1.14 |
| TypeScript | 5.9.3 |
| Vite | 6.4.0 |
| SQLite | 3.x |
| Pest | 3.8.4 |
| Bun | 1.x |

## Next Steps

- [Architecture Overview](./architecture.md) - Understand the system design
- [Database Schema](./database.md) - Learn about data models
- [Configuration Guide](./configuration.md) - Set up your environment
- [Development Setup](./development.md) - Get started developing

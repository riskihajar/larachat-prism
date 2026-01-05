# Architecture Overview

This document provides a detailed overview of the LaraChat-Prism application architecture, including design patterns, data flow, and component interactions.

## High-Level Architecture

LaraChat-Prism follows a modern server-side rendered (SSR) application architecture using Laravel and Inertia.js. The application is divided into two main parts:

1. **Backend (Laravel)**: Handles authentication, database operations, AI provider integration, and streaming responses
2. **Frontend (Vue 3 + Inertia.js)**: Provides a reactive user interface with server-side rendering capabilities

```
┌─────────────────────────────────────────────────────────────────┐
│                        Client Browser                            │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                  Vue 3 + Inertia.js                      │    │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐  │    │
│  │  │   Pages     │  │ Components  │  │   Composables   │  │    │
│  │  └─────────────┘  └─────────────┘  └─────────────────┘  │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTP Requests / SSE
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     Laravel 12 Backend                           │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐  │
│  │ Controllers │  │   Models    │  │   Prism SDK (AI)        │  │
│  │             │  │             │  │                         │  │
│  │ - Chat      │  │ - User      │  │ - OpenAI                │  │
│  │ - Auth      │  │ - Chat      │  │ - Anthropic             │  │
│  │ - Settings  │  │ - Message   │  │ - Gemini                │  │
│  └─────────────┘  └─────────────┘  │ - Ollama                │  │
│                                    │ - Groq                   │  │
│  ┌─────────────┐  ┌─────────────┐  │ - And more...           │  │
│  │  Middleware │  │  Policies   │  └─────────────────────────┘  │
│  │             │  │             │                                │
│  │ - Auth      │  │ - Chat      │  ┌─────────────────────────┐  │
│  │ - Inertia   │  │             │  │   Database (SQLite)     │  │
│  │ - Appearance│  │             │  │                         │  │
│  └─────────────┘  └─────────────┘  │   - users               │  │
│                                    │   - chats                │  │
│                                    │   - messages             │  │
│                                    └─────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Design Patterns

### MVC Pattern

The application follows the Model-View-Controller pattern with Laravel:

- **Models**: `User`, `Chat`, `Message` - represent data and business logic
- **Views**: Inertia pages (`resources/js/pages/`) - Vue components rendered server-side
- **Controllers**: Handle HTTP requests and coordinate between models and views

### Repository Pattern (Implicit)

While not explicitly implemented, the application follows repository-like patterns through:
- Eloquent query scopes in models
- Service classes for complex operations
- Form request classes for validation

### Composition API

The Vue frontend uses the Composition API with composables for reusable logic:

```typescript
// Example: Chat messages management composable
useChatMessages(chat, chatContainerRef)

// Example: Real-time message streaming
useMessageStream(chatId, messages, onComplete)

// Example: Theme/appearance management
useAppearance()
```

### Provider Pattern

AI providers are abstracted using the Strategy pattern through Prism:

```php
$response = Prism::text()
    ->using($model->getProvider(), $model->value)
    ->withPrompt($userMessage)
    ->asStream();
```

## Request Lifecycle

### Standard Page Request

```
1. Browser sends HTTP GET request
           │
           ▼
2. Laravel Router matches route
           │
           ▼
3. Controller processes request
           │
           ▼
4. Database queries via Eloquent
           │
           ▼
5. Inertia renders Vue page with data
           │
           ▼
6. Server sends HTML response
           │
           ▼
7. Browser hydrates Vue components
```

### Chat Streaming Request

```
1. User submits message via Inertia form
           │
           ▼
2. Client initiates SSE connection to /chat/stream/{chat}
           │
           ▼
3. ChatStreamController validates request
           │
           ▼
4. Prism SDK initiates AI API call with streaming
           │
           ▼
5. Response::stream() creates SSE response
           │
           ▼
6. Server sends events as they arrive:
   - text_delta: Regular AI response text
   - thinking: Reasoning output (if supported)
   - error: Error message if something fails
           │
           ▼
7. Client parses SSE events and updates UI in real-time
```

## Data Flow

### Chat Creation and Messaging

```
User Input → MultimodalInput → ChatContainer → Chat/Show.vue
                                        │
                                        ▼
                              useMessageStream (SSE)
                                        │
                                        ▼
                              ChatStreamController
                                        │
                                        ▼
                              Prism SDK (AI Provider)
                                        │
                                        ▼
                              SSE Response Events
                                        │
                                        ▼
                              Client UI Updates
                                        │
                                        ▼
                              Database Persistence
```

### Message Storage Structure

```typescript
interface Message {
  id: string              // UUID
  chat_id: string         // Foreign key to Chat
  role: 'user' | 'assistant'
  parts: {
    text?: string         // Main message content
    thinking?: string     // Reasoning content (if applicable)
  }
  attachments: string[]   // File attachments
  is_upvoted: boolean     // User feedback flag
  created_at: string
  updated_at: string
}
```

## Component Architecture

### Layout Hierarchy

```
AppLayout
├── AuthLayout (for authentication pages)
│   ├── AuthSimpleLayout
│   ├── AuthCardLayout
│   └── AuthSplitLayout
└── AppLayout
    ├── AppSidebarLayout
    │   ├── AppShell (sidebar variant)
    │   │   ├── AppSidebar
    │   │   └── AppContent
    │   │       ├── AppSidebarHeader
    │   │       └── Page Content (slot)
    │   └── Nav components
    └── SettingsLayout
        └── Settings page content
```

### Page Components

Pages are organized by feature:

```
resources/js/pages/
├── Welcome.vue              # Landing page
├── Dashboard.vue            # User dashboard
├── Chat/
│   ├── Index.vue            # Chat list
│   └── Show.vue             # Individual chat view
├── auth/
│   ├── Login.vue
│   ├── Register.vue
│   ├── VerifyEmail.vue
│   ├── ForgotPassword.vue
│   ├── ResetPassword.vue
│   └── ConfirmPassword.vue
└── settings/
    ├── Profile.vue
    ├── Password.vue
    └── Appearance.vue
```

### Chat Components

```
Chat/Show.vue
└── ChatContainer
    ├── Messages
    │   ├── Message (for each message)
    │   │   ├── MarkdownRenderer
    │   │   ├── ReasoningDisplay
    │   │   ├── MessageActions
    │   │   └── MessageEditor
    │   └── ThinkingMessage
    └── MultimodalInput
        ├── SendButton
        ├── StopButton
        ├── AttachmentsButton
        └── PreviewAttachment
```

## Authentication Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    Authentication Flow                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Guest User                                                     │
│      │                                                         │
│      ├─── GET /login ──→ Login Page                            │
│      │                                                         │
│      ├─── POST /login ──→ Authenticated ──→ Dashboard          │
│      │                                                         │
│      ├─── GET /register ──→ Register Page                      │
│      │                                                         │
│      └─── POST /register ──→ Authenticated ──→ Dashboard       │
│                                                                 │
│  Authenticated User                                             │
│      │                                                         │
│      ├─── GET /chats ──→ Chat List                             │
│      │                                                         │
│      ├─── GET /chats/{id} ──→ Chat View (with SSE)             │
│      │                                                         │
│      ├─── POST /chat/stream/{id} ──→ Streaming Response        │
│      │                                                         │
│      ├─── GET /settings/profile ──→ Profile Settings           │
│      │                                                         │
│      └─── POST /logout ──→ Guest User                          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Security Measures

### Authentication

- Laravel Sanctum for API token management
- Session-based authentication for web requests
- Email verification required for full access
- Password hashing with Bcrypt (12 rounds)

### Authorization

- Route middleware for protecting endpoints
- Policy-based authorization for models:
  - `ChatPolicy`: Controls chat access based on ownership and visibility
  - Public chats: Visible to anyone with the link
  - Private chats: Only visible to owner

### CSRF Protection

- Automatic CSRF token validation on all state-changing requests
- Same-origin policy enforced

### Input Validation

- Form request classes validate all inputs
- SQL injection prevention via Eloquent ORM
- XSS prevention via Inertia's automatic escaping

## Performance Considerations

### Server-Side Rendering

- Inertia SSR for fast initial page loads
- Preloaded assets with Link headers
- Lazy loading of components

### Streaming

- Server-Sent Events for real-time updates
- Chunk-based response processing
- Efficient memory usage during streaming

### Database

- UUID primary keys for security
- Eager loading to prevent N+1 queries
- Pagination for chat history

### Frontend

- Component auto-importing via unplugin-vue-components
- TypeScript for better development experience
- Vite for fast HMR during development

## File Structure Summary

| Directory | Purpose |
|-----------|---------|
| `app/Console/Commands/` | Custom Artisan commands |
| `app/Enums/` | PHP enums (ModelName, Visibility) |
| `app/Http/Controllers/` | HTTP request handlers |
| `app/Http/Middleware/` | Request preprocessing |
| `app/Http/Requests/` | Form validation |
| `app/Models/` | Eloquent models |
| `app/Policies/` | Authorization logic |
| `app/Providers/` | Service providers |
| `config/` | Configuration files |
| `database/migrations/` | Database schema |
| `database/factories/` | Test data factories |
| `resources/js/components/` | Vue components |
| `resources/js/composables/` | Reusable Vue logic |
| `resources/js/layouts/` | Page layouts |
| `resources/js/pages/` | Inertia pages |
| `resources/js/types/` | TypeScript definitions |
| `routes/` | Route definitions |
| `tests/` | Pest tests |

## Next Steps

- [Technology Stack](./stack.md) - Learn about the technologies used
- [Database Schema](./database.md) - Understand the data models
- [API Routes](./api-routes.md) - Explore all endpoints
- [Components](./components.md) - Details on Vue components

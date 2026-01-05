# API Routes and Endpoints

This document provides a complete reference of all routes, endpoints, and controller actions in LaraChat-Prism.

## Route Files

The application organizes routes into separate files for better maintainability:

| File | Purpose |
|------|---------|
| `routes/web.php` | Main application routes (chats, pages) |
| `routes/auth.php` | Authentication routes |
| `routes/settings.php` | User settings routes |
| `routes/console.php` | Artisan command routes |

## Web Routes

### Main Routes

**File**: `routes/web.php`

```php
Route::get('/', function () {
    return to_route('chats.index');
})->name('home');
```

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|------------|
| GET | / | home | Closure | web |

### Chat Resource Routes

```php
Route::resource('chat', ChatController::class)
    ->names('chats')
    ->except(['create', 'edit'])
    ->middlewareFor(['store', 'update', 'destroy'], ['auth', 'verified']);
```

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|------------|
| GET | /chat | chats.index | ChatController@index | web |
| POST | /chat | chats.store | ChatController@store | auth, verified |
| GET | /chat/{chat} | chats.show | ChatController@show | web |
| PATCH/PUT | /chat/{chat} | chats.update | ChatController@update | auth, verified |
| DELETE | /chat/{chat} | chats.destroy | ChatController@destroy | auth, verified |

### Chat Streaming Route

```php
Route::post('/chat/stream/{chat}', ChatStreamController::class)
    ->name('chat.stream')
    ->middleware(['auth', 'verified']);
```

| Method | URI | Name | Action | Middleware |
|--------|-----|------|--------|------------|
| POST | /chat/stream/{chat} | chat.stream | ChatStreamController | auth, verified |

**Note**: This route returns a Server-Sent Events (SSE) stream.

## Authentication Routes

**File**: `routes/auth.php`

### Guest Routes (Unauthenticated Users)

```php
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    
    Route::post('register', [RegisteredUserController::class, 'store']);
    
    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    
    // Password Reset - Request
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    
    // Password Reset - Reset Form
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});
```

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | /register | register | RegisteredUserController@create | guest |
| POST | /register | - | RegisteredUserController@store | guest |
| GET | /login | login | AuthenticatedSessionController@create | guest |
| POST | /login | - | AuthenticatedSessionController@store | guest |
| GET | /forgot-password | password.request | PasswordResetLinkController@create | guest |
| POST | /forgot-password | password.email | PasswordResetLinkController@store | guest |
| GET | /reset-password/{token} | password.reset | NewPasswordController@create | guest |
| POST | /reset-password | password.store | NewPasswordController@store | guest |

### Authenticated Routes

```php
Route::middleware('auth')->group(function () {
    // Email Verification
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');
    
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Password Confirmation
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    
    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | /verify-email | verification.notice | EmailVerificationPromptController | auth |
| GET | /verify-email/{id}/{hash} | verification.verify | VerifyEmailController | auth, signed, throttle |
| POST | /email/verification-notification | verification.send | EmailVerificationNotificationController | auth, throttle |
| GET | /confirm-password | password.confirm | ConfirmablePasswordController@show | auth |
| POST | /confirm-password | - | ConfirmablePasswordController@store | auth |
| POST | /logout | logout | AuthenticatedSessionController@destroy | auth |

## Settings Routes

**File**: `routes/settings.php`

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('settings', 'settings/profile');
    
    Route::get('settings/profile', [ProfileController::class, 'show'])
        ->name('settings.profile');
    
    Route::patch('settings/profile', [ProfileController::class, 'update'])
        ->name('settings.profile.update');
    
    Route::get('settings/password', [PasswordController::class, 'show'])
        ->name('settings.password');
    
    Route::put('settings/password', [PasswordController::class, 'update'])
        ->name('settings.password.update');
    
    Route::get('settings/appearance', [AppearanceController::class, 'show'])
        ->name('settings.appearance');
});
```

| Method | URI | Name | Controller | Middleware |
|--------|-----|------|------------|------------|
| GET | /settings | - | Redirect to settings/profile | auth, verified |
| GET | /settings/profile | settings.profile | ProfileController@show | auth, verified |
| PATCH | /settings/profile | settings.profile.update | ProfileController@update | auth, verified |
| GET | /settings/password | settings.password | PasswordController@show | auth, verified |
| PUT | /settings/password | settings.password.update | PasswordController@update | auth, verified |
| GET | /settings/appearance | settings.appearance | AppearanceController@show | auth, verified |

## Console Routes

**File**: `routes/console.php`

```php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
```

| Method | Command | Purpose |
|--------|---------|---------|
| CLI | `php artisan inspire` | Display inspiring quote |

## Controller Actions

### ChatController

**Location**: `app/Http/Controllers/ChatController.php`

#### index()

```php
public function index(): Response
```

- **Purpose**: Display user's chat history
- **Returns**: Inertia response with paginated chat list
- **Access**: Public

**Props Shared**:
```php
[
    'chatHistory' => PaginatedChatHistory,
]
```

#### store(StoreChatRequest $request)

```php
public function store(StoreChatRequest $request): RedirectResponse
```

- **Purpose**: Create a new chat
- **Validation**: Message and visibility required
- **Access**: Authenticated, verified
- **Redirect**: To new chat show page

**Request Data**:
```php
[
    'message' => 'string (required)',
    'visibility' => 'public|private (required)',
]
```

#### show(Chat $chat)

```php
public function show(Chat $chat): Response
```

- **Purpose**: Display chat messages
- **Access**: Owner or public visibility
- **Returns**: Inertia response with chat and messages

**Props Shared**:
```php
[
    'chat' => Chat with loaded messages,
    'chatHistory' => PaginatedChatHistory,
]
```

#### update(Chat $chat, UpdateChatRequest $request)

```php
public function update(Chat $chat, UpdateChatRequest $request): RedirectResponse
```

- **Purpose**: Update chat (title, visibility, or message upvote)
- **Access**: Owner only
- **Redirect**: To chat show page

**Request Data**:
```php
[
    'title' => 'string (optional)',
    'visibility' => 'public|private (optional)',
    'message_id' => 'uuid (optional)',
    'is_upvoted' => 'boolean (optional)',
]
```

#### destroy(Chat $chat)

```php
public function destroy(Chat $chat): RedirectResponse
```

- **Purpose**: Delete chat and all messages
- **Access**: Owner only
- **Redirect**: To chat index

### ChatStreamController

**Location**: `app/Http/Controllers/ChatStreamController.php`

#### __invoke(ChatStreamRequest $request, Chat $chat)

```php
public function __invoke(ChatStreamRequest $request, Chat $chat): StreamedResponse
```

- **Purpose**: Handle real-time AI streaming via SSE
- **Access**: Authenticated, verified
- **Returns**: StreamedResponse with SSE events

**Event Types**:

| Event Type | Description |
|------------|-------------|
| `text_delta` | Regular AI response text chunk |
| `thinking` | Reasoning/thinking output (if supported) |
| `error` | Error message |

**Request Data**:
```php
[
    'message' => 'string (required)',
    'model' => 'ModelName enum (required)',
]
```

**Example SSE Response**:
```json
{"eventType":"text_delta","content":"Hello"}
{"eventType":"text_delta","content":"!"}
{"eventType":"thinking","content":"Thinking process..."}
```

### Auth Controllers

| Controller | Actions |
|------------|---------|
| `RegisteredUserController` | create, store |
| `AuthenticatedSessionController` | create, store, destroy |
| `EmailVerificationPromptController` | __invoke |
| `VerifyEmailController` | __invoke |
| `EmailVerificationNotificationController` | store |
| `PasswordResetLinkController` | create, store |
| `NewPasswordController` | create, store |
| `ConfirmablePasswordController` | show, store |

### Settings Controllers

| Controller | Actions | Routes |
|------------|---------|--------|
| `ProfileController` | show, update | settings.profile, settings.profile.update |
| `PasswordController` | show, update | settings.password, settings.password.update |
| `AppearanceController` | show | settings.appearance |

## Middleware

### Application Middleware

**File**: `bootstrap/app.php`

```php
$middleware->web(append: [
    HandleAppearance::class,
    HandleInertiaRequests::class,
    AddLinkHeadersForPreloadedAssets::class,
]);
```

### Middleware Stack

| Middleware | Purpose |
|------------|---------|
| `HandleAppearance` | Shares theme preference with views |
| `HandleInertiaRequests` | Shares global Inertia data |
| `AddLinkHeadersForPreloadedAssets` | Preloads assets for better performance |
| `EncryptCookies` | Encrypts cookies (except appearance, sidebar_state) |

### Route Middleware

| Middleware | Purpose |
|------------|---------|
| `auth` | Requires authentication |
| `verified` | Requires email verification |
| `guest` | Redirects authenticated users |
| `signed` | Validates signed URLs |
| `throttle:6,1` | Rate limiting (6 requests per minute) |

## Route Naming Convention

The application follows Laravel's RESTful naming convention:

| HTTP Method | URI Pattern | Action | Route Name |
|-------------|-------------|--------|------------|
| GET | /resource | index | resource.index |
| POST | /resource | store | resource.store |
| GET | /resource/{id} | show | resource.show |
| PUT/PATCH | /resource/{id} | update | resource.update |
| DELETE | /resource/{id} | destroy | resource.destroy |

## Generating URLs

Use the `route()` helper to generate URLs:

```php
// Generate URL for a named route
$url = route('chats.index');

// Generate URL with parameters
$url = route('chats.show', ['chat' => $chat->id]);

// Generate URL with query string
$url = route('chats.index', ['page' => 2]);
```

In Vue/JavaScript, use Ziggy for route generation:

```typescript
import { route } from 'ziggy-js'

// Generate URL
const url = route('chats.show', { chat: chatId })

// Navigate with Inertia
router.visit(route('chats.index'))
```

## Next Steps

- [Database Schema](./database.md) - Understand data models
- [Components](./components.md) - Vue components and pages
- [Architecture Overview](./architecture.md) - System design

# Database Schema

This document details the database structure, models, relationships, and migrations used in LaraChat-Prism.

## Database Overview

LaraChat-Prism uses a relational database with three main models: `User`, `Chat`, and `Message`. The application is configured to use SQLite by default but can be easily switched to MySQL or PostgreSQL.

### Supported Databases

- **SQLite**: Default, file-based, no server required
- **MySQL**: Popular open-source relational database
- **PostgreSQL**: Advanced open-source relational database

## Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         users                                    │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ id: integer (PK)                                        │    │
│  │ name: string                                            │    │
│  │ email: string (unique)                                  │    │
│  │ email_verified_at: timestamp (nullable)                 │    │
│  │ password: string                                        │    │
│  │ remember_token: string (nullable)                       │    │
│  │ created_at: timestamp                                   │    │
│  │ updated_at: timestamp                                   │    │
│  └─────────────────────────────────────────────────────────┘    │
│                           │                                     │
│                           │ 1:n                                 │
│                           ▼                                     │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                         chats                            │    │
│  │  ┌─────────────────────────────────────────────────────┐│    │
│  │  │ id: uuid (PK)                                       ││    │
│  │  │ user_id: integer (FK)                               ││    │
│  │  │ title: string                                       ││    │
│  │  │ visibility: enum('public', 'private')               ││    │
│  │  │ created_at: timestamp                               ││    │
│  │  │ updated_at: timestamp                               ││    │
│  │  └─────────────────────────────────────────────────────┘│    │
│  │                           │                               │
│  │                           │ 1:n                           │
│  │                           ▼                               │
│  │  ┌─────────────────────────────────────────────────────┐│    │
│  │  │                       messages                       ││    │
│  │  │  ┌─────────────────────────────────────────────────┐││    │
│  │  │  │ id: uuid (PK)                                   │││    │
│  │  │  │ chat_id: uuid (FK)                              │││    │
│  │  │  │ role: string ('user' | 'assistant')             │││    │
│  │  │  │ parts: json (nullable)                           │││    │
│  │  │  │ attachments: json                                │││    │
│  │  │  │ is_upvoted: boolean (nullable)                   │││    │
│  │  │  │ created_at: timestamp                            │││    │
│  │  │  │ updated_at: timestamp                            │││    │
│  │  │  └─────────────────────────────────────────────────┘││    │
│  │  └─────────────────────────────────────────────────────┘│    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

## Migrations

### Users Table Migration

**File**: `database/migrations/0001_01_01_000000_create_users_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Session storage table
        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

### Chats Table Migration

**File**: `database/migrations/2025_05_25_015905_create_chats_table.php`

```php
<?php

declare(strict_types=1);

use App\Enums\Visibility;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->enum('visibility', Visibility::toArray())->default(Visibility::PRIVATE);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
```

### Messages Table Migration

**File**: `database/migrations/2025_05_25_020136_create_messages_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('chat_id')->constrained('chats');
            $table->string('role');
            $table->json('parts')->nullable();
            $table->string('attachments');
            $table->boolean('is_upvoted')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

## Models

### User Model

**File**: `app/Models/User.php`

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotificationCollection;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Chat> $chats
 * @property-read int|null $chats_count
 */
final class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the user's chats.
     */
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Chat Model

**File**: `app/Models/Chat.php`

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Database\Factories\ChatFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property int $user_id
 * @property string $title
 * @property string $visibility
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Message> $messages
 * @property-read int|null $messages_count
 * @property-read User $user
 */
final class Chat extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    /**
     * Get the messages for the chat.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the user that owns the chat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Message Model

**File**: `app/Models/Message.php`

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property string $chat_id
 * @property string $role
 * @property array<string, string>|null $parts
 * @property array<array-key, mixed> $attachments
 * @property int|null $is_upvoted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Chat $chat
 */
final class Message extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'parts' => 'array',
        'attachments' => 'array',
        'is_upvoted' => 'boolean',
    ];

    /**
     * Get the chat that owns the message.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
```

## Enums

### Visibility Enum

**File**: `app/Enums/Visibility.php`

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';

    /**
     * Get the values of the enum.
     */
    public static function toArray(): array
    {
        return [
            self::PUBLIC->value => 'public',
            self::PRIVATE->value => 'private',
        ];
    }
}
```

## Relationships

### User -> Chats (One-to-Many)

```php
// User model
public function chats(): HasMany
{
    return $this->hasMany(Chat::class);
}

// Usage
$user = User::find(1);
$userChats = $user->chats()->orderBy('updated_at', 'desc')->get();
```

### Chat -> User (Many-to-One)

```php
// Chat model
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

// Usage
$chat = Chat::find('uuid-here');
$owner = $chat->user;
```

### Chat -> Messages (One-to-Many)

```php
// Chat model
public function messages(): HasMany
{
    return $this->hasMany(Message::class);
}

// Usage
$chat = Chat::with('messages')->find('uuid-here');
$messages = $chat->messages()->orderBy('created_at')->get();
```

### Message -> Chat (Many-to-One)

```php
// Message model
public function chat(): BelongsTo
{
    return $this->belongsTo(Chat::class);
}

// Usage
$message = Message::find('uuid-here');
$chat = $message->chat;
```

## Model Factories

### User Factory

**File**: `database/factories/UserFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => str()->random(10),
        ];
    }
}
```

### Chat Factory

**File**: `database/factories/ChatFactory.php`

```php
<?php

namespace Database\Factories;

use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chat>
 */
class ChatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'title' => fake()->sentence(3),
            'visibility' => fake()->randomElement([Visibility::PUBLIC, Visibility::PRIVATE]),
        ];
    }
}
```

### Message Factory

**File**: `database/factories/MessageFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'chat_id' => \App\Models\Chat::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'parts' => [
                'text' => fake()->paragraph(),
            ],
            'attachments' => '[]',
            'is_upvoted' => null,
        ];
    }
}
```

## Query Examples

### Get User's Chats with Message Count

```php
$chats = User::find(1)
    ->chats()
    ->withCount('messages')
    ->orderBy('updated_at', 'desc')
    ->paginate(25);
```

### Get Chat with All Messages

```php
$chat = Chat::where('id', $chatId)
    ->with('messages')
    ->first();
```

### Get Public Chats

```php
$publicChats = Chat::where('visibility', 'public')
    ->orderBy('updated_at', 'desc')
    ->get();
```

### Get Chats Created Today

```php
$todayChats = Chat::whereDate('created_at', today())
    ->get();
```

## Database Configuration

### SQLite (Default)

```env
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

### MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larachat_prism
DB_USERNAME=root
DB_PASSWORD=your_password
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

## Performance Tips

1. **Use Eager Loading**: Always use `with()` to avoid N+1 queries
2. **Index Foreign Keys**: Foreign keys are automatically indexed
3. **Paginate Large Lists**: Use `paginate()` for chat history
4. **Use UUIDs**: Primary keys use UUIDs for security

## Next Steps

- [API Routes](./api-routes.md) - Learn about endpoints
- [Components](./components.md) - Understand Vue components
- [Configuration](./configuration.md) - Database configuration

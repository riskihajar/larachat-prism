# Vue Components and Composables

This document provides a comprehensive guide to the Vue components, composables, and TypeScript types used in LaraChat-Prism.

## Component Structure

```
resources/js/
├── app.ts                    # Inertia app initialization
├── ssr.ts                    # SSR entry point
├── components/
│   ├── chat/                 # Chat-specific components
│   ├── ui/                   # shadcn-style UI components
│   └── *.vue                 # Shared components
├── composables/              # Reusable Vue logic
├── layouts/                  # Page layouts
├── pages/                    # Inertia pages
└── types/                    # TypeScript definitions
```

## Pages

Pages are Inertia components rendered on the server and hydrated on the client.

### Dashboard

**File**: `resources/js/pages/Dashboard.vue`

```vue
<script setup lang="ts">
// User dashboard page
</script>

<template>
  <AppLayout>
    <!-- Dashboard content -->
  </AppLayout>
</template>
```

### Chat Pages

#### Chat Index

**File**: `resources/js/pages/Chat/Index.vue`

- Displays paginated list of user's chats
- Shows chat titles, visibility, and timestamps
- Allows creating new chats

#### Chat Show

**File**: `resources/js/pages/Chat/Show.vue`

Main chat interface with real-time streaming:

```vue
<script setup lang="ts">
import type {
  BreadcrumbItemType,
  Chat,
  ChatHistory,
  MessageParts,
  Model,
} from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { useStorage } from '@vueuse/core'
import { computed, nextTick, onMounted, provide, ref, watch } from 'vue'
import ChatContainer from '@/components/chat/ChatContainer.vue'
import { provideChatInput } from '@/composables/useChatInput'
import { useChatMessages } from '@/composables/useChatMessages'
import { useMessageStream } from '@/composables/useMessageStream'
import { provideVisibility } from '@/composables/useVisibility'
import { MODEL_KEY } from '@/constants/models'
import AppLayout from '@/layouts/AppLayout.vue'
import { ContentType, Visibility } from '@/types/enum'

const props = defineProps<{
  chatHistory?: ChatHistory
  chat: Chat
  availableModels: Model[]
}>()

const pageTitle = computed<string>(() => props.chat?.title || 'Chat')

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

// Chat input management
const { input, clearInput } = provideChatInput()

// Model selection (persisted in localStorage)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])

// Message streaming
const { isFetching, isStreaming, send, cancel, id } = useMessageStream(
  props.chat.id,
  messages,
  clearInput,
)

// Send message handler
async function handleSubmit(): Promise<void> {
  const trimmedInput = input.value.trim()

  if (
    !trimmedInput
    || isFetching.value
    || isStreaming.value
    || !props.chat.id
  ) {
    return
  }

  clearInput()

  await nextTick(() => {
    addTextMessage(trimmedInput)
  })

  send({
    message: trimmedInput,
    model: selectedModel.value.id,
  })
}

// Stop streaming
function stop(): void {
  if (isStreaming.value || isFetching.value) {
    cancel()
  }
}
</script>
```

### Authentication Pages

| Page | File | Route |
|------|------|-------|
| Login | `resources/js/pages/auth/Login.vue` | /login |
| Register | `resources/js/pages/auth/Register.vue` | /register |
| Verify Email | `resources/js/pages/auth/VerifyEmail.vue` | /verify-email |
| Forgot Password | `resources/js/pages/auth/ForgotPassword.vue` | /forgot-password |
| Reset Password | `resources/js/pages/auth/ResetPassword.vue` | /reset-password |
| Confirm Password | `resources/js/pages/auth/ConfirmPassword.vue` | /confirm-password |

### Settings Pages

| Page | File | Route |
|------|------|-------|
| Profile | `resources/js/pages/settings/Profile.vue` | /settings/profile |
| Password | `resources/js/pages/settings/Password.vue` | /settings/password |
| Appearance | `resources/js/pages/settings/Appearance.vue` | /settings/appearance |

## Chat Components

### ChatContainer

**File**: `resources/js/components/chat/ChatContainer.vue`

Main chat interface component that orchestrates messages and input.

```vue
<script setup lang="ts">
import type { Message } from '@/types/index'
import { ref } from 'vue'
import Messages from '@/components/chat/Messages.vue'
import MultimodalInput from '@/components/chat/MultimodalInput.vue'

withDefaults(
  defineProps<{
    messages?: Array<Message>
    streamId?: string
    attachments?: Array<string>
    isReadonly?: boolean
    chatId?: string
  }>(),
  {
    messages: () => [],
    streamId: '',
    attachments: () => [],
    isReadonly: false,
    chatId: '',
  },
)

defineEmits<{
  append: [message: string]
  stop: []
  handleSubmit: []
}>()

const isAtBottom = ref(false)
const messagesRef = ref<any>()

function handleScrollToBottom(): void {
  messagesRef.value?.scrollToBottom()
}

function updateIsAtBottom(value: boolean): void {
  isAtBottom.value = value
}

defineExpose({
  handleScrollToBottom,
})
</script>

<template>
  <div class="flex flex-col h-full bg-background overflow-hidden">
    <div class="flex-1 min-h-0 overflow-hidden">
      <Messages
        ref="messagesRef"
        :chat-id="chatId"
        :stream-id="streamId"
        :messages="messages"
        :is-readonly="isReadonly"
        @update-is-at-bottom="updateIsAtBottom"
      />
    </div>

    <div class="flex-shrink-0 mx-auto w-full max-w-3xl px-2 sm:px-4 pb-2 sm:pb-4 mt-2">
      <MultimodalInput
        :chat-id="chatId"
        :stream-id="streamId"
        :attachments="attachments"
        :messages="messages"
        :is-at-bottom="isAtBottom"
        :is-readonly="isReadonly"
        @append="$emit('append', $event)"
        @stop="$emit('stop')"
        @handle-submit="$emit('handleSubmit')"
        @scroll-to-bottom="handleScrollToBottom"
      />
    </div>
  </div>
</template>
```

### Messages

**File**: `resources/js/components/chat/Messages.vue`

Renders a list of messages with auto-scroll and typing indicators.

### Message

**File**: `resources/js/components/chat/Message.vue`

Individual message component with:
- Markdown rendering
- Reasoning display (for models that support it)
- Message actions (edit, upvote)
- Copy functionality

### MultimodalInput

**File**: `resources/js/components/chat/MultimodalInput.vue`

Text input with:
- File attachments support
- Send and stop buttons
- Keyboard shortcuts (Enter to send, Shift+Enter for new line)

### MarkdownRenderer

**File**: `resources/js/components/chat/MarkdownRenderer.vue`

Renders markdown content with syntax highlighting using highlight.js.

### ReasoningDisplay

**File**: `resources/js/components/chat/ReasoningDisplay.vue`

Displays thinking/reasoning output from AI models.

### ChatHeader

**File**: `resources/js/components/chat/ChatHeader.vue`

Header showing chat title and visibility selector.

### ModelSelector

**File**: `resources/js/components/chat/ModelSelector.vue`

Dropdown for selecting AI model.

### Other Chat Components

| Component | File | Purpose |
|-----------|------|---------|
| AttachmentsButton | `AttachmentsButton.vue` | File attachment button |
| ChatAction | `ChatAction.vue` | Chat actions (delete, rename) |
| Greeting | `Greeting.vue` | Initial chat greeting |
| MessageActions | `MessageActions.vue` | Message action buttons |
| MessageEditor | `MessageEditor.vue` | Inline message editing |
| PreviewAttachment | `PreviewAttachment.vue` | Attachment preview |
| SendButton | `SendButton.vue` | Send message button |
| SidebarToggle | `SidebarToggle.vue` | Mobile sidebar toggle |
| StopButton | `StopButton.vue` | Stop streaming button |
| SuggestedActions | `SuggestedActions.vue` | Quick action suggestions |
| ThinkingMessage | `ThinkingMessage.vue` | Thinking animation |
| VisibilitySelector | `VisibilitySelector.vue` | Chat visibility toggle |

## Layouts

### AppLayout

**File**: `resources/js/layouts/AppLayout.vue`

Main application layout with sidebar and header.

### AuthLayout

**File**: `resources/js/layouts/AuthLayout.vue`

Authentication pages layout (used for login, register, etc.).

### Sub-layouts

| Layout | File | Purpose |
|--------|------|---------|
| AuthSimpleLayout | `auth/AuthSimpleLayout.vue` | Simple centered layout |
| AuthCardLayout | `auth/AuthCardLayout.vue` | Card-based layout |
| AuthSplitLayout | `auth/AuthSplitLayout.vue` | Split screen layout |
| AppSidebarLayout | `app/AppSidebarLayout.vue` | Sidebar layout |
| AppHeaderLayout | `app/AppHeaderLayout.vue` | Header layout |
| SettingsLayout | `settings/Layout.vue` | Settings page layout |

## UI Components

The `ui/` directory contains shadcn-style components:

### Button

**File**: `resources/js/components/ui/button/Button.vue`

```vue
<script setup lang="ts">
import { cn } from '@/lib/utils'
import { type ButtonHTMLAttributes, computed } from 'vue'
import { cva, type VariantProps } from 'class-variance-authority'

const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default: 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
        destructive: 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
        outline: 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
        secondary: 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-9 px-4 py-2',
        sm: 'h-8 rounded-md px-3 text-xs',
        lg: 'h-10 rounded-md px-8',
        icon: 'h-9 w-9',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
)

interface Props extends ButtonHTMLAttributes {
  variant?: VariantProps<typeof buttonVariants>['variant']
  size?: VariantProps<typeof buttonVariants>['size']
}

const props = defineProps<Props>()
</script>

<template>
  <button :class="cn(buttonVariants({ variant, size }), props.class)">
    <slot />
  </button>
</template>
```

### Other UI Components

| Component | Directory | Purpose |
|-----------|-----------|---------|
| Textarea | `textarea/` | Multi-line text input |
| Dialog | `dialog/` | Modal dialogs |
| Sheet | `sheet/` | Slide-out panels |
| Avatar | `avatar/` | User avatars |
| Collapsible | `collapsible/` | Collapsible content |
| Breadcrumb | `breadcrumb/` | Navigation breadcrumbs |
| Sonner | `sonner/` | Toast notifications |

## Composables

Composables are reusable Vue functions using the Composition API.

### useChatMessages

**File**: `resources/js/composables/useChatMessages.ts`

Manages chat messages state and operations.

```typescript
import type { Ref } from 'vue'
import type { Chat, Message, MessageParts } from '@/types'
import { nextTick, ref, watch } from 'vue'
import { ContentType, Role } from '@/types/enum'

export function useChatMessages(chat: Chat, chatContainerRef: Ref<any>) {
  const messages = ref<Message[]>([
    ...(chat?.messages?.map(message => ({
      ...message,
      attachments:
        typeof message.attachments === 'string'
          ? JSON.parse(message.attachments)
          : message.attachments,
    })) || []),
  ])

  const addUserMessage = (content: MessageParts): Message => {
    const userMessage: Message = {
      role: Role.USER,
      parts: content,
      attachments: [],
    }
    messages.value.push(userMessage)
    return userMessage
  }

  const addTextMessage = (text: string): Message => {
    return addUserMessage({ [ContentType.TEXT]: text })
  }

  const scrollToBottom = (): void => {
    nextTick(() => {
      if (chatContainerRef.value) {
        chatContainerRef.value.handleScrollToBottom()
      }
    })
  }

  const getLastMessage = (): Message | undefined => {
    return messages.value[messages.value.length - 1]
  }

  const isLastMessageFromUser = (): boolean => {
    const lastMessage = getLastMessage()
    return lastMessage?.role === Role.USER
  }

  watch(
    () => chat?.messages,
    (newMessages) => {
      if (newMessages && newMessages.length > 0) {
        messages.value = [...newMessages]
        scrollToBottom()
      }
    },
    { immediate: true, deep: true },
  )

  return {
    messages,
    addUserMessage,
    addTextMessage,
    scrollToBottom,
    getLastMessage,
    isLastMessageFromUser,
  }
}
```

### useMessageStream

**File**: `resources/js/composables/useMessageStream.ts`

Handles real-time streaming via Server-Sent Events.

```typescript
import type { Ref } from 'vue'
import type { Message, StreamEvent } from '@/types'
import { useStream } from '@laravel/stream-vue'
import { nextTick } from 'vue'
import { ContentType, Role, StreamEventType } from '@/types/enum'

interface StreamParams {
  message: string
  model: string
}

export function useMessageStream(
  chatId: string,
  messages: Ref<Message[]>,
  onComplete?: () => void,
) {
  const updateMessageWithEvent = (eventData: StreamEvent): void => {
    let currentMessage = messages.value[messages.value.length - 1]

    if (!currentMessage || currentMessage.role !== Role.ASSISTANT) {
      currentMessage = {
        role: Role.ASSISTANT,
        parts: {},
      }
      messages.value.push(currentMessage)
    }

    const contentType
      = eventData.eventType === StreamEventType.TEXT_DELTA
        ? ContentType.TEXT
        : ContentType.THINKING

    if (!currentMessage.parts[contentType]) {
      currentMessage.parts[contentType] = ''
    }

    currentMessage.parts[contentType] += eventData.content
  }

  const parseStreamChunk = (chunk: string): void => {
    const lines = chunk
      .trim()
      .split('\n')
      .filter(line => line.trim())

    for (const line of lines) {
      try {
        const eventData = JSON.parse(line) as StreamEvent
        if (eventData.eventType !== StreamEventType.ERROR) {
          updateMessageWithEvent(eventData)
        }
      }
      catch (error) {
        console.error('Failed to parse JSON line:', error, 'Line:', line)
      }
    }
  }

  const handleStreamError = (): void => {
    nextTick(() => {
      messages.value.push({
        role: Role.ASSISTANT,
        parts: {
          [ContentType.TEXT]:
            'Sorry, there was an error processing your request. Please try again.',
        },
      })
    })
  }

  const stream = useStream<StreamParams, StreamEvent>(
    route('chat.stream', { chat: chatId }),
    {
      onData: parseStreamChunk,
      onError: handleStreamError,
      onFinish: onComplete,
    },
  )

  return {
    ...stream,
    updateMessageWithEvent,
  }
}
```

### useAppearance

**File**: `resources/js/composables/useAppearance.ts`

Manages light/dark mode theming.

```typescript
import { onMounted, ref } from 'vue'

type Appearance = 'light' | 'dark' | 'system'

export function updateTheme(value: Appearance): void {
  if (typeof window === 'undefined') {
    return
  }

  if (value === 'system') {
    const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)')
    const systemTheme = mediaQueryList.matches ? 'dark' : 'light'

    document.documentElement.classList.toggle('dark', systemTheme === 'dark')
  } else {
    document.documentElement.classList.toggle('dark', value === 'dark')
  }
}

export function useAppearance() {
  const appearance = ref<Appearance>('system')

  onMounted(() => {
    const savedAppearance = localStorage.getItem('appearance') as Appearance | null

    if (savedAppearance) {
      appearance.value = savedAppearance
    }
  })

  function updateAppearance(value: Appearance): void {
    appearance.value = value
    localStorage.setItem('appearance', value)
    updateTheme(value)
  }

  return {
    appearance,
    updateAppearance,
  }
}
```

### Other Composables

| Composable | File | Purpose |
|------------|------|---------|
| useAuth | `useAuth.ts` | Authentication state |
| useChatContainer | `useChatContainer.ts` | Chat container state |
| useChatHistory | `useChatHistory.ts` | Chat history management |
| useChatInput | `useChatInput.ts` | Chat input state |
| useInitials | `useInitials.ts` | Generate initials from name |
| useMessageFormatting | `useMessageFormatting.ts` | Message formatting utilities |
| useMessageVoting | `useMessageVoting.ts` | Message upvote/downvote |
| useScrollToBottom | `useScrollToBottom.ts` | Auto-scroll to bottom |
| useVisibility | `useVisibility.ts` | Visibility state management |

## TypeScript Types

**File**: `resources/js/types/index.d.ts`

```typescript
import type { PageProps } from '@inertiajs/core'
import type { LucideIcon } from 'lucide-vue-next'
import type { Config } from 'ziggy-js'
import type { ContentType, Role, StreamEventType, Visibility } from './enum'

export interface Auth {
  user?: User
}

export interface BreadcrumbItem {
  title: string
  href: string
}

export interface SharedData extends PageProps {
  name: string
  auth: Auth
  ziggy: Config & { location: string }
  sidebarOpen: boolean
  availableModels: Model[]
}

export interface User {
  id: number
  name: string
  email: string
  avatar?: string
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface HistoryItem {
  id: number
  title: string
  created_at: string
  updated_at: string
  visibility: Visibility
}

export interface ChatHistory {
  data: HistoryItem[]
  // ... pagination fields
}

export interface StreamEvent {
  eventType: StreamEventType
  content: string
}

export type MessageParts = Partial<Record<ContentType, string>>

export interface Message {
  id?: string
  chat_id?: string
  role: Role
  parts: MessageParts
  attachments?: string[]
  is_upvoted?: boolean
  created_at?: string
  updated_at?: string
}

export interface Chat {
  id: string
  user_id: number
  title: string
  visibility: Visibility
  created_at: string
  updated_at: string
  messages?: Message[]
}

export interface Model {
  id: string
  name: string
  description: string
  provider: string
}
```

## Enums

**File**: `resources/js/types/enum.ts`

```typescript
export enum Visibility {
  PUBLIC = 'public',
  PRIVATE = 'private',
}

export enum StreamStatus {
  READY = 'ready',
  STREAMING = 'streaming',
  SUBMITTED = 'submitted',
}

export enum Role {
  USER = 'user',
  ASSISTANT = 'assistant',
}

export enum StreamEventType {
  TEXT_DELTA = 'text_delta',
  THINKING = 'thinking',
  ERROR = 'error',
}

export enum ContentType {
  TEXT = 'text',
  THINKING = 'thinking',
}
```

## Utility Functions

**File**: `resources/js/lib/utils.ts`

```typescript
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}
```

## Component Auto-Import

Components are auto-imported using `unplugin-vue-components`. Components in these directories are automatically available:

- `resources/js/components/**/*.vue`
- `resources/js/components/ui/**/index.ts`
- `resources/js/layouts/**/*.vue`
- `resources/js/pages/**/*.vue`

No manual imports needed!

## Next Steps

- [Architecture Overview](./architecture.md) - System design
- [Database Schema](./database.md) - Data models
- [API Routes](./api-routes.md) - Endpoints
- [Configuration](./configuration.md) - Environment setup

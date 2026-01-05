<script setup lang="ts">
import type { Message, ToolCallInfo } from '@/types'
import type { HTMLAttributes } from 'vue'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'
import { CopyIcon, SparklesIcon, StopCircleIcon } from 'lucide-vue-next'
import { computed, nextTick, ref, watch } from 'vue'
import { Role } from '@/types/enum'
import MarkdownRenderer from '@/components/chat/MarkdownRenderer.vue'

interface Props {
  chatId: string
  messages: Message[]
  currentTool?: ToolCallInfo | null
  isStreaming?: boolean
  isReadonly?: boolean
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  isStreaming: false,
  isReadonly: false,
})

const emit = defineEmits<{
  (e: 'submit', message: string): void
  (e: 'stop'): void
}>()

const inputRef = ref<HTMLInputElement | null>(null)
const containerRef = ref<HTMLDivElement | null>(null)
const input = ref('')
const copiedId = ref<string | null>(null)

const isAtBottom = ref(true)

watch(
  () => props.messages.length,
  () => {
    nextTick(() => {
      scrollToBottom()
    })
  },
)

function scrollToBottom() {
  if (containerRef.value && isAtBottom.value) {
    containerRef.value.scrollTop = containerRef.value.scrollHeight
  }
}

function handleScroll() {
  if (!containerRef.value)
    return
  const { scrollTop, scrollHeight, clientHeight } = containerRef.value
  isAtBottom.value = scrollHeight - scrollTop - clientHeight < 50
}

async function handleSubmit() {
  const trimmed = input.value.trim()
  if (!trimmed || props.isStreaming)
    return

  input.value = ''
  emit('submit', trimmed)
}

function handleStop() {
  emit('stop')
}

async function handleCopy(message: Message) {
  const text = message.parts?.text || ''
  await navigator.clipboard.writeText(text)
  copiedId.value = message.id
  setTimeout(() => {
    copiedId.value = null
  }, 2000)
}

function getAvatarInitials(role: string) {
  return role === Role.USER ? '' : ''
}

function getAvatarClass(role: string) {
  return role === Role.USER ? 'hidden' : ''
}

function formatToolResult(result: string): string {
  try {
    const parsed = JSON.parse(result)
    if (parsed.readable)
      return parsed.readable
    return JSON.stringify(parsed, null, 2)
  }
  catch {
    return result
  }
}

function shouldShowToolResult(message: Message, tool: ToolCallInfo | null) {
  return (
    message.role === Role.ASSISTANT
    && tool
    && tool.status === 'complete'
    && tool.result
  )
}

const suggestions = [
  'What time is it in Tokyo?',
  'What date is it today?',
  'Tell me a fun fact',
  'Help me debug my code',
]

function handleSuggestionClick(suggestion: string) {
  emit('submit', suggestion)
}
</script>

<template>
  <div :class="cn('flex flex-col h-full bg-background', props.class)">
    <div
      ref="containerRef"
      class="flex-1 overflow-y-auto"
      @scroll="handleScroll"
    >
      <div class="max-w-3xl mx-auto py-6">
        <template v-if="messages.length === 0">
          <div class="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
            <div class="mb-6">
              <SparklesIcon class="size-10 w-10 h-10 text-muted-foreground/50" />
            </div>
            <h1 class="text-2xl font-semibold mb-2">
              How can I help you today?
            </h1>
            <p class="text-muted-foreground mb-6">
              Start a conversation or try one of these examples
            </p>
            <div class="flex flex-wrap gap-2 justify-center max-w-lg">
              <Button
                v-for="suggestion in suggestions"
                :key="suggestion"
                variant="outline"
                class="rounded-full"
                @click="handleSuggestionClick(suggestion)"
              >
                {{ suggestion }}
              </Button>
            </div>
          </div>
        </template>

        <template v-else>
          <div class="space-y-6">
            <div
              v-for="message in messages"
              :key="message.id"
              class="group flex gap-4"
              :class="message.role === Role.USER ? 'flex-row-reverse' : ''"
            >
              <Avatar
                :class="cn('size-8 shrink-0 mt-1', getAvatarClass(message.role))"
              >
                <AvatarFallback />
              </Avatar>

              <div
                class="flex-1 min-w-0"
                :class="message.role === Role.USER ? 'flex justify-end' : ''"
              >
                <div
                  :class="
                    cn(
                      'max-w-full',
                      message.role === Role.USER ? 'max-w-[85%]' : '',
                    )
                  "
                >
                  <div
                    :class="
                      cn(
                        'prose prose-sm dark:prose-invert max-w-none',
                        message.role === Role.USER
                          ? 'bg-zinc-100 dark:bg-zinc-800 rounded-2xl px-4 py-3 text-foreground'
                          : '',
                      )
                    "
                  >
                    <MarkdownRenderer
                      v-if="message.parts?.text"
                      :content="message.parts.text"
                    />
                    <p v-else class="italic text-muted-foreground">
                      No content
                    </p>
                  </div>

                  <div
                    v-if="shouldShowToolResult(message, currentTool) && message === messages[messages.length - 1]"
                    class="mt-3 rounded-lg bg-zinc-100 dark:bg-zinc-800/50 p-3 text-xs"
                  >
                    <div class="flex items-center gap-1.5 mb-1.5 text-muted-foreground">
                      <SparklesIcon class="size-3" />
                      <span class="font-medium">Tool: {{ currentTool.name }}</span>
                    </div>
                    <pre class="font-mono whitespace-pre-wrap break-words">{{ formatToolResult(currentTool.result!) }}</pre>
                  </div>
                </div>
              </div>
            </div>

            <div
              v-if="isStreaming"
              class="flex gap-4"
            >
              <Avatar class="size-8 shrink-0 mt-1">
                <AvatarFallback />
              </Avatar>
              <div class="flex items-center gap-1 text-muted-foreground pt-2">
                <span class="size-1.5 bg-zinc-300 dark:bg-zinc-600 rounded-full animate-pulse" />
                <span class="size-1.5 bg-zinc-300 dark:bg-zinc-600 rounded-full animate-pulse" style="animation-delay: 0.2s" />
                <span class="size-1.5 bg-zinc-300 dark:bg-zinc-600 rounded-full animate-pulse" style="animation-delay: 0.4s" />
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="border-t bg-background">
      <div class="max-w-3xl mx-auto px-4 py-4">
        <form
          class="flex items-center gap-2"
          @submit.prevent="handleSubmit"
        >
          <Input
            ref="inputRef"
            v-model="input"
            placeholder="Reply to..."
            class="flex-1 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 border-0 focus-visible:ring-1"
            :disabled="isStreaming"
            @keydown.enter.exact.prevent="handleSubmit"
          />
          <Button
            v-if="isStreaming"
            type="button"
            variant="outline"
            size="icon"
            class="rounded-full h-12 w-12"
            @click="handleStop"
          >
            <StopCircleIcon class="size-5" />
          </Button>
          <Button
            type="submit"
            size="icon"
            class="rounded-full h-12 w-12"
            :disabled="!input.trim() || isStreaming"
          >
            <svg
              class="size-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 12h14M12 5l7 7-7 7"
              />
            </svg>
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>

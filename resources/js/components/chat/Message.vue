<script setup lang="ts">
import type { Message } from '@/types'
import { Icon } from '@iconify/vue'
import { AnimatePresence, motion } from 'motion-v'
import { computed, ref } from 'vue'
import { useMessageFormatting } from '@/composables/useMessageFormatting'
import { ContentType, Role } from '@/types/enum'
import MarkdownRenderer from './MarkdownRenderer.vue'
import MessageActions from './MessageActions.vue'
import ReasoningDisplay from './ReasoningDisplay.vue'

interface ToolCallInfo {
  name: string
  arguments: Record<string, unknown>
  status: 'calling' | 'complete'
  result?: string
}

const props = defineProps<{
  message: Message
  isLoading: boolean
  requiresScrollPadding: boolean
  isReadonly?: boolean
  chatId?: string
  toolResult?: ToolCallInfo | null
}>()

const mode = ref<'view' | 'edit'>('view')
const { messageParts } = useMessageFormatting(props.message)

const isUserMessage = props.message.role === Role.USER
const isAssistantMessage = props.message.role === Role.ASSISTANT

const shouldShowToolResult = computed(() => {
  return (
    isAssistantMessage
    && props.toolResult
    && props.toolResult.status === 'complete'
    && props.toolResult.result
  )
})

function formatToolResult(result: string): string {
  try {
    const parsed = JSON.parse(result)
    if (parsed.readable) {
      return parsed.readable
    }
    return JSON.stringify(parsed, null, 2)
  }
  catch {
    return result
  }
}
</script>

<template>
  <AnimatePresence>
    <motion.div
      :key="message.id"
      :data-testid="`message-${message.role}`"
      class="w-full mx-auto max-w-3xl px-4 group/message"
      :initial="{ y: 5, opacity: 0 }"
      :animate="{ y: 0, opacity: 1 }"
      :data-role="message.role"
    >
      <div
        class="flex flex-col md:flex-row gap-2 md:gap-4 w-full group-data-[role=user]/message:ml-auto group-data-[role=user]/message:max-w-2xl"
        :class="[
          {
            'w-full': mode === 'edit',
            'group-data-[role=user]/message:w-fit': mode !== 'edit',
          },
        ]"
      >
        <div
          v-if="isAssistantMessage"
          class="size-8 flex items-center rounded-full justify-center ring-1 shrink-0 ring-border bg-background"
        >
          <div class="translate-y-px">
            <Icon icon="lucide:sparkles" class="size-4" />
          </div>
        </div>

        <div
          class="flex flex-col gap-2 w-full"
          :class="[
            {
              'min-h-96':
                isAssistantMessage && requiresScrollPadding,
            },
          ]"
        >
          <template
            v-for="(part, partIndex) in messageParts"
            :key="`${message.id}-${partIndex}`"
          >
            <ReasoningDisplay
              v-if="part[ContentType.THINKING]"
              :content="part[ContentType.THINKING]"
              :is-loading="isLoading"
            />

            <div
              v-else-if="part[ContentType.TEXT]"
              class="flex flex-row gap-2 items-start"
            >
              <div
                v-if="mode === 'view'"
                data-testid="message-content"
                class="flex flex-col gap-4 min-w-0 overflow-hidden"
                :class="[
                  {
                    'bg-primary text-primary-foreground px-3 py-2 rounded-xl':
                      isUserMessage,
                  },
                ]"
              >
                <div
                  v-if="part[ContentType.TEXT]"
                  class="w-full"
                >
                  <MarkdownRenderer
                    v-if="isAssistantMessage"
                    :content="part[ContentType.TEXT]"
                  />
                  <div
                    v-else
                    class="whitespace-pre-wrap"
                    v-text="part[ContentType.TEXT]"
                  />
                </div>

                <div
                  v-else
                  class="w-full text-muted-foreground italic"
                >
                  No content available
                </div>
              </div>
            </div>
          </template>

          <div
            v-if="shouldShowToolResult"
            class="mt-3 rounded-md bg-muted/50 border border-muted-foreground/20 p-3 text-sm"
          >
            <div class="flex items-center gap-2 text-xs text-muted-foreground mb-2">
              <Icon icon="lucide:tool" class="size-3" />
              <span class="font-medium">Tool: {{ toolResult?.name }}</span>
            </div>
            <div class="font-mono text-xs text-muted-foreground bg-muted/30 p-2 rounded">
              {{ formatToolResult(toolResult!.result!) }}
            </div>
          </div>

          <MessageActions
            v-if="!isLoading && !isReadonly"
            :key="`action-${message.id}`"
            :message="message"
            :chat-id="chatId"
            :is-loading="isLoading"
          />
        </div>
      </div>
    </motion.div>
  </AnimatePresence>
</template>

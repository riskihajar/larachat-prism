<script setup lang="ts">
import type { Message as MessageType, StreamEvent } from '@/types'
import type { ToolCallInfo } from '@/composables/useMessageStream'
import { useJsonStream } from '@laravel/stream-vue'
import { useScroll } from '@vueuse/core'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import Greeting from '@/components/chat/Greeting.vue'
import Message from '@/components/chat/Message.vue'
import ThinkingMessage from '@/components/chat/ThinkingMessage.vue'

const props = defineProps<{
  chatId?: string
  streamId?: string
  messages: Array<MessageType>
  isReadonly: boolean
  currentTool?: ToolCallInfo | null
}>()

const emit = defineEmits<{
  updateIsAtBottom: [isAtBottom: boolean]
}>()

const { isFetching, isStreaming } = useJsonStream<StreamEvent>(
  `stream/${props.chatId}`,
  { id: props.streamId },
)

const containerRef = ref<HTMLElement>()
// @ts-expect-error - containerRef is not typed (for some reason)
const { y, arrivedState } = useScroll(containerRef, { behavior: 'auto' })
const isAtBottom = computed(() => arrivedState.bottom)

function scrollToBottom() {
  if (!containerRef.value)
    return

  const maxScrollTop
    = containerRef.value.scrollHeight - containerRef.value.clientHeight
  y.value = maxScrollTop
}

watch(
  () => props.messages.length,
  (newLength, oldLength) => {
    if (newLength > oldLength && (isAtBottom.value || newLength === 1)) {
      nextTick(scrollToBottom)
    }
  },
)

watch(
  () => props.messages[props.messages.length - 1]?.parts,
  () => {
    if (isAtBottom.value && isStreaming.value) {
      nextTick(scrollToBottom)
    }
  },
  { flush: 'post' },
)

watch(isAtBottom, (newValue) => {
  emit('updateIsAtBottom', newValue)
})

onMounted(() => {
  if (props.messages.length > 0) {
    nextTick(scrollToBottom)
  }
})

defineExpose({
  scrollToBottom,
})
</script>

<template>
  <div
    ref="containerRef"
    class="flex flex-col h-full overflow-y-auto overflow-x-hidden pt-4 relative"
  >
    <div
      v-if="messages.length === 0"
      class="flex-1 flex items-center justify-center"
    >
      <Greeting />
    </div>

    <template v-else>
      <div class="flex flex-col gap-6 min-w-0 px-4 mb-4">
        <Message
          v-for="(message, index) in messages"
          :key="message.id"
          :message="message"
          :chat-id="chatId"
          :is-loading="isStreaming"
          :is-readonly="isReadonly"
          :requires-scroll-padding="index === messages.length - 1"
        />

        <ThinkingMessage v-if="isFetching" />

        <div
          v-if="currentTool && currentTool.status === 'calling'"
          class="flex items-center gap-2 text-sm text-muted-foreground animate-pulse"
        >
          <svg
            class="h-4 w-4 animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>Calling tool: {{ currentTool.name }}</span>
        </div>

        <div
          v-if="currentTool && currentTool.status === 'complete' && currentTool.result"
          class="rounded-md bg-muted p-3 text-sm"
        >
          <div class="font-medium mb-1">Tool Result: {{ currentTool.name }}</div>
          <pre class="text-xs overflow-x-auto">{{ currentTool.result }}</pre>
        </div>
      </div>
    </template>
  </div>
</template>

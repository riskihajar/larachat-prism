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
import AiChat from '@/components/ai-chat/AiChat.vue'
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
const initialVisibility = computed<Visibility>(
  () => props.chat?.visibility || Visibility.PRIVATE,
)

const breadcrumbs: BreadcrumbItemType[] = [
  {
    title: 'Chat',
    href: route('chats.index'),
  },
]

const { input, clearInput } = provideChatInput()
const initialVisibilityType = ref<Visibility>(initialVisibility.value)
const selectedModel = useStorage<Model>(MODEL_KEY, props.availableModels[0])

const { visibility } = provideVisibility(
  initialVisibility.value,
  initialVisibilityType,
)
const {
  messages,
  addTextMessage,
  getLastMessage,
  isLastMessageFromUser,
} = useChatMessages(props.chat)

const { isFetching, isStreaming, send, cancel, currentTool } = useMessageStream(
  props.chat.id,
  messages,
  clearInput,
)

provide('chatId', props.chat.id)

function updateChatVisibility(newVisibility: Visibility): void {
  router.patch(
    route('chats.update', { chat: props.chat.id }),
    { visibility: newVisibility },
    {
      preserveState: true,
      preserveScroll: true,
      async: true,
      only: [],
    },
  )
}

watch(
  visibility,
  (newVisibility, oldVisibility) => {
    if (oldVisibility !== undefined && newVisibility !== oldVisibility) {
      updateChatVisibility(newVisibility)
    }
  },
  { immediate: false },
)

async function handleSubmit(message: string): Promise<void> {
  if (
    !message
    || isFetching.value
    || isStreaming.value
    || !props.chat.id
  ) {
    return
  }

  addTextMessage(message)

  send({
    message,
    model: selectedModel.value.id,
  })
}

function stop(): void {
  if (isStreaming.value || isFetching.value) {
    cancel()
  }
}

onMounted(() => {
  if (input.value.trim()) {
    addTextMessage(input.value.trim())
    send({
      message: input.value.trim(),
      model: selectedModel.value.id,
    })
    clearInput()
    return
  }

  const lastMessage = getLastMessage()
  if (lastMessage && isLastMessageFromUser()) {
    send({
      message: lastMessage.parts?.text || '',
      model: selectedModel.value.id,
    })
    clearInput()
  }
})
</script>

<template>
  <Head :title="pageTitle" />
  <AppLayout :breadcrumbs="breadcrumbs" :chat-history="chatHistory">
    <div class="h-[calc(100vh-4rem)] bg-background">
      <AiChat
        :chat-id="props.chat.id"
        :messages="messages"
        :current-tool="currentTool"
        :is-streaming="isStreaming"
        :is-readonly="false"
        @submit="handleSubmit"
        @stop="stop"
      />
    </div>
  </AppLayout>
</template>

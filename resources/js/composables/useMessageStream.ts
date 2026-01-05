import type { Ref } from 'vue'
import type { Message, StreamEvent } from '@/types'
import { useStream } from '@laravel/stream-vue'
import { nextTick, ref } from 'vue'
import { ContentType, Role, StreamEventType } from '@/types/enum'

interface StreamParams {
  message: string
  model: string
}

interface ToolCallInfo {
  name: string
  arguments: Record<string, unknown>
  status: 'calling' | 'complete'
  result?: string
}

export function useMessageStream(
  chatId: string,
  messages: Ref<Message[]>,
  onComplete?: () => void,
) {
  const currentTool = ref<ToolCallInfo | null>(null)

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

    currentMessage.parts[contentType] += eventData.content ?? ''
  }

  const handleToolEvent = (eventData: StreamEvent): void => {
    if (eventData.eventType === StreamEventType.TOOL_CALL) {
      currentTool.value = {
        name: eventData.toolName ?? '',
        arguments: eventData.arguments ?? {},
        status: 'calling',
      }
    } else if (eventData.eventType === StreamEventType.TOOL_RESULT && currentTool.value) {
      currentTool.value = {
        ...currentTool.value,
        status: 'complete',
        result: eventData.result,
      }
    }
  }

  const parseStreamChunk = (chunk: string): void => {
    const lines = chunk
      .trim()
      .split('\n')
      .filter(line => line.trim())

    for (const line of lines) {
      try {
        const eventData = JSON.parse(line) as StreamEvent

        if (eventData.eventType === StreamEventType.ERROR) {
          console.error('Stream error:', eventData.content)
          continue
        }

        if (eventData.eventType === StreamEventType.STREAM_END) {
          if (onComplete) {
            onComplete()
          }
          continue
        }

        if (eventData.eventType === StreamEventType.TOOL_CALL
          || eventData.eventType === StreamEventType.TOOL_RESULT) {
          handleToolEvent(eventData)
        } else {
          updateMessageWithEvent(eventData)
        }
      }
      catch (error) {
        console.error(
          'Failed to parse JSON line:',
          error,
          'Line:',
          line,
        )
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
    currentTool,
  }
}

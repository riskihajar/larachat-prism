<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Generator;
use Throwable;
use App\Models\Chat;
use Prism\Prism\Prism;
use App\Models\Message;
use App\Enums\ModelName;
use App\Services\ChatTools;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ChatStreamRequest;
use Illuminate\Support\Facades\Response;
use Prism\Prism\Streaming\Events\ThinkingEvent;
use Prism\Prism\Streaming\Events\ToolCallEvent;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Prism\Prism\Streaming\Events\ToolResultEvent;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;

final class ChatStreamController extends Controller
{
    public function __invoke(ChatStreamRequest $request, Chat $chat): StreamedResponse
    {
        $userMessage = $request->string('message')->trim()->value();
        $model = $request->enum('model', ModelName::class, ModelName::BEDROCK_CLAUDE_4_5_SONNET);

        $chat->messages()->create([
            'role' => 'user',
            'parts' => [
                'text' => $userMessage,
            ],
            'attachments' => '[]',
        ]);

        $messages = $this->buildConversationHistory($chat);
        $tools = (new ChatTools)->getAvailableTools();

        return Response::stream(function () use ($chat, $messages, $model, $tools): Generator {
            $parts = [
                'text' => '',
                'thinking' => '',
            ];
            $hasToolCalls = false;

            try {
                $response = Prism::text()
                    ->withSystemPrompt(view('prompts.system'))
                    ->using($model->getProvider(), $model->value)
                    ->withMessages($messages)
                    ->withTools($tools)
                    ->withMaxSteps(3)
                    ->asStream();

                foreach ($response as $event) {
                    $eventData = match ($event::class) {
                        TextDeltaEvent::class => [
                            'eventType' => 'text_delta',
                            'content' => $event->delta,
                        ],
                        ThinkingEvent::class => [
                            'eventType' => 'thinking',
                            'content' => $event->delta,
                        ],
                        ToolCallEvent::class => [
                            'eventType' => 'tool_call',
                            'toolName' => $event->toolCall->name,
                            'arguments' => $event->toolCall->arguments(),
                        ],
                        ToolResultEvent::class => [
                            'eventType' => 'tool_result',
                            'toolName' => $event->toolResult->toolName,
                            'result' => $event->toolResult->result,
                        ],
                        default => null,
                    };

                    if ($eventData === null) {
                        continue;
                    }

                    if ($event instanceof TextDeltaEvent) {
                        $parts['text'] .= $event->delta;
                    }

                    if ($event instanceof ThinkingEvent) {
                        $parts['thinking'] .= $event->delta;
                    }

                    if ($event instanceof ToolCallEvent) {
                        $hasToolCalls = true;
                    }

                    yield json_encode($eventData)."\n";
                }

                $filteredParts = array_filter($parts, fn ($value) => $value !== '');

                if ($filteredParts !== [] || $hasToolCalls) {
                    $chat->messages()->create([
                        'role' => 'assistant',
                        'parts' => $filteredParts ?: ['text' => ''],
                        'attachments' => '[]',
                    ]);
                    $chat->touch();
                }

                yield json_encode(['eventType' => 'stream_end'])."\n";

            } catch (Throwable $throwable) {
                Log::error("Chat stream error for chat $chat->id: ".$throwable->getMessage()."\n".$throwable->getTraceAsString());
                yield json_encode([
                    'eventType' => 'error',
                    'content' => 'Stream failed: '.$throwable->getMessage(),
                ])."\n";
            }
        });
    }

    private function buildConversationHistory(Chat $chat): array
    {
        return $chat->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn (Message $message): UserMessage|AssistantMessage => match ($message->role) {
                'user' => new UserMessage(content: $message->parts['text'] ?? ''),
                'assistant' => new AssistantMessage(content: $message->parts['text'] ?? ''),
            })
            ->toArray();
    }
}

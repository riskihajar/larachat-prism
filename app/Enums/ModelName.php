<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    case BEDROCK_CLAUDE_4_5_SONNET = 'us.anthropic.claude-sonnet-4-5-20250929-v1:0';
    case BEDROCK_CLAUDE_4_5_SONNET_GLOBAL = 'global.anthropic.claude-sonnet-4-5-20250929-v1:0';
    case BEDROCK_CLAUDE_3_5_SONNET = 'anthropic.claude-3-5-sonnet-20240620-v1:0';
    case ANTHROPIC_CLAUDE_3_5_SONNET = 'claude-sonnet-4-20250501';
    case OPENAI_GPT_5_NANO = 'gpt-5-nano';
    case OPENAI_GPT_5_MINI = 'gpt-5-mini';

    /**
     * @return array{id: string, name: string, description: string, provider: string}[]
     */
    public static function getAvailableModels(): array
    {
        return array_map(
            fn (ModelName $model): array => $model->toArray(),
            self::cases()
        );
    }

    public function getName(): string
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet (US)',
            self::BEDROCK_CLAUDE_4_5_SONNET_GLOBAL => 'Claude 4.5 Sonnet (Global)',
            self::BEDROCK_CLAUDE_3_5_SONNET => 'Claude 3.5 Sonnet (Bedrock)',
            self::ANTHROPIC_CLAUDE_3_5_SONNET => 'Claude 3.5 Sonnet (Anthropic Direct)',
            self::OPENAI_GPT_5_NANO => 'GPT-5 Nano',
            self::OPENAI_GPT_5_MINI => 'GPT-5 Mini',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet - US Region via Bedrock Gateway',
            self::BEDROCK_CLAUDE_4_5_SONNET_GLOBAL => 'Claude 4.5 Sonnet - Global Profile (Tool calling)',
            self::BEDROCK_CLAUDE_3_5_SONNET => 'Claude 3.5 Sonnet - Stable model for tool calling',
            self::ANTHROPIC_CLAUDE_3_5_SONNET => 'Claude 3.5 Sonnet - Direct Anthropic API (Best for tools)',
            self::OPENAI_GPT_5_NANO => 'GPT-5 Nano - Fast and efficient',
            self::OPENAI_GPT_5_MINI => 'GPT-5 Mini - Good balance of speed and capability',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => Provider::OpenRouter,
            self::BEDROCK_CLAUDE_4_5_SONNET_GLOBAL => Provider::OpenRouter,
            self::BEDROCK_CLAUDE_3_5_SONNET => Provider::OpenRouter,
            self::ANTHROPIC_CLAUDE_3_5_SONNET => Provider::Anthropic,
            self::OPENAI_GPT_5_NANO => Provider::OpenAI,
            self::OPENAI_GPT_5_MINI => Provider::OpenAI,
        };
    }

    /**
     * @return array{id: string, name: string, description: string, provider: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'provider' => $this->getProvider()->value,
        ];
    }
}

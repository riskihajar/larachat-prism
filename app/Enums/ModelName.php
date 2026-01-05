<?php

declare(strict_types=1);

namespace App\Enums;

use Prism\Prism\Enums\Provider;

enum ModelName: string
{
    case BEDROCK_CLAUDE_4_5_SONNET = 'us.anthropic.claude-sonnet-4-5-20250929-v1:0';

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
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => 'Claude 4.5 Sonnet',
        };
    }

    public function getProvider(): Provider
    {
        return match ($this) {
            self::BEDROCK_CLAUDE_4_5_SONNET => Provider::OpenRouter
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

<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use DateTimeZone;
use DateTimeImmutable;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Tool as PrismTool;

final class ChatTools
{
    /**
     * @return array<PrismTool>
     */
    public function getAvailableTools(): array
    {
        return [
            $this->getDatetimeTool(),
        ];
    }

    private function getDatetimeTool(): PrismTool
    {
        return Tool::as('get_datetime')
            ->for('Get the current date and time in a specific timezone')
            ->withStringParameter(
                'timezone',
                'Timezone identifier (e.g., "America/New_York", "Europe/London", "UTC"). Defaults to "UTC".',
                false
            )
            ->withStringParameter(
                'format',
                'Output format: "iso" for ISO 8601, "unix" for Unix timestamp, "human" for readable format. Defaults to "iso".',
                false
            )
            ->using(function (string $timezone = 'UTC', string $format = 'iso'): string {
                $timezone = $this->validateTimezone($timezone);

                $dt = new DateTimeImmutable('now', new DateTimeZone($timezone));

                return match ($format) {
                    'unix' => json_encode([
                        'timestamp' => $dt->getTimestamp(),
                        'datetime' => $dt->format('Y-m-d H:i:s'),
                        'timezone' => $timezone,
                    ]),
                    'human' => json_encode([
                        'readable' => $dt->format('l, F j, Y \a\t g:i A'),
                        'timezone' => $timezone,
                    ]),
                    default => json_encode([
                        'iso8601' => $dt->format('c'),
                        'rfc2822' => $dt->format('r'),
                        'timezone' => $timezone,
                    ]),
                };
            });
    }

    private function validateTimezone(string $timezone): string
    {
        try {
            new DateTimeZone($timezone);

            return $timezone;
        } catch (Exception) {
            return 'UTC';
        }
    }
}

<?php

declare(strict_types=1);

use Prism\Prism\Prism;
use App\Services\ChatTools;
use Prism\Prism\Enums\Provider;
use Prism\Prism\ValueObjects\Usage;
use Prism\Prism\Testing\TextResponseFake;

describe('ChatTools', function () {
    it('returns available tools', function () {
        $tools = (new ChatTools)->getAvailableTools();

        expect($tools)->toHaveCount(1);
    });

    it('can be used with Prism text generation', function () {
        $fakeResponse = TextResponseFake::make()
            ->withText('The current time is now.')
            ->withUsage(new Usage(10, 5));

        $fake = Prism::fake([$fakeResponse]);

        $tools = (new ChatTools)->getAvailableTools();

        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withPrompt('What time is it?')
            ->withTools($tools)
            ->withMaxSteps(3)
            ->asText();

        expect($response->text)->toBe('The current time is now.');

        $fake->assertPrompt('What time is it?');
        $fake->assertCallCount(1);
    });

    it('can be used with Prism streaming', function () {
        $fakeResponse = TextResponseFake::make()
            ->withText('The current time is now.')
            ->withUsage(new Usage(10, 5));

        $fake = Prism::fake([$fakeResponse]);

        $tools = (new ChatTools)->getAvailableTools();

        $stream = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withPrompt('What time is it?')
            ->withTools($tools)
            ->withMaxSteps(3)
            ->asStream();

        $chunks = [];
        foreach ($stream as $chunk) {
            $chunks[] = $chunk;
        }

        expect(count($chunks))->toBeGreaterThan(0);

        $fake->assertPrompt('What time is it?');
        $fake->assertCallCount(1);
    });

    it('returns tools that can handle datetime queries', function () {
        $fakeResponse = TextResponseFake::make()
            ->withText('The current time in New York is 10:30 AM.')
            ->withUsage(new Usage(15, 7));

        $fake = Prism::fake([$fakeResponse]);

        $tools = (new ChatTools)->getAvailableTools();

        $response = Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withPrompt('What time is it in New York?')
            ->withTools($tools)
            ->withMaxSteps(3)
            ->asText();

        expect($response->text)->toBe('The current time in New York is 10:30 AM.');

        $fake->assertPrompt('What time is it in New York?');
    });
});

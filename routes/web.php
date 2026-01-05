<?php

declare(strict_types=1);

use App\Enums\ModelName;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatStreamController;
use Prism\Prism\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;

Route::get('/', function () {
    return to_route('chats.index');
})->name('home');

Route::get('/testing', function(){
    $model = ModelName::BEDROCK_CLAUDE_4_5_SONNET;
    $provider = $model->getProvider();

    $response = Prism::text()
        ->withSystemPrompt(view('prompts.system'))
        ->using($model->getProvider(), $model->value)
        ->withMessages([
            new UserMessage('Hello, how are you?'),
        ])
        // ->asStream()
        ->asText();
    
    dd($response);
});

Route::resource('chat', ChatController::class)
    ->names('chats')
    ->except(['create', 'edit'])
    ->middlewareFor(['store', 'update', 'destroy'], ['auth', 'verified']);

Route::post('/chat/stream/{chat}', ChatStreamController::class)
    ->name('chat.stream')
    ->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

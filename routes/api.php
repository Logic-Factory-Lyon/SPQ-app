<?php

use App\Http\Controllers\Api\MacMachineController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// ── Mac Mini daemon (legacy) ────────────────────────────────────────────
Route::prefix('mac')
    ->middleware(['api', 'auth.mac'])
    ->name('api.mac.')
    ->group(function () {
        Route::post('heartbeat', [MacMachineController::class, 'heartbeat'])->name('heartbeat');
        Route::get('messages/pending', [MacMachineController::class, 'pendingMessages'])->name('messages.pending');
        Route::post('messages/{message}/result', [MacMachineController::class, 'submitResult'])->name('messages.result');
    });

// ── Telegram bot webhooks (public, verified by secret header) ───────────
Route::post('telegram/webhook/{agent}', [TelegramWebhookController::class, 'handle'])
    ->name('api.telegram.webhook');

<?php

use App\Http\Controllers\Api\MacMachineController;
use App\Http\Controllers\Api\MacToolController;
use App\Http\Controllers\Api\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

// ── Mac Mini daemon ────────────────────────────────────────────────────
Route::prefix('mac')
    ->middleware(['api', 'auth.mac'])
    ->name('api.mac.')
    ->group(function () {
        Route::post('heartbeat', [MacMachineController::class, 'heartbeat'])->name('heartbeat');
        Route::get('messages/pending', [MacMachineController::class, 'pendingMessages'])->name('messages.pending');
        Route::post('messages/{message}/result', [MacMachineController::class, 'submitResult'])->name('messages.result');
        Route::get('tasks/pending', [MacMachineController::class, 'pendingTasks'])->name('tasks.pending');
        Route::post('tasks/{task}/result', [MacMachineController::class, 'submitTaskResult'])->name('tasks.result');

        // Skills & tools (daemon syncs from SPQ to Mac Mini filesystem)
        Route::get('skills/sync', [MacToolController::class, 'syncSkills'])->name('skills.sync');
        Route::post('tools/execute', [MacToolController::class, 'execute'])->name('tools.execute');
    });

// ── Telegram bot webhooks (public, verified by secret header) ───────────
Route::post('telegram/webhook/{agent}', [TelegramWebhookController::class, 'handle'])
    ->name('api.telegram.webhook');

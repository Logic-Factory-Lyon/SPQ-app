<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Message;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives Telegram updates and stores them in the SPQ database for history display.
 * OpenClaw on the Mac Mini still handles all AI processing.
 *
 * This webhook is OPTIONAL — it only makes sense if the admin explicitly registers it
 * (which disables OpenClaw's long-polling on the Mac Mini).
 * A better setup is to have OpenClaw forward conversation events to SPQ via its own hooks.
 */
class TelegramWebhookController extends Controller
{
    public function handle(Agent $agent, Request $request): JsonResponse
    {
        if (!$agent->telegram_bot_token) {
            return response()->json(['ok' => false], 404);
        }

        // Verify request comes from Telegram
        $expectedSecret = TelegramService::webhookSecret($agent->telegram_bot_token);
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== $expectedSecret) {
            return response()->json(['ok' => false], 403);
        }

        $update  = $request->json()->all();
        $message = $update['message'] ?? null;

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId   = (int) $message['chat']['id'];
        $text     = $message['text'];
        $isFromBot = isset($message['from']['is_bot']) && $message['from']['is_bot'];

        // Save /start command — capture the chat_id so admin can link it to a member
        if (str_starts_with($text, '/start')) {
            return response()->json(['ok' => true]);
        }

        // Find the project member linked to this chat_id
        $member = $agent->projectMembers()
            ->where('telegram_chat_id', $chatId)
            ->first();

        if (!$member) {
            return response()->json(['ok' => true]);
        }

        $conversation = $member->conversations()->latest()->first()
            ?? $member->conversations()->create(['title' => 'Telegram']);

        // Store the message (user message OR bot reply for history display)
        Message::create([
            'conversation_id' => $conversation->id,
            'direction'       => $isFromBot ? 'out' : 'in',
            'content'         => $text,
            'status'          => $isFromBot ? 'response' : 'done',
        ]);

        return response()->json(['ok' => true]);
    }
}

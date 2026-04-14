<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the Telegram Bot API.
 * Only used for optional conversation-history sync (when a bot token is configured).
 * The actual AI processing is handled by OpenClaw on the Mac Mini.
 */
class TelegramService
{
    private function call(string $token, string $method, array $params = []): array
    {
        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$token}/{$method}", $params);
            return $response->json() ?? ['ok' => false];
        } catch (\Exception $e) {
            Log::error("Telegram {$method}: {$e->getMessage()}");
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    public function sendMessage(string $token, int|string $chatId, string $text): bool
    {
        $result = $this->call($token, 'sendMessage', [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
        ]);
        if (!($result['ok'] ?? false)) {
            // Retry without Markdown if formatting caused an error
            $result = $this->call($token, 'sendMessage', ['chat_id' => $chatId, 'text' => $text]);
        }
        return $result['ok'] ?? false;
    }

    /**
     * Register spq.app as the Telegram webhook for this bot.
     * NOTE: this disables OpenClaw's long-polling on the Mac Mini.
     * Only use if you want SPQ to capture conversation history.
     */
    public function setWebhook(string $token, string $url, string $secret): bool
    {
        $result = $this->call($token, 'setWebhook', [
            'url'             => $url,
            'secret_token'    => $secret,
            'max_connections' => 5,
            'allowed_updates' => ['message'],
        ]);
        return $result['ok'] ?? false;
    }

    public function deleteWebhook(string $token): bool
    {
        return ($this->call($token, 'deleteWebhook')['ok']) ?? false;
    }

    public function getWebhookInfo(string $token): array
    {
        return $this->call($token, 'getWebhookInfo');
    }

    /** Shared secret for webhook URL verification (derived from bot token). */
    public static function webhookSecret(string $token): string
    {
        return substr(hash('sha256', $token), 0, 32);
    }
}

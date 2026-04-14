<?php
namespace App\Services\MacMachine;

use App\Models\Conversation;
use App\Models\Message;

class OpenClawDispatchService
{
    /**
     * Queue an outbound message for the Mac Mini daemon to pick up.
     * The Laravel app NEVER shells out to openclaw directly.
     */
    public function dispatch(Conversation $conversation, string $userContent): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'out',
            'content' => $userContent,
            'status' => 'pending',
        ]);
    }
}

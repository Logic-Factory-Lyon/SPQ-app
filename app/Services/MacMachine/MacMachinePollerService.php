<?php
namespace App\Services\MacMachine;

use App\Models\MacMachine;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class MacMachinePollerService
{
    /**
     * Get pending outbound messages for members whose agent runs on this machine.
     */
    public function getPendingForMachine(MacMachine $machine): Collection
    {
        return DB::transaction(function () use ($machine) {
            $messages = Message::query()
                ->where('direction', 'out')
                ->where('status', 'pending')
                ->whereHas('conversation.projectMember.agent', fn($q) => $q->where('mac_machine_id', $machine->id))
                ->with(['conversation.projectMember.agent'])
                ->lockForUpdate()
                ->get();

            $ids = $messages->pluck('id');
            if ($ids->isNotEmpty()) {
                Message::whereIn('id', $ids)->update(['status' => 'processing']);
            }

            return $messages;
        });
    }

    /**
     * Submit the result of an agent execution.
     */
    public function submitResult(Message $outboundMessage, MacMachine $machine, string $result): Message
    {
        $agent = $outboundMessage->conversation->projectMember->agent;

        if (! $agent || $agent->mac_machine_id !== $machine->id) {
            abort(403, 'Cette machine ne peut pas soumettre de résultats pour ce membre.');
        }

        $outboundMessage->update([
            'status' => 'done',
            'processed_at' => now(),
        ]);

        return Message::create([
            'conversation_id' => $outboundMessage->conversation_id,
            'direction' => 'out',
            'content' => $result,
            'status' => 'response',
            'processed_at' => now(),
        ]);
    }

    public function resetStuckMessages(int $minutes = 10): int
    {
        return Message::where('status', 'processing')
            ->where('updated_at', '<', now()->subMinutes($minutes))
            ->update(['status' => 'pending']);
    }
}

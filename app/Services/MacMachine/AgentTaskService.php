<?php
namespace App\Services\MacMachine;

use App\Models\AgentTask;
use App\Models\MacMachine;
use Illuminate\Database\Eloquent\Collection;

class AgentTaskService
{
    /**
     * Stale threshold: tasks in 'processing' for longer than this (minutes)
     * are considered stuck and reset to 'pending'.
     */
    const STALE_MINUTES = 10;

    /**
     * Get pending initialization tasks for agents assigned to this machine.
     * Also recovers stale 'processing' tasks that were never completed.
     */
    public function getPendingForMachine(MacMachine $machine): Collection
    {
        // Recover stale tasks stuck in 'processing' for too long
        $this->recoverStaleTasks($machine);

        return AgentTask::where('mac_machine_id', $machine->id)
            ->where('status', 'pending')
            ->with('agent')
            ->orderBy('created_at')
            ->get()
            ->each(fn(AgentTask $task) => $task->update(['status' => 'processing']));
    }

    /**
     * Reset tasks that have been in 'processing' for longer than STALE_MINUTES.
     */
    protected function recoverStaleTasks(MacMachine $machine): int
    {
        $cutoff = now()->subMinutes(self::STALE_MINUTES);

        $stale = AgentTask::where('mac_machine_id', $machine->id)
            ->where('status', 'processing')
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($stale as $task) {
            $task->update(['status' => 'pending']);
        }

        return $stale->count();
    }

    /**
     * Submit the result of an initialization task.
     */
    public function submitResult(AgentTask $task, MacMachine $machine, string $result = '', ?string $error = null): AgentTask
    {
        if ($task->mac_machine_id !== $machine->id) {
            abort(403, 'Machine mismatch for this task.');
        }

        if ($error) {
            $task->update([
                'status'        => 'error',
                'error_message' => $error,
                'processed_at'  => now(),
            ]);
            $task->agent->update(['status' => 'error']);
        } else {
            $task->update([
                'status'       => 'done',
                'result'       => $result,
                'processed_at' => now(),
            ]);

            if ($task->type === 'initialize' || $task->type === 'resync') {
                $task->agent->update([
                    'status'                      => 'ready',
                    'openclaw_profile_synced_at'  => now(),
                ]);
            } elseif ($task->type === 'destroy') {
                $task->agent->update(['status' => 'draft']);
            }
        }

        return $task;
    }
}
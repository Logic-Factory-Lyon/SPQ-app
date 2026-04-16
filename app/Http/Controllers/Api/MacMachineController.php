<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentTask;
use App\Models\MacMachine;
use App\Models\Message;
use App\Services\MacMachine\AgentTaskService;
use App\Services\MacMachine\MacMachinePollerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacMachineController extends Controller
{
    public function __construct(
        private readonly MacMachinePollerService $poller,
        private readonly AgentTaskService $taskService,
    ) {}

    /**
     * POST /api/mac/heartbeat
     * Mac Mini daemon calls this every 30s to stay "online".
     */
    public function heartbeat(Request $request): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');

        $metadata = $request->validate([
            'metadata' => 'nullable|array',
            'metadata.openclaw_version' => 'nullable|string|max:30',
            'metadata.os_version' => 'nullable|string|max:50',
            'metadata.hostname' => 'nullable|string|max:100',
            'metadata.openclaw_agents' => 'nullable|array',
            'metadata.openclaw_agents.*.name' => 'nullable|string|max:100',
            'metadata.openclaw_agents.*.profile' => 'nullable|string|max:100',
        ]);

        $machine->markSeen();

        if (! empty($metadata['metadata'])) {
            $machine->update(['metadata' => $metadata['metadata']]);
        }

        $response = ['status' => 'ok', 'timestamp' => now()->toIso8601String()];

        // If the machine has a pending daemon restart command, signal it
        if ($machine->metadata && ($machine->metadata['daemon_restart'] ?? false)) {
            $response['daemon_restart'] = true;
            $response['daemon_script_url'] = url('/daemon/script');
            // Clear the flag so it doesn't keep restarting
            $meta = $machine->metadata;
            unset($meta['daemon_restart']);
            $machine->update(['metadata' => $meta]);
        }

        return response()->json($response);
    }

    /**
     * GET /api/mac/messages/pending
     * Mac Mini polls this to get tasks to execute.
     */
    public function pendingMessages(Request $request): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');
        $machine->markSeen();

        $messages = $this->poller->getPendingForMachine($machine);

        $payload = $messages->map(fn(Message $msg) => [
            'id' => $msg->id,
            'conversation_id' => $msg->conversation_id,
            'openclaw_profile' => $msg->conversation->projectMember->agent?->profile,
            'content' => $msg->content,
        ]);

        return response()->json(['messages' => $payload]);
    }

    /**
     * POST /api/mac/messages/{message}/result
     * Mac Mini submits the result of an openclaw execution.
     */
    public function submitResult(Request $request, Message $message): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');

        $validated = $request->validate([
            'result' => 'nullable|string',
            'error' => 'nullable|string',
        ]);

        $error   = $validated['error'] ?? null;
        $result  = $validated['result'] ?? null;

        if ($error) {
            $message->update([
                'status' => 'error',
                'error_message' => $error,
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'error_recorded']);
        }

        $inbound = $this->poller->submitResult($message, $machine, $result ?? '');

        return response()->json([
            'status' => 'ok',
            'response_message_id' => $inbound->id,
        ]);
    }

    /**
     * GET /api/mac/tasks/pending
     * Mac Mini polls this for agent initialization/resync tasks.
     */
    public function pendingTasks(Request $request): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');

        $tasks = $this->taskService->getPendingForMachine($machine);

        $payload = $tasks->map(fn(AgentTask $task) => [
            'id'           => $task->id,
            'type'         => $task->type,
            'agent_id'     => $task->agent_id,
            'agent_profile'=> $task->agent->profile,
            'agent_name'   => $task->agent->name,
            'payload'      => $task->payload,
        ]);

        return response()->json(['tasks' => $payload]);
    }

    /**
     * POST /api/mac/tasks/{task}/result
     * Mac Mini submits the result of an initialization task.
     */
    public function submitTaskResult(Request $request, AgentTask $task): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');

        $validated = $request->validate([
            'result' => 'nullable|string',
            'error' => 'nullable|string',
        ]);

        $error  = $validated['error'] ?? null;
        $result = $validated['result'] ?? '';

        $this->taskService->submitResult($task, $machine, $result, $error);

        return response()->json([
            'status' => $error ? 'error_recorded' : 'ok',
            'agent_status' => $task->agent->fresh()->status,
        ]);
    }
}
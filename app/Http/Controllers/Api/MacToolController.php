<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\MacMachine;
use App\Models\Project;
use App\Models\SkillExecution;
use App\Services\MacMachine\ToolExecutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MacToolController extends Controller
{
    public function __construct(
        private readonly ToolExecutorService $executor,
    ) {}

    /**
     * POST /api/mac/tools/execute
     * Called by spq_bridge.py on the Mac Mini to execute a tool action.
     * The request is authenticated via the mac_machine token (auth.mac middleware).
     */
    public function execute(Request $request): JsonResponse
    {
        /** @var MacMachine $machine */
        $machine = $request->get('mac_machine');

        $validated = $request->validate([
            'endpoint'            => 'required|string|max:100',
            'payload'             => 'required|array',
            'skill_execution_id'  => 'nullable|integer|exists:skill_executions,id',
        ]);

        $endpoint = $validated['endpoint'];
        $payload  = $validated['payload'];
        $executionId = $validated['skill_execution_id'] ?? null;

        try {
            $result = $this->executor->execute($endpoint, $payload, $machine, $executionId);
            return response()->json($result);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/mac/skills/sync
     * Daemon pulls the full skill catalog to write to the central directory.
     */
    public function syncSkills(Request $request): JsonResponse
    {
        $skills = \App\Models\Skill::where('is_active', true)
            ->get()
            ->map(fn($s) => $s->toSkillJson());

        return response()->json([
            'skills'       => $skills,
            'tools_config' => $this->executor->getToolsConfig(),
        ]);
    }
}
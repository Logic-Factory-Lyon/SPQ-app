<?php

namespace App\Services\MacMachine;

use App\Models\Document;
use App\Models\MacMachine;
use App\Models\Project;
use App\Models\SkillExecution;

class ToolExecutorService
{
    /**
     * Execute a tool action called from spq_bridge.py on the Mac Mini.
     * Returns an array result that will be JSON-encoded back to the caller.
     */
    public function execute(string $endpoint, array $payload, MacMachine $machine, ?int $executionId = null): array
    {
        $execution = $executionId ? SkillExecution::find($executionId) : null;

        return match ($endpoint) {
            'website-test'       => $this->websiteTest($payload, $execution),
            'documents/create'   => $this->createDocument($payload, $execution),
            'memory/update'      => $this->updateMemory($payload, $execution),
            'mails/analyze'      => $this->analyzeMail($payload, $execution),
            'human/ask'          => $this->askHuman($payload, $execution),
            'search/info'        => $this->searchInfo($payload, $execution),
            default              => throw new \InvalidArgumentException("Unknown tool endpoint: {$endpoint}"),
        };
    }

    /**
     * Return the tools configuration JSON (for skills/sync).
     */
    public function getToolsConfig(): array
    {
        $bridgePath = base_path('daemon/../.openclaw/spqapp/tools/spq_bridge_tools.json');
        if (file_exists($bridgePath)) {
            return json_decode(file_get_contents($bridgePath), true) ?? [];
        }
        return [];
    }

    // ── Tool implementations ─────────────────────────────────────────────────

    protected function websiteTest(array $payload, ?SkillExecution $execution): array
    {
        $url = $payload['start_url'] ?? '';
        $instructions = $payload['instructions'] ?? '';

        // The actual testing is done by the OpenClaw agent on the Mac Mini.
        // This endpoint records the result and creates an artifact.
        if ($execution) {
            $execution->markRunning();
        }

        // For now, return the parameters back so the agent can proceed.
        // The real audit happens in the agent's OpenClaw session.
        return [
            'status'  => 'ok',
            'message' => 'Website test initiated. Agent will perform the audit.',
            'url'     => $url,
        ];
    }

    protected function createDocument(array $payload, ?SkillExecution $execution): array
    {
        $project = Project::find($payload['project_id'] ?? 0);
        if (! $project) {
            return ['status' => 'error', 'error' => 'Project not found'];
        }

        $doc = Document::create([
            'project_id' => $project->id,
            'title'      => $payload['title'] ?? 'Untitled',
            'content'    => $payload['content'] ?? '',
            'doc_type'   => $payload['doc_type'] ?? 'report',
            'agent_id'   => $execution?->agent_id,
        ]);

        if ($execution) {
            $artifacts = $execution->artifacts ?? [];
            $artifacts[] = [
                'document_id' => $doc->id,
                'type'        => $doc->doc_type,
                'title'       => $doc->title,
            ];
            $execution->update(['artifacts' => $artifacts]);
        }

        return [
            'status'      => 'ok',
            'document_id' => $doc->id,
            'title'       => $doc->title,
        ];
    }

    protected function updateMemory(array $payload, ?SkillExecution $execution): array
    {
        $project = Project::find($payload['project_id'] ?? 0);
        $layer = $payload['layer'] ?? 'project';
        $content = $payload['content'] ?? '';

        if (! $project) {
            return ['status' => 'error', 'error' => 'Project not found'];
        }

        // Memory files are stored on the Mac Mini filesystem.
        // The daemon will handle the actual file write when it processes the result.
        // Here we just validate and record the intent.
        if ($execution) {
            $artifacts = $execution->artifacts ?? [];
            $artifacts[] = [
                'type'   => 'memory_update',
                'layer'  => $layer,
                'size'   => strlen($content),
            ];
            $execution->update(['artifacts' => $artifacts]);
        }

        return [
            'status'  => 'ok',
            'layer'   => $layer,
            'message' => 'Memory update recorded. Daemon will write to filesystem.',
        ];
    }

    protected function analyzeMail(array $payload, ?SkillExecution $execution): array
    {
        // Mail analysis requires Microsoft Graph API OAuth2 integration.
        // This is a stub that will be implemented when OAuth2 is configured.
        return [
            'status'  => 'ok',
            'message' => 'Mail analysis is not yet configured. OAuth2 Microsoft Graph API required.',
            'action'  => $payload['action'] ?? 'unknown',
        ];
    }

    protected function askHuman(array $payload, ?SkillExecution $execution): array
    {
        $question = $payload['question'] ?? 'No question provided.';

        if ($execution) {
            $execution->markHumanApproval($question);
        }

        return [
            'status'  => 'ok',
            'message' => 'Human approval requested. Waiting for response.',
            'question' => $question,
        ];
    }

    protected function searchInfo(array $payload, ?SkillExecution $execution): array
    {
        // Search is performed by the OpenClaw agent itself using WebSearch/WebFetch tools.
        // This endpoint is for recording the action in the audit trail.
        return [
            'status'  => 'ok',
            'message' => 'Search recorded. Agent performs search directly via WebSearch.',
            'subject' => $payload['subject'] ?? '',
        ];
    }
}
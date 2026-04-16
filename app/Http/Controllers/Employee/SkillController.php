<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ProjectMember;
use App\Services\MacMachine\OpenClawDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function __construct(
        private readonly OpenClawDispatchService $dispatcher,
    ) {}

    private function getMember(): ProjectMember
    {
        $member = auth()->user()->projectMembers()->with('agent.skills')->first();
        abort_if(! $member, 403, __('app.no_project'));
        return $member;
    }

    /**
     * List available skills for the authenticated employee's agent.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $this->getMember();
        $agent = $member->agent;

        if (! $agent) {
            return response()->json(['skills' => []]);
        }

        $skills = $agent->skills()
            ->where('is_active', true)
            ->get()
            ->map(fn($s) => [
                'slug' => $s->slug,
                'name' => $s->name,
                'description' => $s->description,
                'icon' => $s->icon,
                'category' => $s->category,
                'param_fields' => $this->extractParamFields($s->prompt_template),
            ]);

        return response()->json(['skills' => $skills]);
    }

    /**
     * Dispatch a skill instruction to the agent.
     */
    public function dispatch(Request $request, Conversation $conversation): JsonResponse
    {
        $member = $this->getMember();
        abort_if($conversation->project_member_id !== $member->id, 403);

        $validated = $request->validate([
            'skill_slug' => 'required|string|exists:skills,slug',
            'params' => 'nullable|array',
            'params.*' => 'string|max:1000',
        ]);

        $agent = $member->agent;
        abort_if(! $agent, 403, __('app.no_agent'));

        // Security: verify the agent has this skill
        if (! $agent->hasSkill($validated['skill_slug'])) {
            abort(403, __('app.no_agent'));
        }

        // Save inbound message (user → system) with skill label
        Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'in',
            'message_type' => 'skill',
            'content' => $this->formatUserMessage($validated['skill_slug'], $validated['params'] ?? []),
            'status' => 'done',
            'metadata' => [
                'skill_slug' => $validated['skill_slug'],
                'params' => $validated['params'] ?? [],
            ],
        ]);

        // Queue outbound message with skill prompt
        $this->dispatcher->dispatch(
            $conversation,
            '',
            $validated['skill_slug'],
            $validated['params'] ?? [],
        );

        return response()->json(['status' => 'queued']);
    }

    /**
     * Extract {{param}} placeholders from a prompt template and return field definitions.
     */
    protected function extractParamFields(string $template): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $template, $matches);
        $fields = [];
        foreach ($matches[1] as $param) {
            $fields[] = [
                'key' => $param,
                'label' => ucfirst(str_replace('_', ' ', $param)),
                'placeholder' => '',
            ];
        }
        return $fields;
    }

    protected function formatUserMessage(string $skillSlug, array $params): string
    {
        $paramStr = collect($params)
            ->map(fn($v, $k) => "$k: $v")
            ->implode(', ');

        return "[Skill: {$skillSlug}]" . ($paramStr ? " — {$paramStr}" : '');
    }
}
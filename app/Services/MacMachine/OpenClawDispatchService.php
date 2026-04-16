<?php

namespace App\Services\MacMachine;

use App\Models\Agent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Skill;
use App\Models\SkillExecution;

class OpenClawDispatchService
{
    /**
     * Queue an outbound message for the Mac Mini daemon to pick up.
     * Supports both plain text messages and structured skill dispatches.
     */
    public function dispatch(
        Conversation $conversation,
        string $userContent,
        ?string $skillSlug = null,
        ?array $skillParams = null,
    ): Message {
        $messageType = 'text';
        $metadata = null;
        $content = $userContent;

        if ($skillSlug) {
            $skill = Skill::where('slug', $skillSlug)->where('is_active', true)->firstOrFail();

            // Build the prompt based on handler_type
            $content = match ($skill->handler_type) {
                'prompt'       => $this->buildSkillPrompt($skill, $skillParams ?? []),
                'native_tool'  => $this->buildNativeToolPrompt($skill, $skillParams ?? []),
                'composite'    => $this->buildCompositePrompt($skill, $skillParams ?? []),
                default        => $this->buildSkillPrompt($skill, $skillParams ?? []),
            };

            $messageType = 'skill';
            $metadata = [
                'skill_slug'    => $skillSlug,
                'skill_name'    => $skill->name,
                'handler_type'  => $skill->handler_type,
                'params'        => $skillParams,
                'allowed_tools' => $skill->allowed_tools,
                'skill_version' => $skill->version,
            ];

            // Create skill_execution record for audit trail
            $this->createExecution($conversation, $skill, $skillParams ?? []);
        }

        return Message::create([
            'conversation_id' => $conversation->id,
            'direction'      => 'out',
            'message_type'    => $messageType,
            'content'        => $content,
            'status'         => 'pending',
            'metadata'        => $metadata,
        ]);
    }

    /**
     * Create a SkillExecution record for the audit trail.
     */
    protected function createExecution(Conversation $conversation, Skill $skill, array $params): SkillExecution
    {
        $member = $conversation->projectMember;
        $agent = $member?->agent;
        $project = $member?->project;

        return SkillExecution::create([
            'skill_id'    => $skill->id,
            'message_id'  => 0, // Will be updated after message creation
            'project_id'  => $project?->id ?? 0,
            'agent_id'    => $agent?->id ?? 0,
            'employee_id' => $member?->user_id,
            'parameters'  => $params,
            'status'      => 'pending',
        ]);
    }

    /**
     * Update a SkillExecution with the actual message_id after message creation.
     */
    public function linkExecutionToMessage(SkillExecution $execution, Message $message): void
    {
        $execution->update(['message_id' => $message->id]);
    }

    /**
     * Build a prompt-only skill (handler_type = 'prompt').
     * Replaces {{param}} placeholders with actual values.
     */
    protected function buildSkillPrompt(Skill $skill, array $params): string
    {
        $template = $skill->prompt_template;

        foreach ($params as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }

        return $template;
    }

    /**
     * Build a native tool skill prompt (handler_type = 'native_tool').
     * Instructs the agent to use the SPQ bridge tools.
     */
    protected function buildNativeToolPrompt(Skill $skill, array $params): string
    {
        $paramStr = collect($params)
            ->map(fn($v, $k) => "- **{$k}**: {$v}")
            ->implode("\n");

        $tools = collect($skill->allowed_tools ?? [])
            ->map(fn($t) => "- `{$t}`")
            ->implode("\n");

        $prompt = <<<PROMPT
## Skill: {$skill->name}

{$skill->prompt_template}

### Parameters
{$paramStr}

### Available SPQ Tools
{$tools}

Use the SPQ bridge tools above to accomplish this task. Call the appropriate tool functions with the correct parameters. If you encounter an error or need human guidance, use `spq_ask_human`.
PROMPT;

        return $prompt;
    }

    /**
     * Build a composite skill prompt (handler_type = 'composite').
     * Combines prompt instructions with tool availability.
     */
    protected function buildCompositePrompt(Skill $skill, array $params): string
    {
        // First apply template substitution
        $content = $this->buildSkillPrompt($skill, $params);

        // Then append tool instructions
        $tools = collect($skill->allowed_tools ?? [])
            ->map(fn($t) => "- `{$t}`")
            ->implode("\n");

        if ($tools) {
            $content .= "\n\n### SPQ Tools available\n{$tools}\n\nUse these tools when needed to accomplish the task.";
        }

        return $content;
    }

    /**
     * Legacy: Replace {{param}} placeholders in a prompt template.
     * @deprecated Use buildSkillPrompt() instead
     */
    protected function buildSkillPromptFromTemplate(string $template, array $params): string
    {
        foreach ($params as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
}
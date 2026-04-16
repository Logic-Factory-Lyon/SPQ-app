<?php

namespace App\Services\MacMachine;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Skill;

class OpenClawDispatchService
{
    /**
     * Queue an outbound message for the Mac Mini daemon to pick up.
     * The Laravel app NEVER shells out to openclaw directly.
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

            // Build the prompt from the skill template
            $content = $this->buildSkillPrompt($skill->prompt_template, $skillParams ?? []);

            $messageType = 'skill';
            $metadata = [
                'skill_slug' => $skillSlug,
                'skill_name' => $skill->name,
                'params' => $skillParams,
                'allowed_tools' => $skill->allowed_tools,
            ];
        }

        return Message::create([
            'conversation_id' => $conversation->id,
            'direction' => 'out',
            'message_type' => $messageType,
            'content' => $content,
            'status' => 'pending',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Replace {{param}} placeholders in the skill prompt template with actual values.
     */
    protected function buildSkillPrompt(string $template, array $params): string
    {
        foreach ($params as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }
}
<?php
namespace App\Services;

use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\Conversation;
use App\Models\MacMachine;
use App\Models\Project;
use App\Models\ProjectMember;

class TeamCloningService
{
    /**
     * Clone an entire team (project + members + agents + skills).
     */
    public function cloneTeam(Project $source, array $data): Project
    {
        // 1. Create new project
        $newProject = Project::create([
            'client_id'   => $data['client_id'],
            'name'        => $data['name'] ?? 'Copie de ' . $source->name,
            'description' => $source->description,
            'status'      => 'active',
        ]);

        // 2. Assign MacMachine (create new or reuse)
        $machineId = $data['mac_machine_id'] ?? null;
        if ($machineId) {
            // Reuse existing machine
            $machine = MacMachine::findOrFail($machineId);
            $machine->update(['project_id' => $newProject->id]);
        } else {
            // Create a new machine for the project
            $sourceMachine = $source->macMachines()->first();
            $machine = MacMachine::create([
                'project_id' => $newProject->id,
                'name'       => $newProject->name . ' — Mac Mini',
                'status'     => 'unknown',
            ]);
        }

        // 3. Clone agents
        $agentCloneMap = []; // source_id => new_agent
        $agentsToClone = $data['agents'] ?? [];

        foreach ($source->allAgents as $sourceAgent) {
            $agentKey = 'agent_' . $sourceAgent->id;
            if (!isset($agentsToClone[$agentKey]) || !$agentsToClone[$agentKey]['clone']) {
                continue;
            }

            $cloneMode = $agentsToClone[$agentKey]['mode'] ?? 'clone'; // 'clone' or 'reuse'

            if ($cloneMode === 'reuse') {
                // Reuse the same agent (shared across projects)
                $agentCloneMap[$sourceAgent->id] = $sourceAgent;
                continue;
            }

            // Create a new agent instance
            $newAgent = Agent::create([
                'mac_machine_id'       => $machine->id,
                'name'                 => $sourceAgent->name,
                'profile'              => $data['agents'][$agentKey]['profile'] ?? $sourceAgent->profile,
                'description'          => $sourceAgent->description,
                'system_prompt'        => $sourceAgent->system_prompt,
                'workspace_path'       => null,
                'status'               => 'draft',
                'parent_agent_id'      => $sourceAgent->id,
            ]);

            // Copy skills
            $newAgent->skills()->sync($sourceAgent->skills->pluck('id')->toArray());

            $agentCloneMap[$sourceAgent->id] = $newAgent;

            // Create initialization task if requested
            if ($data['initialize_agents'] ?? true) {
                AgentTask::create([
                    'agent_id'       => $newAgent->id,
                    'mac_machine_id'  => $machine->id,
                    'type'            => 'initialize',
                    'status'          => 'pending',
                    'payload'         => [
                        'profile'       => $newAgent->profile,
                        'name'          => $newAgent->name,
                        'system_prompt' => $newAgent->system_prompt,
                        'project_id'    => $newAgent->project_id ?? 0,
                        'agent_id'      => $newAgent->id,
                        'skills'        => $newAgent->skills->map(fn($s) => [
                            'slug'            => $s->slug,
                            'name'            => $s->name,
                            'prompt_template' => $s->prompt_template,
                            'allowed_tools'   => $s->allowed_tools,
                        ])->values()->toArray(),
                    ],
                ]);
            }
        }

        // 4. Clone project members
        $memberCloneMap = [];
        $membersData = $data['members'] ?? [];

        foreach ($source->members as $sourceMember) {
            $memberKey = 'member_' . $sourceMember->id;

            if (isset($membersData[$memberKey]) && !$membersData[$memberKey]['include']) {
                continue;
            }

            $userId = $membersData[$memberKey]['user_id'] ?? $sourceMember->user_id;

            // Check if user is already a member of the new project
            $existing = ProjectMember::where('project_id', $newProject->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                $memberCloneMap[$sourceMember->id] = $existing;
                continue;
            }

            $newMember = ProjectMember::create([
                'project_id' => $newProject->id,
                'user_id'   => $userId,
                'role'      => $sourceMember->role,
                'agent_id'  => isset($agentCloneMap[$sourceMember->agent_id])
                    ? $agentCloneMap[$sourceMember->agent_id]->id
                    : null,
            ]);

            $memberCloneMap[$sourceMember->id] = $newMember;
        }

        // 5. Create empty conversations for each member with an agent
        foreach ($newProject->members as $member) {
            if ($member->agent) {
                Conversation::create([
                    'project_member_id' => $member->id,
                    'title'            => 'Conversation du ' . now()->format('d/m/Y H:i'),
                ]);
            }
        }

        return $newProject;
    }
}
<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTask;
use App\Models\MacMachine;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgentCrudController extends Controller
{
    public function index(): View
    {
        $agents = Agent::with(['macMachine', 'skills', 'parentAgent'])
            ->withCount('tasks as pending_tasks_count')
            ->latest()
            ->paginate(20);

        return view('superadmin.agents.index', compact('agents'));
    }

    public function create(): View
    {
        $machines = MacMachine::orderBy('name')->get();
        $skills = Skill::where('is_active', true)->orderBy('name')->get();
        $parentAgents = Agent::orderBy('name')->get();

        return view('superadmin.agents.create', compact('machines', 'skills', 'parentAgents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'profile'           => 'nullable|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'system_prompt'     => 'nullable|string|max:10000',
            'mac_machine_id'    => 'nullable|exists:mac_machines,id',
            'parent_agent_id'   => 'nullable|exists:agents,id',
            'skills'            => 'nullable|array',
            'skills.*'          => 'exists:skills,id',
            'telegram_bot_username' => 'nullable|string|max:255',
            'telegram_bot_token'    => 'nullable|string|max:255',
        ]);

        $agent = Agent::create([
            'name'                 => $validated['name'],
            'profile'              => $validated['profile'] ?? null,
            'description'          => $validated['description'] ?? null,
            'system_prompt'        => $validated['system_prompt'] ?? null,
            'mac_machine_id'       => $validated['mac_machine_id'] ?? null,
            'parent_agent_id'      => $validated['parent_agent_id'] ?? null,
            'project_id'           => null,
            'status'               => 'draft',
            'telegram_bot_username' => $validated['telegram_bot_username'] ?? null,
            'telegram_bot_token'   => $validated['telegram_bot_token'] ?? null,
        ]);

        if (!empty($validated['skills'])) {
            $agent->skills()->sync($validated['skills']);
        }

        return redirect()->route('admin.agents.edit', $agent)
            ->with('success', __('app.agent_created'));
    }

    public function edit(Agent $agent): View
    {
        $machines = MacMachine::orderBy('name')->get();
        $skills = Skill::where('is_active', true)->orderBy('name')->get();
        $parentAgents = Agent::where('id', '!=', $agent->id)->orderBy('name')->get();
        $agent->load('skills', 'macMachine');
        $latestTask = $agent->tasks()->latest()->first();

        return view('superadmin.agents.edit', compact('agent', 'machines', 'skills', 'parentAgents', 'latestTask'));
    }

    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'profile'           => 'nullable|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'system_prompt'     => 'nullable|string|max:10000',
            'mac_machine_id'    => 'nullable|exists:mac_machines,id',
            'parent_agent_id'   => 'nullable|exists:agents,id',
            'skills'            => 'nullable|array',
            'skills.*'          => 'exists:skills,id',
            'telegram_bot_username' => 'nullable|string|max:255',
            'telegram_bot_token'    => 'nullable|string|max:255',
        ]);

        $agent->update([
            'name'                 => $validated['name'],
            'profile'              => $validated['profile'] ?? null,
            'description'          => $validated['description'] ?? null,
            'system_prompt'        => $validated['system_prompt'] ?? null,
            'mac_machine_id'       => $validated['mac_machine_id'] ?? null,
            'parent_agent_id'      => $validated['parent_agent_id'] ?? null,
            'telegram_bot_username' => $validated['telegram_bot_username'] ?? null,
            'telegram_bot_token'   => $validated['telegram_bot_token'] ?? null,
        ]);

        $agent->skills()->sync($validated['skills'] ?? []);

        return redirect()->route('admin.agents.edit', $agent)
            ->with('success', __('app.agent_updated'));
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        // Queue a destroy task if the agent has a machine and was initialized
        if ($agent->mac_machine_id && $agent->status !== 'draft') {
            AgentTask::create([
                'agent_id'       => null, // agent will be deleted, so no FK ref
                'mac_machine_id' => $agent->mac_machine_id,
                'type'           => 'destroy',
                'status'         => 'pending',
                'payload'        => [
                    'profile'       => $agent->profile,
                    'name'          => $agent->name,
                    'project_id'    => $agent->project_id ?? 0,
                    'agent_id'      => $agent->id,
                ],
            ]);
        }

        $agent->delete();
        return redirect()->route('admin.agents.index')
            ->with('success', __('app.agent_deleted'));
    }

    public function initialize(Agent $agent): RedirectResponse
    {
        if (!$agent->mac_machine_id) {
            return back()->with('error', __('app.agent_needs_machine'));
        }

        if ($agent->status === 'initializing') {
            return back()->with('error', __('app.agent_already_initializing'));
        }

        $agent->update(['status' => 'initializing']);

        AgentTask::create([
            'agent_id'       => $agent->id,
            'mac_machine_id' => $agent->mac_machine_id,
            'type'           => 'initialize',
            'status'         => 'pending',
            'payload'        => [
                'profile'       => $agent->profile,
                'name'          => $agent->name,
                'system_prompt' => $agent->system_prompt,
                'project_id'    => $agent->project_id ?? 0,
                'agent_id'      => $agent->id,
                'skills'        => $agent->skills->map(fn($s) => $s->toSkillJson())->values()->toArray(),
            ],
        ]);

        return redirect()->route('admin.agents.edit', $agent)
            ->with('success', __('app.agent_initializing'));
    }

    public function resync(Agent $agent): RedirectResponse
    {
        if (!$agent->mac_machine_id) {
            return back()->with('error', __('app.agent_needs_machine'));
        }

        $agent->update(['status' => 'initializing']);

        AgentTask::create([
            'agent_id'       => $agent->id,
            'mac_machine_id' => $agent->mac_machine_id,
            'type'           => 'resync',
            'status'         => 'pending',
            'payload'        => [
                'profile'       => $agent->profile,
                'name'          => $agent->name,
                'system_prompt' => $agent->system_prompt,
                'project_id'    => $agent->project_id ?? 0,
                'agent_id'      => $agent->id,
                'skills'        => $agent->skills->map(fn($s) => $s->toSkillJson())->values()->toArray(),
            ],
        ]);

        return redirect()->route('admin.agents.edit', $agent)
            ->with('success', __('app.agent_resyncing'));
    }
}
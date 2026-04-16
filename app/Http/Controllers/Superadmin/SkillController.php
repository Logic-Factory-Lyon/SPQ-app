<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        $skills = Skill::withCount('agents')->orderBy('category')->orderBy('name')->get();
        return view('superadmin.skills.index', compact('skills'));
    }

    public function create(): View
    {
        return view('superadmin.skills.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSkill($request);
        Skill::create($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill créé avec succès.');
    }

    public function edit(Skill $skill): View
    {
        return view('superadmin.skills.edit', compact('skill'));
    }

    public function update(Request $request, Skill $skill): RedirectResponse
    {
        $validated = $this->validateSkill($request, $skill);
        $validated['version'] = $skill->version + 1;
        $skill->update($validated);

        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill mis à jour.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $skill->delete();
        return redirect()->route('admin.skills.index')
            ->with('success', 'Skill supprimé.');
    }

    /**
     * Attach a skill to an agent.
     */
    public function attach(Request $request, Project $project, Agent $agent, Skill $skill): RedirectResponse
    {
        abort_if($agent->project_id !== $project->id && $agent->macMachine?->project_id !== $project->id, 403);

        $agent->skills()->syncWithoutDetaching([$skill->id]);

        return back()->with('success', "Skill « {$skill->name} » ajouté à l'agent.");
    }

    /**
     * Detach a skill from an agent.
     */
    public function detach(Project $project, Agent $agent, Skill $skill): RedirectResponse
    {
        abort_if($agent->project_id !== $project->id && $agent->macMachine?->project_id !== $project->id, 403);

        $agent->skills()->detach($skill->id);

        return back()->with('success', "Skill « {$skill->name} » retiré de l'agent.");
    }

    /**
     * Shared validation logic for store and update.
     */
    protected function validateSkill(Request $request, ?Skill $skill = null): array
    {
        $slugRule = 'required|string|max:255|unique:skills,slug';
        if ($skill) {
            $slugRule .= ',' . $skill->id;
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'slug'              => $slugRule,
            'description'       => 'nullable|string',
            'icon'              => 'nullable|string|max:255',
            'category'          => 'nullable|string|max:255',
            'handler_type'      => 'required|in:prompt,native_tool,composite',
            'prompt_template'   => 'required|string',
            'parameter_schema'  => 'nullable|string',
            'output_schema'     => 'nullable|string',
            'allowed_tools'     => 'nullable|array',
            'allowed_tools.*'   => 'string',
            'action_handlers'   => 'nullable|array',
            'action_handlers.*' => 'string',
            'is_active'         => 'boolean',
        ]);

        // Decode JSON schema fields
        if ($validated['parameter_schema'] ?? null) {
            $validated['parameter_schema'] = json_decode($validated['parameter_schema'], true) ?: null;
        } else {
            $validated['parameter_schema'] = null;
        }

        if ($validated['output_schema'] ?? null) {
            $validated['output_schema'] = json_decode($validated['output_schema'], true) ?: null;
        } else {
            $validated['output_schema'] = null;
        }

        $validated['is_active'] = $validated['is_active'] ?? ($skill ? false : true);

        return $validated;
    }
}
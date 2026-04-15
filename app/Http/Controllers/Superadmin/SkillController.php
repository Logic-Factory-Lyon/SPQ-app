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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:skills',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'prompt_template' => 'required|string',
            'allowed_tools' => 'nullable|array',
            'allowed_tools.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:skills,slug,' . $skill->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'prompt_template' => 'required|string',
            'allowed_tools' => 'nullable|array',
            'allowed_tools.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
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
}
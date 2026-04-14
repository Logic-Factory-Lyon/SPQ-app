<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\MacMachine;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with(['client', 'macMachines'])
            ->withCount('members')
            ->latest();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $projects = $query->paginate(20)->withQueryString();
        return view('superadmin.projects.index', compact('projects'));
    }

    public function create(Client $client): View
    {
        return view('superadmin.projects.create', compact('client'));
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'machine_name' => 'required|string|max:100',
        ]);

        $project = Project::create([
            'client_id'   => $client->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => 'active',
        ]);

        $machine = MacMachine::create([
            'project_id' => $project->id,
            'name'       => $data['machine_name'],
        ]);

        return redirect()->route('admin.projects.show', $project)
            ->with('success', "Projet créé. Token machine : {$machine->token}");
    }

    public function show(Project $project): View
    {
        $project->load(['client', 'macMachines.agents', 'members.user', 'members.agent']);
        $telegramAgents = $project->telegramAgents()->get();
        return view('superadmin.projects.show', compact('project', 'telegramAgents'));
    }

    public function edit(Project $project): View
    {
        $clients = Client::active()->orderBy('name')->get();
        return view('superadmin.projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,suspended,cancelled',
        ]);

        $project->update($data);
        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Projet mis à jour.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->update(['status' => 'cancelled']);
        return redirect()->route('admin.projects.index')
            ->with('success', 'Projet annulé.');
    }
}

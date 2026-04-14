<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->client;
        $projects = $client->projects()->withCount('members')->with('macMachines')->latest()->paginate(20);
        return view('client.projects.index', compact('projects'));
    }

    public function show(Project $project): View
    {
        abort_if($project->client_id !== auth()->user()->client_id, 403);
        $project->load(['members.user', 'macMachines']);
        return view('client.projects.show', compact('project'));
    }
}

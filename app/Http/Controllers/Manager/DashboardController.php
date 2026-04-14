<?php
namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $member = auth()->user()->projectMembers()->with(['project.macMachines'])->first();
        $teamMembers = $member ? $member->project->members()->with(['user', 'conversations', 'agent'])->get() : collect();
        $agents = $member ? $member->project->agents()->with('macMachine')->get() : collect();
        $pendingMessages = $member
            ? Message::where('status', 'pending')
                ->whereHas('conversation.projectMember', fn($q) => $q->where('project_id', $member->project_id))
                ->count()
            : 0;

        return view('manager.dashboard', compact('member', 'teamMembers', 'pendingMessages', 'agents'));
    }
}

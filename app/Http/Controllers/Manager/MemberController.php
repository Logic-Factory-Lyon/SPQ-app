<?php
namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Services\Invitation\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private readonly InvitationService $invitations) {}

    private function authorizeProject(Project $project): void
    {
        $user = auth()->user();
        $member = $user->memberInProject($project->id);
        abort_if(! $member || ! $member->isManager(), 403);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $data = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => 'required|in:manager,employee',
        ]);

        $result = $this->invitations->invite($project, $data['email'], $data['role'], auth()->user());

        return match(true) {
            $result === 'already_member' => back()->with('error', __('members.already_member')),
            str_starts_with($result, 'added:') => back()->with('success', __('members.added', ['name' => substr($result, 6)])),
            default => back()->with('success', __('members.invited', ['email' => $data['email']])),
        };
    }

    public function assignAgent(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorizeProject($project);
        abort_if($member->project_id !== $project->id, 404);

        $data = $request->validate([
            'agent_id'         => 'nullable|exists:agents,id',
            'telegram_chat_id' => 'nullable|integer',
        ]);

        // Vérifier que l'agent appartient à ce projet (mac ou telegram)
        if ($data['agent_id']) {
            $agentOnProject = $project->allAgents()->find($data['agent_id']);
            abort_if(! $agentOnProject, 403, 'Cet agent n\'appartient pas à ce projet.');
        }

        $member->update([
            'agent_id'         => $data['agent_id'] ?: null,
            'telegram_chat_id' => $data['telegram_chat_id'] ?: null,
        ]);

        return back()->with('success', __('members.agent_assigned'));
    }

    public function destroy(Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorizeProject($project);
        abort_if($member->project_id !== $project->id, 404);
        abort_if($member->user_id === auth()->id(), 403, __('members.cannot_remove_self'));
        $member->delete();
        return back()->with('success', __('members.removed'));
    }

    public function cancelInvitation(Project $project, Invitation $invitation): RedirectResponse
    {
        $this->authorizeProject($project);
        abort_if($invitation->project_id !== $project->id, 404);
        $invitation->delete();
        return back()->with('success', __('members.invitation_cancelled'));
    }
}

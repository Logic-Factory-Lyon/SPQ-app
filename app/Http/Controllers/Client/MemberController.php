<?php
namespace App\Http\Controllers\Client;

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

    public function store(Request $request, Project $project): RedirectResponse
    {
        abort_if($project->client_id !== auth()->user()->client_id, 403);

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

    public function destroy(Project $project, ProjectMember $member): RedirectResponse
    {
        abort_if($project->client_id !== auth()->user()->client_id, 403);
        abort_if($member->project_id !== $project->id, 404);
        $member->delete();
        return back()->with('success', __('members.removed'));
    }

    public function cancelInvitation(Project $project, Invitation $invitation): RedirectResponse
    {
        abort_if($project->client_id !== auth()->user()->client_id, 403);
        abort_if($invitation->project_id !== $project->id, 404);
        $invitation->delete();
        return back()->with('success', __('members.invitation_cancelled'));
    }
}

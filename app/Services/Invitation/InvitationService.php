<?php
namespace App\Services\Invitation;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\Mail\MailService;

class InvitationService
{
    public function __construct(private readonly MailService $mail) {}

    public function invite(Project $project, string $email, string $role, User $inviter): string
    {
        $email = strtolower(trim($email));
        $existingUser = User::where('email', $email)->first();

        // Already member?
        if ($existingUser && ProjectMember::where('project_id', $project->id)->where('user_id', $existingUser->id)->exists()) {
            return 'already_member';
        }

        if ($existingUser) {
            // Add directly
            ProjectMember::create([
                'project_id' => $project->id,
                'user_id'    => $existingUser->id,
                'role'       => $role,
            ]);
            $this->mail->send('member_added', $existingUser, [
                'user_name'    => $existingUser->name,
                'project_name' => $project->name,
                'inviter_name' => $inviter->name,
                'app_url'      => config('app.url'),
            ]);
            return 'added:' . $existingUser->name;
        }

        // Cancel any existing pending invitation for same email+project
        Invitation::where('email', $email)->where('project_id', $project->id)->whereNull('accepted_at')->delete();

        $invitation = Invitation::create([
            'email'      => $email,
            'project_id' => $project->id,
            'invited_by' => $inviter->id,
            'role'       => $role,
        ]);

        $lang = app()->getLocale();
        $this->mail->send('invitation', $email, [
            'project_name' => $project->name,
            'inviter_name' => $inviter->name,
            'invitation_url' => route('invitation.accept', $invitation->token),
            'expires_at'   => $invitation->expires_at->format('d/m/Y'),
            'app_url'      => config('app.url'),
        ]);

        return 'invited';
    }
}

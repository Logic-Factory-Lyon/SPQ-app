<?php
namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function accept(string $token): View|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('login')->with('info', __('invitation.already_accepted'));
        }

        if ($invitation->isExpired()) {
            return view('invitation.expired', compact('invitation'));
        }

        // If user already exists and is logged in, accept immediately
        if (auth()->check() && auth()->user()->email === $invitation->email) {
            return $this->processAcceptance($invitation);
        }

        $existingUser = User::where('email', $invitation->email)->first();

        return view('invitation.accept', compact('invitation', 'existingUser'));
    }

    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        abort_if(! $invitation->isPending(), 410);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $invitation->email,
            'password' => Hash::make($data['password']),
            'role'     => $invitation->role === 'manager' ? 'manager' : 'employee',
        ]);

        return $this->processAcceptance($invitation, $user);
    }

    public function login(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        abort_if(! $invitation->isPending(), 410);

        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! auth()->attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return back()->withErrors(['password' => __('auth.failed')])->withInput();
        }

        if (auth()->user()->email !== $invitation->email) {
            auth()->logout();
            return back()->with('error', __('invitation.email_mismatch'));
        }

        return $this->processAcceptance($invitation);
    }

    private function processAcceptance(Invitation $invitation, ?User $user = null): RedirectResponse
    {
        $user = $user ?? auth()->user();

        // Check not already a member
        if (! ProjectMember::where('project_id', $invitation->project_id)->where('user_id', $user->id)->exists()) {
            ProjectMember::create([
                'project_id' => $invitation->project_id,
                'user_id'    => $user->id,
                'role'       => $invitation->role,
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        if (! auth()->check()) {
            auth()->login($user);
        }

        $redirect = match($user->role) {
            'manager'  => route('manager.dashboard'),
            'employee' => route('employee.dashboard'),
            default    => route('portal.dashboard'),
        };

        return redirect($redirect)->with('success', __('invitation.accepted', ['project' => $invitation->project->name]));
    }
}

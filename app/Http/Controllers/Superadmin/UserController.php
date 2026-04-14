<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('client')->latest();

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(25)->withQueryString();
        return view('superadmin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $clients = Client::active()->orderBy('name')->get();
        return view('superadmin.users.edit', compact('user', 'clients'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'role'      => 'required|in:superadmin,client,manager,employee',
            'client_id' => 'nullable|exists:clients,id',
            'password'  => 'nullable|min:8|confirmed',
            'locale'    => 'nullable|in:fr,en',
        ]);

        $update = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'client_id' => $data['client_id'] ?? null,
            'locale'    => $data['locale'] ?? 'fr',
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé.');
    }
}

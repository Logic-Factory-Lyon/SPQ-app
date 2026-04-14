<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::withCount(['projects', 'users'])
            ->with([])
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_first_name', 'like', "%{$search}%")
                  ->orWhere('contact_last_name', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(20)->withQueryString();
        return view('superadmin.clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('superadmin.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'contact_first_name'  => 'required|string|max:100',
            'contact_last_name'   => 'required|string|max:100',
            'email'               => 'required|email|unique:clients,email',
            'phone'               => 'nullable|string|max:30',
            'address_line1'       => 'nullable|string|max:255',
            'address_line2'       => 'nullable|string|max:255',
            'city'                => 'nullable|string|max:100',
            'zip_code'            => 'nullable|string|max:20',
            'country_code'        => 'nullable|string|size:2',
            'vat_number'          => 'nullable|string|max:30',
            'notes'               => 'nullable|string',
            // Optional: create a client portal user
            'create_user'         => 'nullable|boolean',
            'user_name'           => 'required_if:create_user,1|string|max:255|nullable',
            'user_email'          => 'required_if:create_user,1|email|unique:users,email|nullable',
            'user_password'       => ['nullable', 'required_if:create_user,1', Password::min(8)],
        ]);

        $client = Client::create([
            'name'               => $data['name'],
            'contact_first_name' => $data['contact_first_name'],
            'contact_last_name'  => $data['contact_last_name'],
            'email'              => $data['email'],
            'phone'              => $data['phone'] ?? null,
            'address_line1'      => $data['address_line1'] ?? null,
            'address_line2'      => $data['address_line2'] ?? null,
            'city'               => $data['city'] ?? null,
            'zip_code'           => $data['zip_code'] ?? null,
            'country_code'       => $data['country_code'] ?? 'FR',
            'vat_number'         => $data['vat_number'] ?? null,
            'notes'              => $data['notes'] ?? null,
        ]);

        if (! empty($data['create_user'])) {
            User::create([
                'client_id' => $client->id,
                'name'      => $data['user_name'],
                'email'     => $data['user_email'],
                'password'  => Hash::make($data['user_password']),
                'role'      => 'client',
            ]);
        }

        return redirect()->route('admin.clients.show', $client)
            ->with('success', "Client « {$client->name} » créé avec succès.");
    }

    public function show(Client $client): View
    {
        $client->loadCount(['projects', 'users', 'invoices']);
        $projects  = $client->projects()->withCount('members')->latest()->get();
        $users     = $client->users()->latest()->get();
        $invoices  = $client->invoices()->latest()->limit(5)->get();
        $outstandingBalance = $client->outstanding_balance;

        return view('superadmin.clients.show', compact('client', 'projects', 'users', 'invoices', 'outstandingBalance'));
    }

    public function edit(Client $client): View
    {
        return view('superadmin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'contact_first_name' => 'required|string|max:100',
            'contact_last_name'  => 'required|string|max:100',
            'email'              => "required|email|unique:clients,email,{$client->id}",
            'phone'              => 'nullable|string|max:30',
            'address_line1'      => 'nullable|string|max:255',
            'address_line2'      => 'nullable|string|max:255',
            'city'               => 'nullable|string|max:100',
            'zip_code'           => 'nullable|string|max:20',
            'country_code'       => 'nullable|string|size:2',
            'vat_number'         => 'nullable|string|max:30',
            'notes'              => 'nullable|string',
            'active'             => 'nullable|boolean',
        ]);

        $client->update($data);
        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client mis à jour.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->invoices()->whereIn('status', ['sent', 'overdue'])->exists()) {
            return back()->with('error', 'Impossible de supprimer un client avec des factures impayées.');
        }
        $client->delete();
        return redirect()->route('admin.clients.index')
            ->with('success', 'Client supprimé.');
    }
}

@extends('layouts.app')
@section('title', $client->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-white">Clients</a>
        <span>/</span>
        <span class="text-white">{{ $client->name }}</span>
    </div>
@endsection
@section('header-actions')
    <a href="{{ route('admin.clients.edit', $client) }}"
       class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Modifier
    </a>
@endsection
@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $client->name }}</h1>
            <p class="text-gray-400 mt-1">{{ $client->full_contact_name }} &mdash; {{ $client->email }}</p>
        </div>
        <x-badge :color="$client->active ? 'green' : 'gray'" class="text-sm px-3 py-1">
            {{ $client->active ? 'Actif' : 'Inactif' }}
        </x-badge>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="Projets" value="{{ $client->projects_count }}" />
        <x-stat-card label="Utilisateurs" value="{{ $client->users_count }}" />
        <x-stat-card label="Impayés" value="{{ number_format($outstandingBalance, 2, ',', ' ') }} €" color="{{ $outstandingBalance > 0 ? 'red' : 'green' }}" />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Billing info -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <h3 class="font-semibold text-white mb-4">Informations de facturation</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Adresse</dt><dd class="text-white text-right">{{ $client->full_address ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Téléphone</dt><dd class="text-white">{{ $client->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">N° TVA</dt><dd class="text-white font-mono">{{ $client->vat_number ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Stripe ID</dt><dd class="text-gray-400 font-mono text-xs">{{ $client->stripe_customer_id ?: '—' }}</dd></div>
            </dl>
        </div>

        <!-- Projects -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Projets</h3>
                <a href="{{ route('admin.clients.projects.create', $client) }}"
                   class="text-sm text-indigo-400 hover:text-indigo-300">+ Ajouter</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($projects as $project)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->members_count }} membre(s)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @php $colors = ['active' => 'green', 'suspended' => 'yellow', 'cancelled' => 'gray'] @endphp
                            @php $labels = ['active' => 'Actif', 'suspended' => 'Suspendu', 'cancelled' => 'Annulé'] @endphp
                            <x-badge :color="$colors[$project->status] ?? 'gray'">{{ $labels[$project->status] ?? $project->status }}</x-badge>
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">&rarr;</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">Aucun projet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Users -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">Utilisateurs</h3>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($users as $user)
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php $roleColors = ['client' => 'blue', 'manager' => 'indigo', 'employee' => 'gray'] @endphp
                        @php $roleLabels = ['client' => 'Client', 'manager' => 'Manager', 'employee' => 'Employé'] @endphp
                        <x-badge :color="$roleColors[$user->role] ?? 'gray'">{{ $roleLabels[$user->role] ?? $user->role }}</x-badge>
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-500 hover:text-indigo-400 text-sm">Modifier</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">Aucun utilisateur.</p>
            @endforelse
        </div>
    </div>

    <!-- Recent invoices -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">Dernières factures</h3>
            <a href="{{ route('admin.invoices.create') }}?client_id={{ $client->id }}" class="text-sm text-indigo-400 hover:text-indigo-300">+ Nouvelle facture</a>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($invoices as $invoice)
                @php $sc = ['draft' => 'gray', 'sent' => 'blue', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray'] @endphp
                @php $sl = ['draft' => 'Brouillon', 'sent' => 'Envoyée', 'paid' => 'Payée', 'overdue' => 'En retard', 'cancelled' => 'Annulée'] @endphp
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $invoice->number }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->issue_date?->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-white font-semibold text-sm">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} &euro;</span>
                        <x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-indigo-400 text-sm hover:text-indigo-300">&rarr;</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">Aucune facture.</p>
            @endforelse
        </div>
    </div>

    @if($client->notes)
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6 p-6">
        <h3 class="font-semibold text-white mb-3">Notes internes</h3>
        <p class="text-gray-400 text-sm whitespace-pre-wrap">{{ $client->notes }}</p>
    </div>
    @endif
@endsection

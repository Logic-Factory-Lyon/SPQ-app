@extends('layouts.app')
@section('title', 'Clients')
@section('header')
    <h2 class="text-lg font-semibold text-white">Clients</h2>
@endsection
@section('header-actions')
    <a href="{{ route('admin.clients.create') }}"
       class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nouveau client
    </a>
@endsection
@section('content')
    <x-page-header title="Comptes clients" subtitle="Gérez vos clients et leurs accès." />

    <!-- Search -->
    <form method="GET" class="mb-6 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client..."
            class="flex-1 max-w-sm bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">
            Rechercher
        </button>
        @if(request('search'))
            <a href="{{ route('admin.clients.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-400 text-sm px-4 py-2 rounded-lg transition-colors">
                Effacer
            </a>
        @endif
    </form>

    @if($clients->isEmpty())
        <x-empty-state title="Aucun client" description="Créez votre premier compte client."
            action="Nouveau client" actionUrl="{{ route('admin.clients.create') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Société</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Contact</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Projets</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Utilisateurs</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($clients as $client)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $client->name }}</div>
                            <div class="text-xs text-gray-500">{{ $client->email }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $client->full_contact_name }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $client->projects_count }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $client->users_count }}</td>
                        <td class="px-5 py-4">
                            <x-badge :color="$client->active ? 'green' : 'gray'">
                                {{ $client->active ? 'Actif' : 'Inactif' }}
                            </x-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.clients.show', $client) }}"
                               class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Voir &rarr;</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $clients->links() }}</div>
    @endif
@endsection

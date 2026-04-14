@extends('layouts.app')
@section('title', 'Projets')
@section('header')
    <h2 class="text-lg font-semibold text-white">Projets</h2>
@endsection
@section('content')
    <x-page-header title="Tous les projets" />
    <form method="GET" class="mb-6 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
            class="flex-1 max-w-sm bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Tous statuts</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
        </select>
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">Filtrer</button>
    </form>

    @if($projects->isEmpty())
        <x-empty-state title="Aucun projet" description="Les projets sont créés depuis la fiche client." />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Projet</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Client</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">Membres</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($projects as $project)
                    @php
                        $colors = ['active' => 'green', 'suspended' => 'yellow', 'cancelled' => 'gray'];
                        $labels = ['active' => 'Actif', 'suspended' => 'Suspendu', 'cancelled' => 'Annulé'];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $project->name }}</div>
                            @if($project->macMachines->count())
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $project->macMachines->count() }} machine(s)
                                    @php $online = $project->macMachines->where('status', 'online')->count() @endphp
                                    @if($online) &mdash; <span class="text-green-400">{{ $online }} en ligne</span>@endif
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $project->client->name }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $project->members_count }}</td>
                        <td class="px-5 py-4">
                            <x-badge :color="$colors[$project->status] ?? 'gray'">{{ $labels[$project->status] ?? $project->status }}</x-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Voir &rarr;</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $projects->links() }}</div>
    @endif
@endsection

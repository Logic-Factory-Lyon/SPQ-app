@extends('layouts.app')
@section('title', 'Mes projets')
@section('content')
    <x-page-header title="Mes projets" />

    @if($projects->isEmpty())
        <x-empty-state title="Aucun projet" description="Vous n'avez pas encore de projet actif." />
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($projects as $project)
            <a href="{{ route('portal.projects.show', $project) }}"
               class="block bg-gray-900 rounded-xl border border-gray-800 hover:border-indigo-700 p-5 transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-700/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <x-badge color="green">Actif</x-badge>
                </div>
                <h3 class="font-semibold text-white mb-1">{{ $project->name }}</h3>
                @if($project->description)
                    <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $project->description }}</p>
                @endif
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span>{{ $project->members_count }} membre(s)</span>
                    @if($project->macMachines->isNotEmpty())
                        <span class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full {{ $project->macMachines->first()->status === 'online' ? 'bg-green-400' : 'bg-gray-500' }}"></div>
                            Mac Mini {{ $project->macMachines->first()->status === 'online' ? 'actif' : 'inactif' }}
                        </span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $projects->links() }}</div>
    @endif
@endsection

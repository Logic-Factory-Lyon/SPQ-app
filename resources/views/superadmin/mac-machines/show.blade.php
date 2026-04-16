@extends('layouts.app')
@section('title', $macMachine->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.projects.show', $macMachine->project) }}" class="hover:text-white">{{ $macMachine->project->name }}</a>
        <span>/</span>
        <span class="text-white">{{ $macMachine->name }}</span>
    </div>
@endsection
@section('header-actions')
    <form method="POST" action="{{ route('admin.mac-machines.restart-daemon', $macMachine) }}">
        @csrf
        <button type="submit" onclick="return confirm('Demander un redémarrage du daemon ? Il se mettra à jour automatiquement au prochain heartbeat.')"
            class="bg-yellow-600 hover:bg-yellow-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            ↺ Restart Daemon
        </button>
    </form>
    <a href="{{ route('admin.mac-machines.edit', $macMachine) }}"
       class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Modifier
    </a>
@endsection
@section('content')
    <x-page-header title="{{ $macMachine->name }}"
        subtitle="Projet : {{ $macMachine->project->name }} &mdash; Client : {{ $macMachine->project->client->name }}" />

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        @php $statusColor = ['online' => 'green', 'offline' => 'red', 'unknown' => 'gray'][$macMachine->status] ?? 'gray' @endphp
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm text-gray-400 mb-2">Statut</p>
            <x-badge :color="$statusColor" class="text-sm px-3 py-1">
                {{ ['online' => 'En ligne', 'offline' => 'Hors ligne', 'unknown' => 'Inconnu'][$macMachine->status] ?? $macMachine->status }}
            </x-badge>
            @if($macMachine->last_seen_at)
                <p class="text-xs text-gray-600 mt-2">Vu {{ $macMachine->last_seen_at->diffForHumans() }}</p>
            @endif
        </div>
        <x-stat-card label="Messages en attente" value="{{ $pendingCount }}" :color="$pendingCount > 0 ? 'yellow' : 'green'" />
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm text-gray-400 mb-2">Profil agent</p>
            <p class="text-white font-mono text-sm">{{ $macMachine->profile ?? '—' }}</p>
            <p class="text-xs text-gray-600 mt-1">{{ $macMachine->metadata['hostname'] ?? '' }} — v{{ $macMachine->metadata['openclaw_version'] ?? '?' }} (daemon)</p>
        </div>
    </div>

    <!-- Token + Launcher -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-semibold text-white">Token API &amp; Daemon</h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.mac-machines.setup-guide', $macMachine) }}"
                   class="flex items-center gap-1.5 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Guide d'installation
                </a>
                <a href="{{ route('admin.mac-machines.launcher', $macMachine) }}"
                   class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Télécharger le launcher
                </a>
                <form method="POST" action="{{ route('admin.mac-machines.regenerate-token', $macMachine) }}">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('Régénérer le token ? Le launcher devra être retéléchargé.')"
                        class="text-sm text-red-400 hover:text-red-300 transition-colors">
                        Régénérer le token
                    </button>
                </form>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg px-4 py-3 font-mono text-sm text-green-400 break-all mb-3">
            {{ $macMachine->token }}
        </div>
        <div class="mt-3 pt-3 border-t border-gray-800 text-xs text-gray-600">
            Téléchargez le launcher <code class="text-gray-500">.command</code> ci-dessus, puis dans Terminal : <code class="text-gray-400">bash ~/Downloads/spq_{{ Str::slug($macMachine->name) }}.command</code>
        </div>
    </div>

    <!-- Metadata debug -->
    @if($macMachine->metadata)
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <h3 class="font-semibold text-white mb-3">Métadonnées daemon</h3>
        <div class="space-y-2">
            <div class="flex gap-3 text-sm">
                <span class="text-gray-500 w-40 shrink-0">Hostname</span>
                <span class="text-gray-300 font-mono">{{ $macMachine->metadata['hostname'] ?? '—' }}</span>
            </div>
            <div class="flex gap-3 text-sm">
                <span class="text-gray-500 w-40 shrink-0">OS</span>
                <span class="text-gray-300 font-mono">{{ $macMachine->metadata['os_version'] ?? '—' }}</span>
            </div>
            <div class="flex gap-3 text-sm">
                <span class="text-gray-500 w-40 shrink-0">Version daemon</span>
                <span class="text-gray-300 font-mono">{{ $macMachine->metadata['openclaw_version'] ?? '—' }}</span>
            </div>
            <div class="flex gap-3 text-sm">
                <span class="text-gray-500 w-40 shrink-0">Agents détectés</span>
                <div>
                    @php $detectedAgents = $macMachine->metadata['openclaw_agents'] ?? []; @endphp
                    @if(count($detectedAgents) > 0)
                        <div class="space-y-1">
                            @foreach($detectedAgents as $da)
                                <span class="inline-block bg-gray-800 rounded px-2 py-0.5 text-xs text-green-400 font-mono">
                                    {{ $da['name'] }} — <code>--profile {{ $da['profile'] }}</code>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-yellow-500 text-xs">Aucun agent détecté — le daemon n'a pas encore envoyé de liste de profils.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent messages -->
    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <div class="px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">Messages récents</h3>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($recentMessages as $msg)
                @php
                    $sc = ['pending' => 'yellow', 'processing' => 'blue', 'done' => 'green', 'error' => 'red'];
                    $sl = ['pending' => 'En attente', 'processing' => 'En cours', 'done' => 'Terminé', 'error' => 'Erreur'];
                @endphp
                <div class="px-5 py-3 flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-300 truncate">{{ Str::limit($msg->content, 80) }}</p>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $msg->created_at->diffForHumans() }}</p>
                    </div>
                    <x-badge :color="$sc[$msg->status] ?? 'gray'">{{ $sl[$msg->status] ?? $msg->status }}</x-badge>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">Aucun message récent.</p>
            @endforelse
        </div>
    </div>
@endsection

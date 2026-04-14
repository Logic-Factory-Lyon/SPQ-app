@extends('layouts.app')
@section('title', $project->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.projects.index') }}" class="hover:text-white">Projets</a>
        <span>/</span>
        <span class="text-white">{{ $project->name }}</span>
    </div>
@endsection
@section('header-actions')
    <a href="{{ route('admin.projects.edit', $project) }}"
       class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Modifier
    </a>
@endsection
@section('content')
    @php
        $colors = ['active' => 'green', 'suspended' => 'yellow', 'cancelled' => 'gray'];
        $labels = ['active' => 'Actif', 'suspended' => 'Suspendu', 'cancelled' => 'Annulé'];
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
            <p class="text-gray-400 mt-1">
                Client : <a href="{{ route('admin.clients.show', $project->client) }}" class="text-indigo-400 hover:text-indigo-300">{{ $project->client->name }}</a>
            </p>
            @if($project->description)
                <p class="text-gray-500 text-sm mt-1">{{ $project->description }}</p>
            @endif
        </div>
        <x-badge :color="$colors[$project->status] ?? 'gray'" class="text-sm px-3 py-1">
            {{ $labels[$project->status] ?? $project->status }}
        </x-badge>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Telegram Bots + Mac Machines + Agents -->
        <div class="space-y-4">

            <!-- ── Bots Telegram ──────────────────────────────────────────── -->
            <div class="bg-gray-900 rounded-xl border border-gray-800">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                    <h3 class="font-semibold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.944 0A12 12 0 1 0 24 12 12 12 0 0 0 11.944 0zm5.8 8.226-2.01 9.47c-.15.704-.545.876-1.104.545l-3.04-2.24-1.465 1.41c-.162.162-.298.298-.61.298l.218-3.087 5.63-5.086c.245-.217-.054-.338-.378-.121l-6.96 4.384-2.996-.938c-.652-.204-.663-.652.136-.965l11.7-4.51c.543-.197 1.018.133.879.84z"/>
                        </svg>
                        Bots Telegram
                    </h3>
                </div>

                <div class="divide-y divide-gray-800">
                    @forelse($telegramAgents as $tAgent)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-white font-medium text-sm">{{ $tAgent->name }}</p>
                                    <code class="text-xs text-gray-500">{{ substr($tAgent->telegram_bot_token, 0, 12) }}…</code>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('admin.projects.agents.register-webhook', [$project, $tAgent]) }}">
                                        @csrf
                                        <button type="submit" class="text-xs bg-blue-700 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition-colors">
                                            ↺ Webhook
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.projects.agents.destroy', [$project, $tAgent]) }}"
                                          onsubmit="return confirm('Supprimer ce bot ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                            @if($tAgent->system_prompt)
                                <p class="text-xs text-gray-600 mt-1 italic truncate">{{ Str::limit($tAgent->system_prompt, 120) }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center py-4">Aucun bot Telegram configuré.</p>
                    @endforelse
                </div>

                <!-- Formulaire d'ajout d'un bot Telegram -->
                <div class="border-t border-gray-800 px-5 py-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Ajouter un bot</p>
                    <form method="POST" action="{{ route('admin.projects.agents.store', $project) }}" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="name" placeholder="Nom affiché (ex: Nestor)" required
                                class="flex-1 min-w-0 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <input type="text" name="telegram_bot_username" placeholder="@username du bot (ex: nestor_bot)" required
                                class="flex-1 min-w-0 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white font-mono placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <input type="text" name="telegram_bot_token" placeholder="Token API (optionnel — pour sync historique)"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white font-mono placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-700 hover:bg-blue-600 text-white text-xs font-medium px-4 py-2 rounded-lg transition-colors">
                            + Ajouter le bot
                        </button>
                    </form>
                    <p class="text-xs text-gray-600 mt-2">Le username crée un lien <code class="text-gray-400">t.me/username</code> pour les employés. Le token est optionnel et sert uniquement à la synchronisation de l'historique.</p>
                </div>
            </div>
            <div class="bg-gray-900 rounded-xl border border-gray-800">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                    <h3 class="font-semibold text-white">Mac Mini(s)</h3>
                </div>
                <div class="divide-y divide-gray-800">
                    @forelse($project->macMachines as $machine)
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full {{ $machine->status === 'online' ? 'bg-green-400' : ($machine->status === 'offline' ? 'bg-red-400' : 'bg-gray-500') }}"></div>
                                    <span class="text-white font-medium">{{ $machine->name }}</span>
                                </div>
                                <a href="{{ route('admin.mac-machines.show', $machine) }}" class="text-indigo-400 text-sm hover:text-indigo-300">Gérer &rarr;</a>
                            </div>
                            @if($machine->last_seen_at)
                                <p class="text-xs text-gray-600">Dernière activité : {{ $machine->last_seen_at->diffForHumans() }}</p>
                            @endif
                            <!-- Agents de cette machine -->
                            @if($machine->agents->isNotEmpty())
                            <div class="mt-2 space-y-1">
                                @foreach($machine->agents as $ag)
                                <div class="flex items-center justify-between bg-gray-800 rounded-lg px-3 py-2">
                                    <div>
                                        <span class="text-sm text-white">{{ $ag->name }}</span>
                                        <code class="text-xs text-gray-500 ml-2">--profile {{ $ag->profile }}</code>
                                    </div>
                                    <form method="POST" action="{{ route('admin.projects.agents.destroy', [$project, $ag]) }}" onsubmit="return confirm('Supprimer cet agent ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">Supprimer</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            <!-- Ajouter un agent -->
                            @php $availableAgents = $machine->metadata['openclaw_agents'] ?? []; @endphp
                            <form method="POST" action="{{ route('admin.projects.agents.store', $project) }}" class="mt-2 flex flex-wrap gap-2">
                                @csrf
                                <input type="hidden" name="mac_machine_id" value="{{ $machine->id }}">
                                <input type="text" name="name" placeholder="Nom affiché" required
                                    class="flex-1 min-w-0 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @if(count($availableAgents) > 0)
                                    <select name="profile" required
                                        class="w-44 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">— Choisir un profil —</option>
                                        @foreach($availableAgents as $av)
                                            <option value="{{ $av['profile'] }}">{{ $av['name'] }} ({{ $av['profile'] }})</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" name="profile" placeholder="--profile (ex: default)" required
                                        class="w-44 bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white font-mono placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @if($machine->status !== 'online')
                                        <p class="w-full text-xs text-gray-600 mt-0.5">Machine hors ligne — les profils disponibles s'afficheront ici une fois connectée.</p>
                                    @else
                                        <p class="w-full text-xs text-gray-600 mt-0.5">Aucun profil détecté — vérifiez que le daemon est à jour.</p>
                                    @endif
                                @endif
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    + Agent
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm text-center py-6">Aucune machine configurée.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Members -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">@lang('members.members') ({{ $project->members->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($project->members as $member)
                    @php
                        $rc = ['manager' => 'indigo', 'employee' => 'gray'];
                        $rl = ['manager' => __('members.role_manager'), 'employee' => __('members.role_employee')];
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold">
                                {{ strtoupper(substr($member->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $member->user->name }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $member->user->email }}
                                    @if($member->agent) · Agent : <span class="text-indigo-400">{{ $member->agent->name }}</span>@endif
                                    @if($member->telegram_chat_id) · <span class="text-blue-400">Telegram ✓</span>@endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :color="$rc[$member->role] ?? 'gray'">{{ $rl[$member->role] ?? $member->role }}</x-badge>
                            <form method="POST" action="{{ route('admin.projects.members.destroy', [$project, $member]) }}" onsubmit="return confirm('@lang('members.remove_confirm')')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">@lang('members.remove_member')</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">@lang('members.no_members')</p>
                @endforelse
            </div>

            <!-- Pending Invitations -->
            @php $pendingInvitations = $project->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->get(); @endphp
            @if($pendingInvitations->count())
                <div class="border-t border-gray-800 px-5 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">@lang('members.pending_invitations')</p>
                    @foreach($pendingInvitations as $inv)
                        <div class="flex items-center justify-between py-1.5">
                            <div>
                                <span class="text-sm text-gray-300">{{ $inv->email }}</span>
                                <span class="text-xs text-gray-500 ml-2">@lang('members.invitation_expires') {{ $inv->expires_at->format('d/m/Y') }}</span>
                            </div>
                            <form method="POST" action="{{ route('admin.projects.invitations.cancel', [$project, $inv]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-gray-500 hover:text-red-400 transition-colors">@lang('members.cancel_invitation')</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Invite Form -->
            <div class="border-t border-gray-800 px-5 py-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">@lang('members.invite')</p>
                <form method="POST" action="{{ route('admin.projects.members.store', $project) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <input type="email" name="email" placeholder="@lang('members.invite_email')"
                           class="flex-1 min-w-0 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           required>
                    <select name="role" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="employee">@lang('members.role_employee')</option>
                        <option value="manager">@lang('members.role_manager')</option>
                    </select>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                        @lang('members.send_invitation')
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

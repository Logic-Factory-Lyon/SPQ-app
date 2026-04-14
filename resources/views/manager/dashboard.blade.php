@extends('layouts.app')
@section('title', 'Dashboard Manager')
@section('content')
    <x-page-header title="Tableau de bord"
        subtitle="{{ $member ? 'Projet : ' . $member->project->name : '' }}" />

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="Membres de l'équipe" value="{{ $teamMembers->count() }}" />
        <x-stat-card label="Messages en attente" value="{{ $pendingMessages }}" :color="$pendingMessages > 0 ? 'yellow' : 'green'" />
        @if($member)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-sm text-gray-400 mb-2">Agents disponibles</p>
            <p class="text-white font-bold">{{ $agents->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $agents->filter(fn($a) => $a->macMachine?->status === 'online')->count() }} en ligne
            </p>
        </div>
        @endif
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">Membres de l'équipe</h3>
            <a href="{{ route('manager.conversations.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">Voir toutes les conversations →</a>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($teamMembers as $tm)
            <div class="px-5 py-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($tm->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $tm->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $tm->user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php $rc = ['manager'=>'indigo','employee'=>'gray']; $rl = ['manager'=>__('members.role_manager'),'employee'=>__('members.role_employee')] @endphp
                        <x-badge :color="$rc[$tm->role] ?? 'gray'">{{ $rl[$tm->role] ?? $tm->role }}</x-badge>
                        @if($tm->user_id !== auth()->id())
                        <form method="POST" action="{{ route('manager.projects.members.destroy', [$member->project, $tm]) }}" onsubmit="return confirm('@lang('members.remove_confirm')')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors">@lang('members.remove_member')</button>
                        </form>
                        @endif
                    </div>
                </div>
                @if($tm->role === 'employee' && $agents->isNotEmpty())
                <div class="mt-2 ml-11">
                    <form method="POST" action="{{ route('manager.projects.members.assign-agent', [$member->project, $tm]) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="agent_id" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— @lang('members.no_agent') —</option>
                            @foreach($agents as $ag)
                            <option value="{{ $ag->id }}" {{ $tm->agent_id === $ag->id ? 'selected' : '' }}>
                                {{ $ag->name }} {{ $ag->macMachine?->status === 'online' ? '●' : '○' }}
                            </option>
                            @endforeach
                        </select>
                        <button type="submit" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">@lang('members.assign_agent')</button>
                    </form>
                </div>
                @endif
            </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">@lang('members.no_members')</p>
            @endforelse
        </div>

        @if($member)
        <!-- Pending Invitations -->
        @php $pendingInvitations = $member->project->invitations()->whereNull('accepted_at')->where('expires_at', '>', now())->get(); @endphp
        @if($pendingInvitations->count())
        <div class="border-t border-gray-800 px-5 py-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">@lang('members.pending_invitations')</p>
            @foreach($pendingInvitations as $inv)
            <div class="flex items-center justify-between py-1.5">
                <div>
                    <span class="text-sm text-gray-300">{{ $inv->email }}</span>
                    <span class="text-xs text-gray-500 ml-2">@lang('members.invitation_expires') {{ $inv->expires_at->format('d/m/Y') }}</span>
                </div>
                <form method="POST" action="{{ route('manager.projects.invitations.cancel', [$member->project, $inv]) }}">
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
            <form method="POST" action="{{ route('manager.projects.members.store', $member->project) }}" class="flex flex-wrap gap-2">
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
        @endif
    </div>
@endsection

@extends('layouts.app')
@section('title', $project->name)
@section('content')
    <x-page-header title="{{ $project->name }}" subtitle="Détails du projet" />

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <!-- Project info -->
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
                <h3 class="font-semibold text-white mb-4">Informations</h3>
                <div class="space-y-3 text-sm">
                    @if($project->description)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Description</p>
                        <p class="text-gray-300">{{ $project->description }}</p>
                    </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Statut</p>
                            <x-badge color="green">Actif</x-badge>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Membres</p>
                            <p class="text-white font-semibold">{{ $project->members->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team members -->
            <div class="bg-gray-900 rounded-xl border border-gray-800">
                <div class="px-5 py-4 border-b border-gray-800">
                    <h3 class="font-semibold text-white">@lang('members.members') ({{ $project->members->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-800">
                    @forelse($project->members as $member)
                    @php $rc = ['manager'=>'indigo','employee'=>'gray']; $rl = ['manager'=>__('members.role_manager'),'employee'=>__('members.role_employee')] @endphp
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $member->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->user->email }}</p>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <x-badge :color="$rc[$member->role] ?? 'gray'">{{ $rl[$member->role] ?? $member->role }}</x-badge>
                            <form method="POST" action="{{ route('portal.projects.members.destroy', [$project, $member]) }}" onsubmit="return confirm('@lang('members.remove_confirm')')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300">@lang('members.remove_member')</button>
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
                        <form method="POST" action="{{ route('portal.projects.invitations.cancel', [$project, $inv]) }}">
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
                    <form method="POST" action="{{ route('portal.projects.members.store', $project) }}" class="flex flex-wrap gap-2">
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

        <!-- Sidebar -->
        <div class="space-y-4">
            @if($project->macMachines->isNotEmpty())
            @php $machine = $project->macMachines->first() @endphp
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Infrastructure</p>
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-2 h-2 rounded-full {{ $machine->status === 'online' ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></div>
                    <span class="text-white font-semibold">{{ $machine->name }}</span>
                </div>
                <p class="text-xs text-gray-500">
                    Mac Mini — {{ $machine->status === 'online' ? 'En ligne' : 'Hors ligne' }}
                </p>
            </div>
            @endif

            <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Navigation</p>
                <div class="space-y-2">
                    <a href="{{ route('portal.invoices.index') }}"
                       class="flex items-center gap-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Mes factures
                    </a>
                    <a href="{{ route('portal.quotes.index') }}"
                       class="flex items-center gap-2 text-sm text-gray-300 hover:text-white hover:bg-gray-800 px-3 py-2 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Mes devis
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

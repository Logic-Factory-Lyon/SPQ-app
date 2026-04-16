@extends('layouts.app')
@section('title', __('app.clone_team'))
@section('content')
    <x-page-header title="{{ __('app.clone_team') }}" subtitle="{{ $project->name }}" />

    <form method="POST" action="{{ route('admin.projects.clone', $project) }}" class="max-w-3xl space-y-6">
        @csrf

        {{-- Project info --}}
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-5">
            <h3 class="text-white font-semibold">{{ __('app.new_project') }}</h3>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.project_name') }}</label>
                <input type="text" name="name" value="{{ old('name', 'Copie de ' . $project->name) }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.target_client') }}</label>
                <select name="client_id" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $project->client_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.machine') }}</label>
                <select name="mac_machine_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('app.create_new_machine') }} —</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ old('mac_machine_id') == $machine->id ? 'selected' : '' }}>
                            {{ $machine->name }} ({{ $machine->status }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Agents to clone --}}
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
            <h3 class="text-white font-semibold">{{ __('app.agents_to_clone') }}</h3>

            @forelse($project->macMachines as $machine)
                @foreach($machine->agents as $agent)
                <div class="flex items-center gap-4 bg-gray-800 rounded-lg px-4 py-3">
                    <label class="flex items-center gap-2 flex-1">
                        <input type="checkbox" name="agents[agent_{{ $agent->id }}][clone]" value="1" checked
                            class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <p class="text-white text-sm font-medium">{{ $agent->name }}</p>
                            <p class="text-gray-500 text-xs">{{ $agent->profile }} · {{ $machine->name }}</p>
                        </div>
                    </label>
                    <select name="agents[agent_{{ $agent->id }}][mode]"
                        class="bg-gray-900 border border-gray-700 rounded px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="clone">{{ __('app.clone_new_instance') }}</option>
                        <option value="reuse">{{ __('app.reuse_shared') }}</option>
                    </select>
                    <input type="hidden" name="agents[agent_{{ $agent->id }}][profile]" value="{{ $agent->profile }}">
                </div>
                @endforeach
            @empty
                <p class="text-gray-500 text-sm">{{ __('app.no_agents') }}</p>
            @endforelse

            @foreach($telegramAgents as $agent)
            <div class="flex items-center gap-4 bg-gray-800 rounded-lg px-4 py-3">
                <label class="flex items-center gap-2 flex-1">
                    <input type="checkbox" name="agents[agent_{{ $agent->id }}][clone]" value="1" checked
                        class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-white text-sm font-medium">{{ $agent->name }}</p>
                        <p class="text-gray-500 text-xs">Telegram · @{{ $agent->telegram_bot_username }}</p>
                    </div>
                </label>
                <input type="hidden" name="agents[agent_{{ $agent->id }}][mode]" value="reuse">
            </div>
            @endforeach
        </div>

        {{-- Members to clone --}}
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
            <h3 class="text-white font-semibold">{{ __('app.members_to_clone') }}</h3>

            @foreach($project->members as $member)
            <div class="flex items-center gap-4 bg-gray-800 rounded-lg px-4 py-3">
                <label class="flex items-center gap-2 flex-1">
                    <input type="checkbox" name="members[member_{{ $member->id }}][include]" value="1" checked
                        class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <p class="text-white text-sm font-medium">{{ $member->user->name }}</p>
                        <p class="text-gray-500 text-xs">{{ $member->role }} · {{ $member->user->email }}</p>
                    </div>
                </label>
                <input type="hidden" name="members[member_{{ $member->id }}][user_id]" value="{{ $member->user_id }}">
            </div>
            @endforeach
        </div>

        {{-- Initialize agents checkbox --}}
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="initialize_agents" value="1" checked
                    class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500 w-5 h-5">
                <div>
                    <p class="text-white font-medium">{{ __('app.initialize_agents') }}</p>
                    <p class="text-gray-500 text-sm">{{ __('app.initialize_agents_help') }}</p>
                </div>
            </label>
        </div>

        {{-- Memory (stub for future) --}}
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-3">
            <h3 class="text-white font-semibold">{{ __('app.memory_cloning') }}</h3>
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" checked disabled class="rounded border-gray-600 text-indigo-600">
                    {{ __('app.memory_core') }}
                    <span class="text-xs text-gray-500">({{ __('app.always_cloned') }})</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" checked disabled class="rounded border-gray-600 text-indigo-600">
                    {{ __('app.memory_company') }}
                    <span class="text-xs text-gray-500">({{ __('app.always_cloned') }})</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" checked class="rounded border-gray-600 text-indigo-600">
                    {{ __('app.memory_project') }}
                </label>
            </div>
            <p class="text-xs text-gray-600">{{ __('app.memory_cloning_note') }}</p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.clone_team') }}
            </button>
            <a href="{{ route('admin.projects.show', $project) }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
@endsection
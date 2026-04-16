@extends('layouts.app')
@section('title', __('app.edit_agent'))
@section('content')
    <x-page-header title="{{ $agent->name }}" subtitle="{{ $agent->profile }}" />

    {{-- Status + actions bar --}}
    <div class="flex items-center gap-4 mb-6 p-4 bg-gray-900 rounded-xl border border-gray-800">
        @php
            $statusColors = [
                'draft' => 'bg-gray-700 text-gray-300',
                'initializing' => 'bg-yellow-900/50 text-yellow-300',
                'ready' => 'bg-green-900/50 text-green-300',
                'error' => 'bg-red-900/50 text-red-300',
            ];
        @endphp
        <span class="px-3 py-1 rounded text-sm font-medium {{ $statusColors[$agent->status] ?? 'bg-gray-700 text-gray-300' }}">
            {{ $agent->status }}
        </span>
        @if($agent->openclaw_profile_synced_at)
            <span class="text-xs text-gray-500">{{ __('app.last_sync') }}: {{ $agent->openclaw_profile_synced_at->diffForHumans() }}</span>
        @endif
        <div class="ml-auto flex gap-2">
            @if(in_array($agent->status, ['draft', 'error']) && $agent->mac_machine_id)
            <form method="POST" action="{{ route('admin.agents.initialize', $agent) }}">
                @csrf
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.initialize_openclaw') }}
                </button>
            </form>
            @endif
            @if($agent->status === 'ready' && $agent->mac_machine_id)
            <form method="POST" action="{{ route('admin.agents.resync', $agent) }}">
                @csrf
                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors border border-gray-700">
                    {{ __('app.resync') }}
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Latest task result --}}
    @if($latestTask && $latestTask->status === 'error')
    <div class="mb-6 p-4 bg-red-900/30 border border-red-800 rounded-xl text-red-300 text-sm">
        <p class="font-medium mb-1">{{ __('app.last_task_error') }}</p>
        <pre class="text-xs overflow-x-auto whitespace-pre-wrap">{{ $latestTask->error_message }}</pre>
    </div>
    @endif

    @if($latestTask && $latestTask->status === 'done')
    <div class="mb-6 p-4 bg-green-900/30 border border-green-800 rounded-xl text-green-300 text-sm">
        <p class="font-medium">{{ __('app.last_task_success') }}</p>
        @if($latestTask->result)
            <pre class="text-xs overflow-x-auto whitespace-pre-wrap mt-1">{{ Str::limit($latestTask->result, 500) }}</pre>
        @endif
    </div>
    @endif

    <form method="POST" action="{{ route('admin.agents.update', $agent) }}" class="max-w-2xl space-y-6">
        @csrf @method('PUT')

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.agent_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $agent->name) }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.agent_profile') }}</label>
                <input type="text" name="profile" value="{{ old('profile', $agent->profile) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.description') }}</label>
                <textarea name="description" rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description', $agent->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.machine') }}</label>
                <select name="mac_machine_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('app.no_machine') }} —</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ $agent->mac_machine_id == $machine->id ? 'selected' : '' }}>
                            {{ $machine->name }} ({{ $machine->status }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.parent_agent') }}</label>
                <select name="parent_agent_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('app.no_parent') }} —</option>
                    @foreach($parentAgents as $parent)
                        <option value="{{ $parent->id }}" {{ $agent->parent_agent_id == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.workspace_path') }}</label>
                <input type="text" name="workspace_path" value="{{ old('workspace_path', $agent->workspace_path) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="~/.openclaw/agents/my-agent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.system_prompt') }}</label>
                <textarea name="system_prompt" rows="8"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm resize-y">{{ old('system_prompt', $agent->system_prompt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.skills') }}</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($skills as $skill)
                    <label class="flex items-center gap-2 bg-gray-800 border {{ $agent->skills->contains($skill->id) ? 'border-indigo-600' : 'border-gray-700' }} rounded-lg px-3 py-2 cursor-pointer hover:border-indigo-600 transition-colors">
                        <input type="checkbox" name="skills[]" value="{{ $skill->id }}"
                            {{ $agent->skills->contains($skill->id) ? 'checked' : '' }}
                            class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-300">{{ $skill->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Telegram fields --}}
        <details class="bg-gray-900 rounded-xl border border-gray-800 p-6" {{ $agent->isTelegram() ? 'open' : '' }}>
            <summary class="text-sm font-medium text-gray-400 cursor-pointer hover:text-white">{{ __('app.telegram_settings') }}</summary>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.telegram_bot_username') }}</label>
                    <input type="text" name="telegram_bot_username" value="{{ old('telegram_bot_username', $agent->telegram_bot_username) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="@my_bot">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.telegram_bot_token') }}</label>
                    <input type="text" name="telegram_bot_token" value="{{ old('telegram_bot_token', $agent->telegram_bot_token) }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="123456:ABC-DEF">
                </div>
            </div>
        </details>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.save') }}
            </button>
            <a href="{{ route('admin.agents.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.cancel') }}
            </a>
            <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}" class="ml-auto"
                  onsubmit="return confirm('Supprimer cet agent ? Le workspace OpenClaw et les fichiers seront également supprimés.')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-gray-800 hover:bg-red-900 text-gray-400 hover:text-red-400 text-sm px-4 py-2 rounded-lg transition-colors border border-gray-700">
                    {{ __('app.delete') }}
                </button>
            </form>
        </div>
    </form>
@endsection
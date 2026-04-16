@extends('layouts.app')
@section('title', __('app.add_agent'))
@section('content')
    <x-page-header title="{{ __('app.add_agent') }}" />

    <form method="POST" action="{{ route('admin.agents.store') }}" class="max-w-2xl space-y-6">
        @csrf

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.agent_name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    placeholder="{{ __('app.agent_name_placeholder') }}">
                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.agent_profile') }}</label>
                <input type="text" name="profile" value="{{ old('profile') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="{{ __('app.agent_profile_placeholder') }}">
                <p class="mt-1 text-xs text-gray-500">{{ __('app.agent_profile_help') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.description') }}</label>
                <textarea name="description" rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.machine') }}</label>
                <select name="mac_machine_id"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— {{ __('app.no_machine') }} —</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" {{ old('mac_machine_id') == $machine->id ? 'selected' : '' }}>
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
                        <option value="{{ $parent->id }}" {{ old('parent_agent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.system_prompt') }}</label>
                <textarea name="system_prompt" rows="6"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-sm resize-y">{{ old('system_prompt') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">{{ __('app.system_prompt_help') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.skills') }}</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($skills as $skill)
                    <label class="flex items-center gap-2 bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 cursor-pointer hover:border-indigo-600 transition-colors">
                        <input type="checkbox" name="skills[]" value="{{ $skill->id }}"
                            {{ in_array($skill->id, old('skills', [])) ? 'checked' : '' }}
                            class="rounded border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-300">{{ $skill->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Telegram fields (collapsible) --}}
        <details class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <summary class="text-sm font-medium text-gray-400 cursor-pointer hover:text-white">{{ __('app.telegram_settings') }}</summary>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.telegram_bot_username') }}</label>
                    <input type="text" name="telegram_bot_username" value="{{ old('telegram_bot_username') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="@my_bot">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">{{ __('app.telegram_bot_token') }}</label>
                    <input type="text" name="telegram_bot_token" value="{{ old('telegram_bot_token') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="123456:ABC-DEF">
                </div>
            </div>
        </details>

        <div class="flex gap-3">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.create') }}
            </button>
            <a href="{{ route('admin.agents.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.cancel') }}
            </a>
        </div>
    </form>
@endsection
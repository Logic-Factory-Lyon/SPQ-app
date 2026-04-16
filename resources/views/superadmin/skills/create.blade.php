@extends('layouts.app')
@section('title', __('app.new_skill_title'))
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.skills.index') }}" class="hover:text-white">{{ __('app.skills') }}</a>
        <span>/</span>
        <span class="text-white">{{ __('app.new_skill_title') }}</span>
    </div>
@endsection
@section('content')
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.skills.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.name_short') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.slug') }}</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="ex: test-website">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.description') }}</label>
                <textarea name="description" rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.icon_heroicons') }}</label>
                    <input type="text" name="icon" value="{{ old('icon') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="ex: o-globe-alt">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.category') }}</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="ex: Audit, Documentation">
                </div>
            </div>

            {{-- Handler Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.handler_type') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.handler_type_help') }}</p>
                <select name="handler_type" class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="prompt" {{ old('handler_type', 'prompt') === 'prompt' ? 'selected' : '' }}>Prompt</option>
                    <option value="native_tool" {{ old('handler_type') === 'native_tool' ? 'selected' : '' }}>Native Tool</option>
                    <option value="composite" {{ old('handler_type') === 'composite' ? 'selected' : '' }}>Composite</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.prompt_template') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.prompt_template_help') }}</p>
                <textarea name="prompt_template" rows="5" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('prompt_template') }}</textarea>
            </div>

            {{-- Parameter Schema (JSON) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.parameter_schema') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.parameter_schema_help') }}</p>
                <textarea name="parameter_schema" rows="6"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('parameter_schema') }}</textarea>
            </div>

            {{-- Output Schema (JSON) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.output_schema') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.output_schema_help') }}</p>
                <textarea name="output_schema" rows="4"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('output_schema') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.allowed_tools') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.allowed_tools_help_short') }}</p>
                <input type="text" name="allowed_tools" value="{{ old('allowed_tools', '[]') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            {{-- Action Handlers --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">{{ __('app.action_handlers') }}</label>
                <p class="text-xs text-gray-500 mb-1">{{ __('app.action_handlers_help') }}</p>
                <input type="text" name="action_handlers" value="{{ old('action_handlers', '[]') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                    class="rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-gray-300">{{ __('app.skill_active') }}</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
                <a href="{{ route('admin.skills.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">{{ __('app.cancel') }}</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.create') }}
                </button>
            </div>
        </form>
    </div>
@endsection
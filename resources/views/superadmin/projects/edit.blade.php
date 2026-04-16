@extends('layouts.app')
@section('title', __('app.edit') . ' ' . $project->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.projects.index') }}" class="hover:text-white">{{ __('app.projects') }}</a>
        <span>/</span>
        <a href="{{ route('admin.projects.show', $project) }}" class="hover:text-white">{{ $project->name }}</a>
        <span>/</span>
        <span class="text-white">{{ __('app.edit') }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="{{ __('app.edit') }} {{ $project->name }}" />
    <div class="max-w-lg">
        <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.project_name') }} *</label>
                    <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.description') }}</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.status') }} *</label>
                    <select name="status" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>{{ __('app.status_active') }}</option>
                        <option value="suspended" {{ old('status', $project->status) === 'suspended' ? 'selected' : '' }}>{{ __('app.status_suspended') }}</option>
                        <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>{{ __('app.status_cancelled') }}</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.save') }}
                </button>
                <a href="{{ route('admin.projects.show', $project) }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@extends('layouts.app')
@section('title', 'Nouveau projet')
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.clients.show', $client) }}" class="hover:text-white">{{ $client->name }}</a>
        <span>/</span>
        <span class="text-white">Nouveau projet</span>
    </div>
@endsection
@section('content')
    <x-page-header title="Nouveau projet pour {{ $client->name }}" />
    <div class="max-w-lg">
        <form method="POST" action="{{ route('admin.clients.projects.store', $client) }}" class="space-y-5">
            @csrf
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nom du projet *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nom du Mac Mini *</label>
                    <input type="text" name="machine_name" value="{{ old('machine_name', 'Mac Mini #1') }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('machine_name') border-red-500 @enderror">
                    @error('machine_name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">Un token API sera généré automatiquement pour cette machine.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Créer le projet
                </button>
                <a href="{{ route('admin.clients.show', $client) }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection

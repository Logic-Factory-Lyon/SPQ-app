@extends('layouts.app')
@section('title', 'Modifier ' . $macMachine->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.projects.show', $macMachine->project) }}" class="hover:text-white">{{ $macMachine->project->name }}</a>
        <span>/</span>
        <a href="{{ route('admin.mac-machines.show', $macMachine) }}" class="hover:text-white">{{ $macMachine->name }}</a>
        <span>/</span>
        <span class="text-white">Modifier</span>
    </div>
@endsection
@section('content')
    <x-page-header title="Modifier {{ $macMachine->name }}" />
    <div class="max-w-sm">
        <form method="POST" action="{{ route('admin.mac-machines.update', $macMachine) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nom de la machine *</label>
                    <input type="text" name="name" value="{{ old('name', $macMachine->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Profil agent (--profile)</label>
                    <input type="text" name="profile" value="{{ old('profile', $macMachine->profile) }}"
                        placeholder="ex: default, sales, support..."
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('profile') border-red-500 @enderror">
                    <p class="text-xs text-gray-600 mt-1">Nom du profil passé au daemon (argument --profile)</p>
                    @error('profile')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Enregistrer
                </button>
                <a href="{{ route('admin.mac-machines.show', $macMachine) }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Annuler
                </a>
                <button type="button" class="bg-red-900/50 hover:bg-red-900 text-red-400 font-semibold px-6 py-2.5 rounded-lg transition-colors ml-auto"
                    onclick="if(confirm('Supprimer cette machine ? Cette action est irréversible.')) document.getElementById('delete-form-machine').submit()">
                    Supprimer
                </button>
            </div>
        </form>

        <form id="delete-form-machine" method="POST" action="{{ route('admin.mac-machines.destroy', $macMachine) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

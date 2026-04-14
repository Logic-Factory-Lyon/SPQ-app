@extends('layouts.app')
@section('title', 'Modifier ' . $user->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.users.index') }}" class="hover:text-white">Utilisateurs</a>
        <span>/</span>
        <span class="text-white">{{ $user->name }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="Modifier {{ $user->name }}" subtitle="{{ $user->email }}" />
    <div class="max-w-lg">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Rôle *</label>
                    <select name="role" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="superadmin" {{ old('role', $user->role) === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                        <option value="client" {{ old('role', $user->role) === 'client' ? 'selected' : '' }}>Client</option>
                        <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                        <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>Employé</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Client associé</label>
                    <select name="client_id"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Aucun —</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $user->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Langue</label>
                    <select name="locale"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="fr" {{ old('locale', $user->locale ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ old('locale', $user->locale ?? 'fr') === 'en' ? 'selected' : '' }}>English</option>
                    </select>
                </div>
            </div>

            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <h3 class="text-white font-semibold">Changer le mot de passe</h3>
                <p class="text-xs text-gray-500">Laissez vide pour conserver le mot de passe actuel.</p>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nouveau mot de passe</label>
                    <input type="password" name="password"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                    @error('password')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Enregistrer
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Annuler
                </a>
                @if($user->id !== auth()->id())
                    <button type="button" class="bg-red-900/50 hover:bg-red-900 text-red-400 font-semibold px-6 py-2.5 rounded-lg transition-colors ml-auto"
                        onclick="if(confirm('Supprimer cet utilisateur ?')) document.getElementById('delete-form-user').submit()">
                        Supprimer
                    </button>
                @endif
            </div>
        </form>

        @if($user->id !== auth()->id())
            <form id="delete-form-user" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
@endsection

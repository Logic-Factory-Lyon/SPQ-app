@extends('layouts.app')
@section('title', 'Nouveau skill')
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.skills.index') }}" class="hover:text-white">Skills</a>
        <span>/</span>
        <span class="text-white">Nouveau</span>
    </div>
@endsection
@section('content')
    <div class="max-w-2xl mx-auto">
        <form method="POST" action="{{ route('admin.skills.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Nom</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="ex: test-website">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Icône (Heroicons)</label>
                    <input type="text" name="icon" value="{{ old('icon') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="ex: o-globe-alt">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Catégorie</label>
                    <input type="text" name="category" value="{{ old('category') }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="ex: Audit, Documentation">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Template de prompt</label>
                <p class="text-xs text-gray-500 mb-1">Utilisez {{param}} pour les variables dynamiques (ex: {{url}}, {{focus}}).</p>
                <textarea name="prompt_template" rows="5" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('prompt_template') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Outils autorisés (JSON)</label>
                <p class="text-xs text-gray-500 mb-1">Liste des outils OpenClaw que l'agent peut utiliser pour ce skill.</p>
                <input type="text" name="allowed_tools" value="{{ old('allowed_tools', '[]') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                    class="rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-gray-300">Skill actif</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
                <a href="{{ route('admin.skills.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">Annuler</a>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Créer
                </button>
            </div>
        </form>
    </div>
@endsection
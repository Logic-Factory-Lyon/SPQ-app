@extends('layouts.app')
@section('title', 'Skills')
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <span class="text-white">Skills</span>
    </div>
@endsection
@section('header-actions')
    <a href="{{ route('admin.skills.create') }}"
       class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Nouveau skill
    </a>
@endsection
@section('content')
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Nom</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Catégorie</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Slug</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Agents</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide">Statut</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wide"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($skills as $skill)
                <tr class="hover:bg-gray-900/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($skill->icon)
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.59L5.5 13.5h13l-3.591-3.092A2.25 2.25 0 0114.25 8.818V3.104a.75.75 0 00-.75-.75h-3.5a.75.75 0 00-.75.75z"/>
                            </svg>
                            @endif
                            <span class="text-white font-medium text-sm">{{ $skill->name }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($skill->description, 80) }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $skill->category ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $skill->slug }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $skill->agents_count }}</td>
                    <td class="px-4 py-3">
                        @if($skill->is_active)
                        <span class="text-xs bg-green-900/50 text-green-400 px-2 py-0.5 rounded">Actif</span>
                        @else
                        <span class="text-xs bg-gray-800 text-gray-500 px-2 py-0.5 rounded">Inactif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.skills.edit', $skill) }}" class="text-sm text-indigo-400 hover:text-indigo-300">Modifier</a>
                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="inline ml-2" onsubmit="return confirm('Supprimer ce skill ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-400 hover:text-red-300">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
@extends('layouts.app')
@section('title', 'Nouveau taux TVA')
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.vat-rates.index') }}" class="hover:text-white">Taux de TVA</a>
        <span>/</span>
        <span class="text-white">Nouveau</span>
    </div>
@endsection
@section('content')
    <x-page-header title="Nouveau taux de TVA" />
    <div class="max-w-sm">
        <form method="POST" action="{{ route('admin.vat-rates.store') }}" class="space-y-5">
            @csrf
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Libellé *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="TVA 20%"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Taux (décimal) *</label>
                    <input type="number" name="rate" value="{{ old('rate') }}" required
                        min="0" max="1" step="0.001" placeholder="0.20"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('rate') border-red-500 @enderror">
                    @error('rate')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    <p class="text-xs text-gray-500 mt-1">Exemple : 0.20 pour 20%, 0.055 pour 5,5%</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Créer
                </button>
                <a href="{{ route('admin.vat-rates.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
@endsection

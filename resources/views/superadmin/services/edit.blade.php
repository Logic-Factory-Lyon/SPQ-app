@extends('layouts.app')
@section('title', 'Modifier ' . $service->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.services.index') }}" class="hover:text-white">Services</a>
        <span>/</span>
        <span class="text-white">{{ $service->name }}</span>
    </div>
@endsection
@section('content')
    <x-page-header title="Modifier {{ $service->name }}" />
    <div class="max-w-lg">
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Nom *</label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $service->description) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Prix unitaire HT (€) *</label>
                        <input type="number" name="unit_price_ht" value="{{ old('unit_price_ht', $service->unit_price_ht) }}" required
                            min="0" step="0.01"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('unit_price_ht') border-red-500 @enderror">
                        @error('unit_price_ht')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1.5">Taux de TVA *</label>
                        <select name="vat_rate_id" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('vat_rate_id') border-red-500 @enderror">
                            <option value="">Choisir...</option>
                            @foreach($vatRates as $vatRate)
                                <option value="{{ $vatRate->id }}" {{ old('vat_rate_id', $service->vat_rate_id) == $vatRate->id ? 'selected' : '' }}>
                                    {{ $vatRate->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vat_rate_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Type de facturation *</label>
                    <select name="billing_type" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="one_time" {{ old('billing_type', $service->billing_type) === 'one_time' ? 'selected' : '' }}>Unique</option>
                        <option value="monthly" {{ old('billing_type', $service->billing_type) === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                        <option value="yearly" {{ old('billing_type', $service->billing_type) === 'yearly' ? 'selected' : '' }}>Annuel</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Enregistrer
                </button>
                <a href="{{ route('admin.services.index') }}"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                    Annuler
                </a>
                <button type="button" form="delete-form" class="ml-auto bg-red-900/50 hover:bg-red-900 text-red-400 font-semibold px-6 py-2.5 rounded-lg transition-colors"
                    onclick="if(confirm('Supprimer ce service ?')) document.getElementById('delete-form').submit()">
                    Supprimer
                </button>
            </div>
        </form>

        <form id="delete-form" method="POST" action="{{ route('admin.services.destroy', $service) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

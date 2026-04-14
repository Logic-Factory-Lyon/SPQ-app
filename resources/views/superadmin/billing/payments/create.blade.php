@extends('layouts.app')
@section('title', 'Nouveau paiement — ' . $invoice->number)
@section('content')
<x-page-header title="Enregistrer un paiement" subtitle="Facture : {{ $invoice->number }} — {{ $invoice->client->name }}" />

<form method="POST" action="{{ route('admin.payments.store', $invoice) }}" class="space-y-6 max-w-lg">
    @csrf

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <div class="mb-4 p-4 bg-gray-800 rounded-lg">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Total TTC</p>
                    <p class="text-white font-bold">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</p>
                </div>
                <div>
                    <p class="text-gray-500">Solde restant</p>
                    <p class="text-red-400 font-bold">{{ number_format($invoice->remaining_balance, 2, ',', ' ') }} €</p>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Montant (€) *</label>
                <input type="number" name="amount" step="0.01" min="0.01"
                    value="{{ old('amount', $invoice->remaining_balance) }}" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('amount') border-red-500 @enderror">
                @error('amount')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Méthode de paiement *</label>
                <select name="method" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('method') border-red-500 @enderror">
                    <option value="">— Sélectionner —</option>
                    <option value="bank_transfer" {{ old('method') === 'bank_transfer' ? 'selected' : '' }}>Virement bancaire</option>
                    <option value="stripe" {{ old('method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                    <option value="cheque" {{ old('method') === 'cheque' ? 'selected' : '' }}>Chèque</option>
                    <option value="cash" {{ old('method') === 'cash' ? 'selected' : '' }}>Espèces</option>
                    <option value="other" {{ old('method') === 'other' ? 'selected' : '' }}>Autre</option>
                </select>
                @error('method')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Date de paiement</label>
                <input type="date" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Référence</label>
                <input type="text" name="reference" value="{{ old('reference') }}"
                    placeholder="N° de virement, chèque, etc."
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Notes</label>
                <textarea name="notes" rows="2"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
            Enregistrer le paiement
        </button>
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
            Annuler
        </a>
    </div>
</form>
@endsection

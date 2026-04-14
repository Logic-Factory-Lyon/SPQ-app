@extends('layouts.app')
@section('title', 'Devis ' . $quote->number)
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.quotes.downloadPdf', $quote) }}" target="_blank"
           class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Télécharger PDF
        </a>
        @if($quote->isEditable())
            <a href="{{ route('admin.quotes.edit', $quote) }}"
               class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Modifier
            </a>
        @endif
        @if($quote->status === 'draft')
            <form method="POST" action="{{ route('admin.quotes.send', $quote) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Marquer envoyé
                </button>
            </form>
        @endif
        @if($quote->isConvertible())
            <form method="POST" action="{{ route('admin.quotes.convertToInvoice', $quote) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Convertir en facture
                </button>
            </form>
        @endif
        @if($quote->isDraft())
            <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}"
                onsubmit="return confirm('Supprimer ce devis ?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="flex items-center gap-2 bg-red-900/50 hover:bg-red-800 text-red-400 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Supprimer
                </button>
            </form>
        @endif
    </div>
@endsection
@section('content')

@php
    $sc = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
    $sl = ['draft'=>'Brouillon','sent'=>'Envoyé','accepted'=>'Accepté','rejected'=>'Refusé','expired'=>'Expiré'];
@endphp

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-white font-mono">{{ $quote->number }}</h1>
    <x-badge :color="$sc[$quote->status] ?? 'gray'">{{ $sl[$quote->status] ?? $quote->status }}</x-badge>
</div>

@if($quote->convertedToInvoice)
    <div class="mb-6 p-4 bg-indigo-900/30 border border-indigo-800 rounded-xl text-sm text-indigo-300">
        Converti en facture :
        <a href="{{ route('admin.invoices.show', $quote->convertedToInvoice) }}"
           class="font-semibold underline hover:text-white">
            {{ $quote->convertedToInvoice->number }}
        </a>
    </div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main content -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Client & dates -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Client</p>
                    <p class="text-white font-semibold">{{ $quote->client->name }}</p>
                    <p class="text-gray-400 text-sm">{{ $quote->client->email }}</p>
                    @if($quote->client->address)
                        <p class="text-gray-500 text-sm mt-1">{{ $quote->client->address }}</p>
                    @endif
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date d'émission</p>
                        <p class="text-white">{{ $quote->issue_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date d'expiration</p>
                        <p class="text-white {{ $quote->isExpired() ? 'text-red-400' : '' }}">
                            {{ $quote->expiry_date?->format('d/m/Y') ?? '—' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Créé par</p>
                        <p class="text-white">{{ $quote->creator?->name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lines -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Lignes du devis</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Description</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Qté</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">PU HT</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">TVA</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Total TTC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($quote->lines as $line)
                    <tr>
                        <td class="px-5 py-3 text-white">{{ $line->description }}</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($line->unit_price_ht, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ $line->vatRate?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-white font-semibold">{{ number_format($line->total_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-800 flex justify-end">
                <div class="w-56 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-400">
                        <span>Total HT</span>
                        <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>TVA</span>
                        <span>{{ number_format($quote->total_vat, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                        <span>Total TTC</span>
                        <span>{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($quote->notes || $quote->conditions)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($quote->notes)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</p>
                <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $quote->notes }}</p>
            </div>
            @endif
            @if($quote->conditions)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Conditions générales</p>
                <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $quote->conditions }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Récapitulatif</p>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Total HT</p>
                    <p class="text-white font-semibold">{{ number_format($quote->total_ht, 2, ',', ' ') }} €</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">TVA</p>
                    <p class="text-white font-semibold">{{ number_format($quote->total_vat, 2, ',', ' ') }} €</p>
                </div>
                <div class="border-t border-gray-800 pt-3">
                    <p class="text-xs text-gray-500">Total TTC</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-3">
            <p class="text-xs font-semibold text-gray-500 uppercase">Actions</p>
            <a href="{{ route('admin.quotes.downloadPdf', $quote) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                Télécharger PDF
            </a>
            @if($quote->isConvertible())
            <form method="POST" action="{{ route('admin.quotes.convertToInvoice', $quote) }}">
                @csrf
                <button type="submit"
                    class="flex items-center justify-center gap-2 w-full bg-green-700 hover:bg-green-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                    Convertir en facture
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection

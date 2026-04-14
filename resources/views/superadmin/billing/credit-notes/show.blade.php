@extends('layouts.app')
@section('title', 'Avoir ' . $creditNote->number)
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.credit-notes.downloadPdf', $creditNote) }}" target="_blank"
           class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Télécharger PDF
        </a>
        @if($creditNote->status === 'draft')
            <form method="POST" action="{{ route('admin.credit-notes.issue', $creditNote) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-orange-600 hover:bg-orange-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Émettre l'avoir
                </button>
            </form>
        @endif
    </div>
@endsection
@section('content')

@php
    $sc = ['draft'=>'gray','issued'=>'orange'];
    $sl = ['draft'=>'Brouillon','issued'=>'Émis'];
@endphp

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-white font-mono">{{ $creditNote->number }}</h1>
    <x-badge :color="$sc[$creditNote->status] ?? 'gray'">{{ $sl[$creditNote->status] ?? $creditNote->status }}</x-badge>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Client</p>
                    <p class="text-white font-semibold">{{ $creditNote->client->name }}</p>
                    <p class="text-gray-400 text-sm">{{ $creditNote->client->email }}</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Facture liée</p>
                        <a href="{{ route('admin.invoices.show', $creditNote->invoice) }}"
                           class="text-indigo-400 hover:text-indigo-300 font-mono">{{ $creditNote->invoice->number }}</a>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date d'émission</p>
                        <p class="text-white">{{ $creditNote->issue_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @if($creditNote->reason)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Motif</p>
                        <p class="text-white text-sm">{{ $creditNote->reason }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lines -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Lignes de l'avoir</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Description</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Qté</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">PU HT</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">TVA</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant TTC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($creditNote->lines as $line)
                    <tr>
                        <td class="px-5 py-3 text-white">{{ $line->description }}</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($line->quantity, 2, ',', ' ') }}</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ number_format($line->unit_price_ht, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-3 text-right text-gray-400">{{ $line->vatRate?->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-orange-400 font-semibold">- {{ number_format($line->total_ttc, 2, ',', ' ') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-800 flex justify-end">
                <div class="w-56 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-400">
                        <span>Total HT</span>
                        <span class="text-orange-300">- {{ number_format($creditNote->total_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>TVA</span>
                        <span class="text-orange-300">- {{ number_format($creditNote->total_vat, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                        <span>Total TTC</span>
                        <span class="text-orange-400">- {{ number_format($creditNote->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        @if($creditNote->notes)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</p>
            <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $creditNote->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Montant de l'avoir</p>
            <p class="text-3xl font-bold text-orange-400">- {{ number_format($creditNote->total_ttc, 2, ',', ' ') }} €</p>
            <p class="text-xs text-gray-500 mt-1">TTC</p>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <a href="{{ route('admin.credit-notes.downloadPdf', $creditNote) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                Télécharger PDF
            </a>
        </div>
    </div>
</div>
@endsection

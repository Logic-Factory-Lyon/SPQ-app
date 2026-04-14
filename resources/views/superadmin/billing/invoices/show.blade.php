@extends('layouts.app')
@section('title', 'Facture ' . $invoice->number)
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.invoices.downloadPdf', $invoice) }}" target="_blank"
           class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Télécharger PDF
        </a>
        @if($invoice->isEditable())
            <a href="{{ route('admin.invoices.edit', $invoice) }}"
               class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Modifier
            </a>
        @endif
        @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Marquer envoyée
                </button>
            </form>
        @endif
        @if(in_array($invoice->status, ['sent', 'overdue']))
            <button x-data="" @click="$dispatch('open-modal', 'mark-paid')"
                class="flex items-center gap-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Enregistrer paiement
            </button>
        @endif
        @if($invoice->isDraft())
            <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}"
                onsubmit="return confirm('Supprimer cette facture ?')">
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
    $sc = ['draft'=>'gray','sent'=>'blue','paid'=>'green','overdue'=>'red','cancelled'=>'gray','refunded'=>'orange'];
    $sl = ['draft'=>'Brouillon','sent'=>'Envoyée','paid'=>'Payée','overdue'=>'En retard','cancelled'=>'Annulée','refunded'=>'Remboursée'];
@endphp

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-white font-mono">{{ $invoice->number }}</h1>
    <x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge>
    @if($invoice->quote)
        <span class="text-xs text-gray-500">
            Depuis devis
            <a href="{{ route('admin.quotes.show', $invoice->quote) }}" class="text-indigo-400 hover:text-indigo-300">
                {{ $invoice->quote->number }}
            </a>
        </span>
    @endif
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <!-- Client & dates -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Client</p>
                    <p class="text-white font-semibold">{{ $invoice->client->name }}</p>
                    <p class="text-gray-400 text-sm">{{ $invoice->client->email }}</p>
                    @if($invoice->client->address)
                        <p class="text-gray-500 text-sm mt-1">{{ $invoice->client->address }}</p>
                    @endif
                </div>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date d'émission</p>
                        <p class="text-white">{{ $invoice->issue_date?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Date d'échéance</p>
                        <p class="{{ $invoice->isOverdue() ? 'text-red-400 font-semibold' : 'text-white' }}">
                            {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                            @if($invoice->isOverdue()) <span class="text-xs">(en retard)</span>@endif
                        </p>
                    </div>
                    @if($invoice->payment_terms)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Conditions</p>
                        <p class="text-white text-sm">{{ $invoice->payment_terms }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Lines -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Lignes de facture</h3>
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
                    @foreach($invoice->lines as $line)
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
                        <span>{{ number_format($invoice->total_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>TVA</span>
                        <span>{{ number_format($invoice->total_vat, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                        <span>Total TTC</span>
                        <span>{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments -->
        @if($invoice->payments->isNotEmpty())
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Paiements reçus</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Méthode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Référence</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @php
                        $methodLabels = ['stripe'=>'Stripe','bank_transfer'=>'Virement','cheque'=>'Chèque','cash'=>'Espèces','other'=>'Autre'];
                    @endphp
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td class="px-5 py-3 text-gray-400">{{ $payment->paid_at?->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 text-gray-400">{{ $methodLabels[$payment->method] ?? $payment->method }}</td>
                        <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $payment->reference ?? '—' }}</td>
                        <td class="px-5 py-3 text-right text-green-400 font-semibold">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-3 text-right">
                            <form method="POST" action="{{ route('admin.payments.destroy', $payment) }}"
                                onsubmit="return confirm('Annuler ce paiement ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400 text-xs">Annuler</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Credit notes -->
        @if($invoice->creditNotes->isNotEmpty())
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Avoirs liés</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @foreach($invoice->creditNotes as $cn)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-white font-mono font-semibold">{{ $cn->number }}</p>
                        <p class="text-xs text-gray-500">{{ $cn->issue_date?->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-orange-400 font-semibold">- {{ number_format($cn->total_ttc, 2, ',', ' ') }} €</span>
                        <a href="{{ route('admin.credit-notes.show', $cn) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">Voir →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($invoice->notes)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</p>
            <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Récapitulatif</p>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Total TTC</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</p>
                </div>
                @if($invoice->remaining_balance > 0)
                <div class="border-t border-gray-800 pt-3">
                    <p class="text-xs text-gray-500">Solde restant dû</p>
                    <p class="text-xl font-bold text-red-400">{{ number_format($invoice->remaining_balance, 2, ',', ' ') }} €</p>
                </div>
                @elseif($invoice->status === 'paid')
                <div class="flex items-center gap-2 text-green-400 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Facture soldée
                </div>
                @endif
            </div>
        </div>

        @if(in_array($invoice->status, ['sent', 'overdue']))
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-3">
            <p class="text-xs font-semibold text-gray-500 uppercase">Enregistrer un paiement</p>
            <a href="{{ route('admin.payments.create', $invoice) }}"
               class="flex items-center justify-center gap-2 w-full bg-green-700 hover:bg-green-600 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                Nouveau paiement
            </a>
            <a href="{{ route('admin.credit-notes.create', $invoice) }}"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                Créer un avoir
            </a>
        </div>
        @endif

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <a href="{{ route('admin.invoices.downloadPdf', $invoice) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                Télécharger PDF
            </a>
        </div>
    </div>
</div>

<!-- Mark paid modal -->
@if(in_array($invoice->status, ['sent', 'overdue']))
<div x-data="{ open: false }" @open-modal.window="open = ($event.detail === 'mark-paid')" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
    <div @click.away="open = false" class="bg-gray-900 rounded-2xl border border-gray-800 p-6 w-full max-w-md">
        <h3 class="text-white font-semibold text-lg mb-4">Enregistrer un paiement</h3>
        <form method="POST" action="{{ route('admin.invoices.markPaid', $invoice) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Montant (€)</label>
                    <input type="number" name="amount" step="0.01" min="0.01"
                        value="{{ $invoice->remaining_balance }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Méthode *</label>
                    <select name="method" required
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="bank_transfer">Virement bancaire</option>
                        <option value="stripe">Stripe</option>
                        <option value="cheque">Chèque</option>
                        <option value="cash">Espèces</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Date de paiement</label>
                    <input type="date" name="paid_at" value="{{ now()->toDateString() }}"
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Référence</label>
                    <input type="text" name="reference" placeholder="N° de virement, etc."
                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                    Enregistrer
                </button>
                <button type="button" @click="open = false"
                    class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold py-2.5 rounded-lg transition-colors">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

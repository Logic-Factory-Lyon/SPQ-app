@extends('layouts.app')
@section('title', 'Paiements')
@section('content')
    <x-page-header title="Paiements reçus" />

    @if($payments->isEmpty())
        <x-empty-state title="Aucun paiement" description="Les paiements enregistrés apparaîtront ici." />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">Client</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Facture</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">Méthode</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">Référence</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @php
                        $methodLabels = ['stripe'=>'Stripe','bank_transfer'=>'Virement','cheque'=>'Chèque','cash'=>'Espèces','other'=>'Autre'];
                    @endphp
                    @foreach($payments as $payment)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4 text-gray-400">{{ $payment->paid_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $payment->client?->name ?? $payment->invoice?->client?->name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if($payment->invoice)
                                <a href="{{ route('admin.invoices.show', $payment->invoice) }}"
                                   class="text-indigo-400 hover:text-indigo-300 font-mono font-semibold">
                                    {{ $payment->invoice->number }}
                                </a>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $methodLabels[$payment->method] ?? $payment->method }}</td>
                        <td class="px-5 py-4 text-gray-500 font-mono text-xs hidden lg:table-cell">{{ $payment->reference ?? '—' }}</td>
                        <td class="px-5 py-4 text-right text-green-400 font-semibold">{{ number_format($payment->amount, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-4 text-right">
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
        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
@endsection

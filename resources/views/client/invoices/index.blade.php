@extends('layouts.app')
@section('title', 'Mes factures')
@section('content')
    <x-page-header title="Mes factures" />

    @if($invoices->isEmpty())
        <x-empty-state title="Aucune facture" description="Vous n'avez pas encore de factures." />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Numéro</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">Échéance</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant TTC</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($invoices as $invoice)
                    @php
                        $sc = ['draft'=>'gray','sent'=>'blue','paid'=>'green','overdue'=>'red','cancelled'=>'gray'];
                        $sl = ['draft'=>'En attente','sent'=>'À payer','paid'=>'Payée','overdue'=>'En retard','cancelled'=>'Annulée'];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors {{ $invoice->isOverdue() ? 'bg-red-950/20' : '' }}">
                        <td class="px-5 py-4 font-mono text-white">{{ $invoice->number }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $invoice->issue_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4 hidden lg:table-cell {{ $invoice->isOverdue() ? 'text-red-400 font-semibold' : 'text-gray-400' }}">
                            {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-white font-semibold">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-4"><x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge></td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center gap-2 justify-end">
                                @if(in_array($invoice->status, ['sent', 'overdue']))
                                    <a href="{{ route('portal.invoices.pay', $invoice) }}"
                                       class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                        Payer
                                    </a>
                                @endif
                                <a href="{{ route('portal.invoices.show', $invoice) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Voir →</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
@endsection

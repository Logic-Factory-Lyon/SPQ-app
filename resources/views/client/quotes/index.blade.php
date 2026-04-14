@extends('layouts.app')
@section('title', 'Mes devis')
@section('content')
    <x-page-header title="Mes devis" />

    @if($quotes->isEmpty())
        <x-empty-state title="Aucun devis" description="Vous n'avez pas encore de devis." />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Numéro</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">Date</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">Expiration</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant TTC</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($quotes as $quote)
                    @php
                        $sc = ['draft'=>'gray','sent'=>'yellow','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                        $sl = ['draft'=>'En attente','sent'=>'À répondre','accepted'=>'Accepté','rejected'=>'Refusé','expired'=>'Expiré'];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4 font-mono text-white">{{ $quote->number }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $quote->issue_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $quote->expiry_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-white font-semibold">{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-4"><x-badge :color="$sc[$quote->status] ?? 'gray'">{{ $sl[$quote->status] ?? $quote->status }}</x-badge></td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('portal.quotes.show', $quote) }}"
                               class="{{ $quote->status === 'sent' ? 'text-yellow-400 hover:text-yellow-300 font-semibold' : 'text-indigo-400 hover:text-indigo-300' }} text-sm">
                                {{ $quote->status === 'sent' ? 'Répondre →' : 'Voir →' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $quotes->links() }}</div>
    @endif
@endsection

@extends('layouts.app')
@section('title', 'Factures')
@section('header-actions')
    <a href="{{ route('admin.invoices.create') }}"
       class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Nouvelle facture
    </a>
@endsection
@section('content')
    <x-page-header title="Factures" />

    @if($totalUnpaid > 0)
    <div class="mb-6 p-4 bg-red-900/30 border border-red-800 rounded-xl flex items-center justify-between">
        <div>
            <p class="text-red-300 font-semibold">Solde impayé total</p>
            <p class="text-2xl font-bold text-red-400">{{ number_format($totalUnpaid, 2, ',', ' ') }} €</p>
        </div>
        <a href="{{ route('admin.invoices.index', ['overdue' => 1]) }}"
           class="text-sm text-red-400 hover:text-red-300">Voir en retard →</a>
    </div>
    @endif

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Tous statuts</option>
            @foreach(['draft'=>'Brouillon','sent'=>'Envoyée','paid'=>'Payée','overdue'=>'En retard','cancelled'=>'Annulée'] as $val => $lab)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        <select name="client_id" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Tous clients</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
            <input type="checkbox" name="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}
                class="w-4 h-4 text-red-600 bg-gray-800 border-gray-600 rounded">
            En retard seulement
        </label>
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg">Filtrer</button>
    </form>

    @if($invoices->isEmpty())
        <x-empty-state title="Aucune facture" description="Créez votre première facture."
            action="Nouvelle facture" actionUrl="{{ route('admin.invoices.create') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Numéro</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">Client</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Montant TTC</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">Statut</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">Échéance</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($invoices as $invoice)
                    @php
                        $sc = ['draft'=>'gray','sent'=>'blue','paid'=>'green','overdue'=>'red','cancelled'=>'gray','refunded'=>'orange'];
                        $sl = ['draft'=>'Brouillon','sent'=>'Envoyée','paid'=>'Payée','overdue'=>'En retard','cancelled'=>'Annulée','refunded'=>'Remboursée'];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors {{ $invoice->isOverdue() ? 'bg-red-950/20' : '' }}">
                        <td class="px-5 py-4 font-mono text-white">{{ $invoice->number }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $invoice->client->name }}</td>
                        <td class="px-5 py-4 text-white font-semibold">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-4"><x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge></td>
                        <td class="px-5 py-4 hidden lg:table-cell {{ $invoice->isOverdue() ? 'text-red-400' : 'text-gray-400' }}">
                            {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">Voir →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    @endif
@endsection

@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('header')
    <h2 class="text-lg font-semibold text-white">Tableau de bord</h2>
@endsection

@section('content')
    <x-page-header title="Vue d'ensemble" subtitle="Bienvenue sur la console d'administration SPQ." />

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <x-stat-card label="Clients actifs" value="{{ $stats['clients'] }}" color="indigo" />
        <x-stat-card label="Projets actifs" value="{{ $stats['projects'] }}" color="blue" />
        <x-stat-card label="Mac Mini en ligne" value="{{ $stats['machines_online'] }}" color="green" />
        <x-stat-card label="Messages en attente" value="{{ $stats['pending_messages'] }}" color="yellow" />
        <x-stat-card label="Encaissé ce mois" value="{{ number_format($stats['mrr'], 0, ',', ' ') }} €" color="green" />
        <x-stat-card label="Impayés" value="{{ number_format($stats['unpaid_total'], 0, ',', ' ') }} €" color="red" />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Recent Invoices -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Dernières factures</h3>
                <a href="{{ route('admin.invoices.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">Voir tout &rarr;</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($recentInvoices as $invoice)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $invoice->number }}</p>
                            <p class="text-xs text-gray-500">{{ $invoice->client->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-white">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</p>
                            @php
                                $statusColors = [
                                    'draft'     => 'gray',
                                    'sent'      => 'blue',
                                    'paid'      => 'green',
                                    'overdue'   => 'red',
                                    'cancelled' => 'gray',
                                ];
                                $statusLabels = [
                                    'draft'     => 'Brouillon',
                                    'sent'      => 'Envoyée',
                                    'paid'      => 'Payée',
                                    'overdue'   => 'En retard',
                                    'cancelled' => 'Annulée',
                                ];
                            @endphp
                            <x-badge :color="$statusColors[$invoice->status] ?? 'gray'">
                                {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                            </x-badge>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">Aucune facture.</p>
                @endforelse
            </div>
        </div>

        <!-- Offline Machines -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">Mac Mini hors ligne</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($offlineMachines as $machine)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $machine->name }}</p>
                            <p class="text-xs text-gray-500">{{ $machine->project->name }} — {{ $machine->project->client->name }}</p>
                        </div>
                        <div class="text-right">
                            <x-badge color="red">Hors ligne</x-badge>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ $machine->last_seen_at ? $machine->last_seen_at->diffForHumans() : 'Jamais connecté' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">
                        <span class="text-green-400">&#10003;</span> Toutes les machines sont en ligne.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

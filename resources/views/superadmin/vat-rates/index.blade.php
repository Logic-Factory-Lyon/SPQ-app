@extends('layouts.app')
@section('title', 'Taux de TVA')
@section('header')
    <h2 class="text-lg font-semibold text-white">Taux de TVA</h2>
@endsection
@section('header-actions')
    <a href="{{ route('admin.vat-rates.create') }}"
       class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Nouveau taux
    </a>
@endsection
@section('content')
    <x-page-header title="Taux de TVA" subtitle="Taux utilisés dans les devis et factures." />
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden max-w-lg">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Libellé</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Taux</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">Défaut</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($rates as $rate)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-5 py-3 text-white">{{ $rate->name }}</td>
                    <td class="px-5 py-3 text-gray-300 font-mono">{{ $rate->rate_percent }}</td>
                    <td class="px-5 py-3">
                        @if($rate->is_default)
                            <x-badge color="green">Défaut</x-badge>
                        @else
                            <form method="POST" action="{{ route('admin.vat-rates.set-default', $rate) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-gray-500 hover:text-indigo-400 transition-colors">
                                    Définir défaut
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.vat-rates.edit', $rate) }}" class="text-indigo-400 text-sm hover:text-indigo-300 font-medium">Modifier</a>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">Aucun taux configuré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@extends('layouts.app')
@section('title', __('app.quote') . ' ' . $quote->number)
@section('header-actions')
    <a href="{{ route('portal.quotes.downloadPdf', $quote) }}" target="_blank"
       class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        {{ __('app.download_pdf') }}
    </a>
@endsection
@section('content')

@php
    $sc = ['draft'=>'gray','sent'=>'yellow','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
    $sl = ['draft'=>__('app.status_pending'),'sent'=>__('app.status_awaiting_response'),'accepted'=>__('app.status_accepted'),'rejected'=>__('app.status_rejected'),'expired'=>__('app.status_expired')];
@endphp

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-white font-mono">{{ $quote->number }}</h1>
    <x-badge :color="$sc[$quote->status] ?? 'gray'">{{ $sl[$quote->status] ?? $quote->status }}</x-badge>
</div>

@if($quote->status === 'sent')
<div class="mb-6 p-5 bg-yellow-900/20 border border-yellow-800 rounded-xl">
    <p class="text-yellow-300 font-semibold mb-1">{{ __('app.quote_waiting_response') }}</p>
    @if($quote->expiry_date)
        <p class="text-yellow-400/70 text-sm mb-4">
            {{ __('app.valid_until') }} {{ $quote->expiry_date->format('d/m/Y') }}
            ({{ $quote->expiry_date->diffForHumans() }})
        </p>
    @endif
    <div class="flex gap-3">
        <form method="POST" action="{{ route('portal.quotes.accept', $quote) }}">
            @csrf
            <button type="submit"
                class="bg-green-600 hover:bg-green-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.accept_quote') }}
            </button>
        </form>
        <form method="POST" action="{{ route('portal.quotes.reject', $quote) }}"
            onsubmit="return confirm('{{ __("app.confirm_rejection") }}')">
            @csrf
            <button type="submit"
                class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ __('app.reject_quote') }}
            </button>
        </form>
    </div>
</div>
@endif

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="grid grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('app.issue_date') }}</p>
                    <p class="text-white">{{ $quote->issue_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('app.expiry_date') }}</p>
                    <p class="text-white">{{ $quote->expiry_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Lines -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">{{ __('app.service_details') }}</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.description') }}</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.qty_short') }}</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.unit_price_ht_short') }}</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.vat') }}</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.total_ttc') }}</th>
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
                        <span>{{ __('app.total_ht') }}</span>
                        <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>{{ __('app.vat') }}</span>
                        <span>{{ number_format($quote->total_vat, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                        <span>{{ __('app.total_ttc') }}</span>
                        <span>{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        @if($quote->notes || $quote->conditions)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($quote->notes)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('app.notes') }}</p>
                <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $quote->notes }}</p>
            </div>
            @endif
            @if($quote->conditions)
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('app.general_conditions') }}</p>
                <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $quote->conditions }}</p>
            </div>
            @endif
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('app.total_amount') }}</p>
            <p class="text-3xl font-bold text-white">{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</p>
            <p class="text-xs text-gray-500 mt-1">{{ __('app.including_tax') }} ({{ number_format($quote->total_vat, 2, ',', ' ') }} € {{ __('app.vat') }})</p>
        </div>

        @if($quote->status === 'sent')
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5 space-y-3">
            <form method="POST" action="{{ route('portal.quotes.accept', $quote) }}">
                @csrf
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                    {{ __('app.accept_quote') }}
                </button>
            </form>
            <form method="POST" action="{{ route('portal.quotes.reject', $quote) }}"
                onsubmit="return confirm('{{ __("app.confirm_rejection_short") }}')">
                @csrf
                <button type="submit"
                    class="w-full bg-gray-800 hover:bg-gray-700 text-gray-400 font-medium py-2 rounded-lg transition-colors text-sm">
                    {{ __('app.reject_quote') }}
                </button>
            </form>
        </div>
        @endif

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <a href="{{ route('portal.quotes.downloadPdf', $quote) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                {{ __('app.download_pdf') }}
            </a>
        </div>
    </div>
</div>
@endsection

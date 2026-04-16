@extends('layouts.app')
@section('title', __('app.invoice') . ' ' . $invoice->number)
@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('portal.invoices.downloadPdf', $invoice) }}" target="_blank"
           class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            {{ __('app.download_pdf') }}
        </a>
        @if(in_array($invoice->status, ['sent', 'overdue']))
            <a href="{{ route('portal.invoices.pay', $invoice) }}"
               class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                {{ __('app.pay_now') }}
            </a>
        @endif
    </div>
@endsection
@section('content')

@php
    $sc = ['draft'=>'gray','sent'=>'blue','paid'=>'green','overdue'=>'red','cancelled'=>'gray'];
    $sl = ['draft'=>__('app.status_pending'),'sent'=>__('app.status_to_pay'),'paid'=>__('app.status_paid'),'overdue'=>__('app.status_overdue'),'cancelled'=>__('app.status_cancelled')];
@endphp

<div class="flex items-center gap-3 mb-6">
    <h1 class="text-2xl font-bold text-white font-mono">{{ $invoice->number }}</h1>
    <x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('app.issue_date') }}</p>
                    <p class="text-white">{{ $invoice->issue_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('app.due_date') }}</p>
                    <p class="{{ $invoice->isOverdue() ? 'text-red-400 font-semibold' : 'text-white' }}">
                        {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                        @if($invoice->isOverdue()) <span class="text-xs font-normal">({{ __('app.late') }})</span>@endif
                    </p>
                </div>
                @if($invoice->payment_terms)
                <div class="col-span-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('app.payment_terms') }}</p>
                    <p class="text-gray-400 text-sm">{{ $invoice->payment_terms }}</p>
                </div>
                @endif
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
                        <span>{{ __('app.total_ht') }}</span>
                        <span>{{ number_format($invoice->total_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>{{ __('app.vat') }}</span>
                        <span>{{ number_format($invoice->total_vat, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                        <span>{{ __('app.total_ttc') }}</span>
                        <span>{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payments history -->
        @if($invoice->payments->isNotEmpty())
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">{{ __('app.payment_history') }}</h3>
            </div>
            <div class="divide-y divide-gray-800">
                @php $methodLabels = ['stripe'=>__('app.method_stripe'),'bank_transfer'=>__('app.method_bank_transfer'),'cheque'=>__('app.method_cheque'),'cash'=>__('app.method_cash'),'other'=>__('app.method_other')]; @endphp
                @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-white text-sm font-medium">{{ $methodLabels[$payment->method] ?? $payment->method }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->paid_at?->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-green-400 font-semibold">{{ number_format($payment->amount, 2, ',', ' ') }} €</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($invoice->notes)
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('app.notes') }}</p>
            <p class="text-gray-300 text-sm whitespace-pre-wrap">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('app.amount_due') }}</p>
            @if($invoice->status === 'paid')
                <p class="text-2xl font-bold text-green-400">{{ __('app.settled') }}</p>
            @else
                <p class="text-3xl font-bold text-white">{{ number_format($invoice->remaining_balance ?? $invoice->total_ttc, 2, ',', ' ') }} €</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('app.including_tax') }}</p>
            @endif
        </div>

        @if(in_array($invoice->status, ['sent', 'overdue']))
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <a href="{{ route('portal.invoices.pay', $invoice) }}"
               class="flex items-center justify-center gap-2 w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-4 py-3 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                {{ __('app.pay_by_card') }}
            </a>
        </div>
        @endif

        <div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
            <a href="{{ route('portal.invoices.downloadPdf', $invoice) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                {{ __('app.download_pdf') }}
            </a>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', __('app.payments'))
@section('content')
    <x-page-header title="{{ __('app.payments_received') }}" />

    @if($payments->isEmpty())
        <x-empty-state title="{{ __('app.no_payment') }}" description="{{ __('app.no_payment_desc') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.date') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">{{ __('app.clients') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.invoice') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">{{ __('app.method') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">{{ __('app.reference') }}</th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.amount') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @php
                        $methodLabels = ['stripe'=>__('app.method_stripe'),'bank_transfer'=>__('app.method_bank_transfer'),'cheque'=>__('app.method_cheque'),'cash'=>__('app.method_cash'),'other'=>__('app.method_other')];
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
                                onsubmit="return confirm('{{ __("app.cancel_payment_confirm") }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400 text-xs">{{ __('app.cancel_payment') }}</button>
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

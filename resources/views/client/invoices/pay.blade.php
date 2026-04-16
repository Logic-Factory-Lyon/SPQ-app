@extends('layouts.app')
@section('title', __('app.pay_invoice', ['number' => $invoice->number]))
@section('content')
<x-page-header title="{{ __('app.secure_payment') }}" subtitle="{{ __('app.invoice') }} {{ $invoice->number }}" />

<div class="max-w-lg mx-auto">
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('app.invoice') }}</p>
                <p class="text-white font-mono font-bold text-lg">{{ $invoice->number }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">{{ __('app.amount_to_pay') }}</p>
                <p class="text-2xl font-bold text-white">{{ number_format($invoice->remaining_balance ?? $invoice->total_ttc, 2, ',', ' ') }} €</p>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-4">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                {{ __('app.secure_payment_note') }}
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">{{ __('app.card_info') }}</h3>

        <form id="payment-form" class="space-y-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.card_number') }}</label>
                <div id="card-element"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-white focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-transparent">
                </div>
                <div id="card-errors" class="text-red-400 text-xs mt-2" role="alert"></div>
            </div>

            <button id="submit-btn" type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed
                       text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                <span id="btn-text">{{ __('app.pay') }} {{ number_format($invoice->remaining_balance ?? $invoice->total_ttc, 2, ',', ' ') }} €</span>
                <svg id="btn-spinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </button>
        </form>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('portal.invoices.show', $invoice) }}" class="text-sm text-gray-500 hover:text-gray-400">
            {{ __('app.back_to_invoice') }}
        </a>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ $stripeKey }}');
const elements = stripe.elements();

const cardElement = elements.create('card', {
    style: {
        base: {
            color: '#fff',
            fontFamily: 'ui-sans-serif, system-ui, sans-serif',
            fontSize: '15px',
            '::placeholder': { color: '#6b7280' },
        },
        invalid: { color: '#f87171' },
    }
});
cardElement.mount('#card-element');

cardElement.on('change', ({ error }) => {
    const errEl = document.getElementById('card-errors');
    errEl.textContent = error ? error.message : '';
});

document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = document.getElementById('submit-btn');
    const btnText = document.getElementById('btn-text');
    const spinner = document.getElementById('btn-spinner');

    btn.disabled = true;
    btnText.textContent = '{{ __("app.processing") }}';
    spinner.classList.remove('hidden');

    const { error, paymentIntent } = await stripe.confirmCardPayment('{{ $clientSecret }}', {
        payment_method: { card: cardElement }
    });

    if (error) {
        document.getElementById('card-errors').textContent = error.message;
        btn.disabled = false;
        btnText.textContent = '{{ __("app.pay") }} {{ number_format($invoice->remaining_balance ?? $invoice->total_ttc, 2, ',', ' ') }} €';
        spinner.classList.add('hidden');
    } else if (paymentIntent.status === 'succeeded') {
        window.location.href = '{{ route('portal.invoices.paySuccess', $invoice) }}?payment_intent=' + paymentIntent.id;
    }
});
</script>
@endpush
@endsection

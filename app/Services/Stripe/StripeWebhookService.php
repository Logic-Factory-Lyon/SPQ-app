<?php
namespace App\Services\Stripe;

use App\Models\Invoice;
use App\Services\Billing\PaymentService;

class StripeWebhookService
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function handlePaymentIntentSucceeded(array $paymentIntent): void
    {
        $piId  = $paymentIntent['id'];
        $amount = $paymentIntent['amount_received'] / 100; // cents → euros

        $invoice = Invoice::where('stripe_payment_intent_id', $piId)->first();
        if (! $invoice) return;

        $this->paymentService->recordStripe($invoice, $piId, $amount);
    }
}

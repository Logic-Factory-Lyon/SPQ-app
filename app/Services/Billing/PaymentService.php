<?php
namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function recordManual(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data): Payment {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'client_id'  => $invoice->client_id,
                'amount'     => $data['amount'],
                'method'     => $data['method'],
                'reference'  => $data['reference'] ?? null,
                'notes'      => $data['notes'] ?? null,
                'paid_at'    => $data['paid_at'] ?? now(),
            ]);

            $this->checkIfFullyPaid($invoice);
            return $payment;
        });
    }

    public function recordStripe(Invoice $invoice, string $paymentIntentId, float $amount): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentIntentId, $amount): Payment {
            // Avoid duplicate recording
            if (Payment::where('stripe_payment_intent_id', $paymentIntentId)->exists()) {
                return Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
            }

            $payment = Payment::create([
                'invoice_id'               => $invoice->id,
                'client_id'                => $invoice->client_id,
                'amount'                   => $amount,
                'method'                   => 'stripe',
                'stripe_payment_intent_id' => $paymentIntentId,
                'paid_at'                  => now(),
            ]);

            $this->checkIfFullyPaid($invoice);
            return $payment;
        });
    }

    public function checkIfFullyPaid(Invoice $invoice): void
    {
        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total_ttc) {
            $invoice->markPaid();
        }
    }

    public function reverse(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $invoice = $payment->invoice;
            $payment->delete();

            // Revert invoice status if it was paid
            if ($invoice->status === 'paid') {
                $invoice->update(['status' => 'sent', 'paid_at' => null]);
            }
        });
    }
}

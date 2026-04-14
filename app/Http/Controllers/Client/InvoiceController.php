<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\Billing\InvoicePdfService;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoicePdfService $pdfService) {}

    public function index(): View
    {
        $client = auth()->user()->client;
        $invoices = $client->invoices()->latest()->paginate(20);
        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice): View
    {
        abort_if($invoice->client_id !== auth()->user()->client_id, 403);
        $invoice->load(['lines.vatRate', 'payments']);
        return view('client.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        abort_if($invoice->client_id !== auth()->user()->client_id, 403);
        return $this->pdfService->streamResponse($invoice);
    }

    public function payWithStripe(Request $request, Invoice $invoice): View|RedirectResponse
    {
        abort_if($invoice->client_id !== auth()->user()->client_id, 403);
        abort_if($invoice->isPaid(), 400, 'Cette facture est déjà payée.');

        Stripe::setApiKey(config('cashier.secret'));

        $amountCents = (int) round($invoice->remaining_balance * 100);
        if ($amountCents <= 0) {
            return redirect()->route('portal.invoices.show', $invoice)->with('success', 'Facture déjà soldée.');
        }

        $client = auth()->user()->client;

        // Ensure Stripe customer exists
        if (! $client->stripe_customer_id) {
            $customer = \Stripe\Customer::create([
                'name'  => $client->name,
                'email' => $client->email,
            ]);
            $client->update(['stripe_customer_id' => $customer->id]);
        }

        $intent = PaymentIntent::create([
            'amount'   => $amountCents,
            'currency' => 'eur',
            'customer' => $client->stripe_customer_id,
            'metadata' => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->number],
        ]);

        $invoice->update(['stripe_payment_intent_id' => $intent->id]);

        return view('client.invoices.pay', [
            'invoice'      => $invoice,
            'clientSecret' => $intent->client_secret,
            'stripeKey'    => config('cashier.key'),
        ]);
    }

    public function paySuccess(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->client_id !== auth()->user()->client_id, 403);
        return redirect()->route('portal.invoices.show', $invoice)
            ->with('success', 'Paiement reçu, merci !');
    }
}

<?php
namespace App\Http\Controllers;

use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeWebhookService $webhookService
    ) {}

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('cashier.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalide.', ['error' => $e->getMessage()]);
            return response('Signature invalide.', 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->webhookService->handlePaymentIntentSucceeded(
                $event->data->object->toArray()
            ),
            default => null, // ignore other events
        };

        return response('OK', 200);
    }
}

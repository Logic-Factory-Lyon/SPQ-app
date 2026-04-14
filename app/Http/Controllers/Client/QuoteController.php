<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\Billing\InvoicePdfService;
use App\Services\Billing\QuoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService,
        private readonly InvoicePdfService $pdfService,
    ) {}

    public function index(): View
    {
        $client = auth()->user()->client;
        $quotes = $client->quotes()->latest()->paginate(20);
        return view('client.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote): View
    {
        abort_if($quote->client_id !== auth()->user()->client_id, 403);
        $quote->load(['lines.vatRate']);
        return view('client.quotes.show', compact('quote'));
    }

    public function accept(Quote $quote): RedirectResponse
    {
        abort_if($quote->client_id !== auth()->user()->client_id, 403);
        abort_if($quote->status !== 'sent', 400, 'Ce devis ne peut plus être accepté.');
        $this->quoteService->accept($quote);
        return back()->with('success', 'Devis accepté. Nous vous enverrons bientôt la facture correspondante.');
    }

    public function reject(Quote $quote): RedirectResponse
    {
        abort_if($quote->client_id !== auth()->user()->client_id, 403);
        $this->quoteService->reject($quote);
        return back()->with('success', 'Devis refusé.');
    }

    public function downloadPdf(Quote $quote)
    {
        abort_if($quote->client_id !== auth()->user()->client_id, 403);
        return $this->pdfService->streamResponse($quote);
    }
}

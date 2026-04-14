<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Quote;
use App\Models\Service;
use App\Models\VatRate;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\InvoicePdfService;
use App\Services\Billing\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService,
        private readonly InvoiceService $invoiceService,
        private readonly InvoicePdfService $pdfService,
    ) {}

    public function index(Request $request): View
    {
        $query = Quote::with('client')->latest();
        if ($s = $request->get('status')) $query->where('status', $s);
        if ($c = $request->get('client_id')) $query->where('client_id', $c);
        $quotes = $query->paginate(20)->withQueryString();
        $clients = Client::active()->orderBy('name')->get();
        return view('superadmin.billing.quotes.index', compact('quotes', 'clients'));
    }

    public function create(Request $request): View
    {
        $clients = Client::active()->orderBy('name')->get();
        $services = Service::active()->with('vatRate')->orderBy('name')->get();
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        $selectedClient = $request->get('client_id') ? Client::find($request->get('client_id')) : null;
        return view('superadmin.billing.quotes.create', compact('clients', 'services', 'vatRates', 'selectedClient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'issue_date'  => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'notes'       => 'nullable|string',
            'conditions'  => 'nullable|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.description'      => 'required|string',
            'lines.*.quantity'         => 'required|numeric|min:0.01',
            'lines.*.unit_price_ht'    => 'required|numeric|min:0',
            'lines.*.vat_rate_id'      => 'required|exists:vat_rates,id',
            'lines.*.service_id'       => 'nullable|exists:services,id',
        ]);

        $quote = $this->quoteService->create($data, auth()->user());
        return redirect()->route('admin.quotes.show', $quote)
            ->with('success', "Devis {$quote->number} créé.");
    }

    public function show(Quote $quote): View
    {
        $quote->load(['client', 'lines.vatRate', 'creator', 'convertedToInvoice']);
        return view('superadmin.billing.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        abort_if(! $quote->isEditable(), 403, 'Ce devis ne peut plus être modifié.');
        $clients = Client::active()->orderBy('name')->get();
        $services = Service::active()->with('vatRate')->orderBy('name')->get();
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        return view('superadmin.billing.quotes.edit', compact('quote', 'clients', 'services', 'vatRates'));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        abort_if(! $quote->isEditable(), 403);
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'issue_date'  => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'notes'       => 'nullable|string',
            'conditions'  => 'nullable|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.description'      => 'required|string',
            'lines.*.quantity'         => 'required|numeric|min:0.01',
            'lines.*.unit_price_ht'    => 'required|numeric|min:0',
            'lines.*.vat_rate_id'      => 'required|exists:vat_rates,id',
            'lines.*.service_id'       => 'nullable|exists:services,id',
        ]);
        $this->quoteService->update($quote, $data);
        return redirect()->route('admin.quotes.show', $quote)->with('success', 'Devis mis à jour.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        abort_if(! $quote->isDraft(), 403, 'Seuls les devis en brouillon peuvent être supprimés.');
        $num = $quote->number;
        $quote->delete();
        return redirect()->route('admin.quotes.index')->with('success', "Devis {$num} supprimé.");
    }

    public function send(Quote $quote): RedirectResponse
    {
        $this->quoteService->sendToClient($quote);
        return back()->with('success', 'Devis marqué comme envoyé.');
    }

    public function convertToInvoice(Quote $quote): RedirectResponse
    {
        abort_if(! $quote->isConvertible(), 403, 'Ce devis ne peut pas être converti.');
        $invoice = $this->invoiceService->createFromQuote($quote, auth()->user());
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Facture {$invoice->number} créée depuis le devis {$quote->number}.");
    }

    public function downloadPdf(Quote $quote)
    {
        return $this->pdfService->streamResponse($quote);
    }
}

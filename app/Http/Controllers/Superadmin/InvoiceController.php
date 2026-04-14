<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\VatRate;
use App\Services\Billing\InvoicePdfService;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly InvoicePdfService $pdfService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request): View
    {
        $query = Invoice::with('client')->latest();
        if ($s = $request->get('status')) $query->where('status', $s);
        if ($c = $request->get('client_id')) $query->where('client_id', $c);
        if ($request->get('overdue')) $query->overdue();
        $invoices = $query->paginate(20)->withQueryString();
        $clients = Client::active()->orderBy('name')->get();
        $totalUnpaid = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_ttc');
        return view('superadmin.billing.invoices.index', compact('invoices', 'clients', 'totalUnpaid'));
    }

    public function create(Request $request): View
    {
        $clients = Client::active()->orderBy('name')->get();
        $services = Service::active()->with('vatRate')->orderBy('name')->get();
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        $selectedClient = $request->get('client_id') ? Client::find($request->get('client_id')) : null;
        return view('superadmin.billing.invoices.create', compact('clients', 'services', 'vatRates', 'selectedClient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'issue_date'    => 'nullable|date',
            'due_date'      => 'nullable|date',
            'notes'         => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.description'      => 'required|string',
            'lines.*.quantity'         => 'required|numeric|min:0.01',
            'lines.*.unit_price_ht'    => 'required|numeric|min:0',
            'lines.*.vat_rate_id'      => 'required|exists:vat_rates,id',
            'lines.*.service_id'       => 'nullable|exists:services,id',
        ]);
        $invoice = $this->invoiceService->create($data, auth()->user());
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', "Facture {$invoice->number} créée.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['client', 'lines.vatRate', 'creator', 'payments', 'creditNotes', 'quote']);
        return view('superadmin.billing.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        abort_if(! $invoice->isEditable(), 403);
        $clients = Client::active()->orderBy('name')->get();
        $services = Service::active()->with('vatRate')->orderBy('name')->get();
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        return view('superadmin.billing.invoices.edit', compact('invoice', 'clients', 'services', 'vatRates'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_if(! $invoice->isEditable(), 403);
        $data = $request->validate([
            'client_id'     => 'required|exists:clients,id',
            'issue_date'    => 'nullable|date',
            'due_date'      => 'nullable|date',
            'notes'         => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.description'      => 'required|string',
            'lines.*.quantity'         => 'required|numeric|min:0.01',
            'lines.*.unit_price_ht'    => 'required|numeric|min:0',
            'lines.*.vat_rate_id'      => 'required|exists:vat_rates,id',
            'lines.*.service_id'       => 'nullable|exists:services,id',
        ]);
        $this->invoiceService->update($invoice, $data);
        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Facture mise à jour.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        abort_if(! $invoice->isDraft(), 403);
        $num = $invoice->number;
        $invoice->delete();
        return redirect()->route('admin.invoices.index')->with('success', "Facture {$num} supprimée.");
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->sendToClient($invoice);
        return back()->with('success', 'Facture marquée comme envoyée.');
    }

    public function markPaid(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'paid_at'   => 'nullable|date',
            'method'    => 'required|in:stripe,bank_transfer,cheque,cash,other',
            'amount'    => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:100',
        ]);

        $this->paymentService->recordManual($invoice, [
            'amount'    => $data['amount'] ?? $invoice->total_ttc,
            'method'    => $data['method'],
            'paid_at'   => $data['paid_at'] ?? now(),
            'reference' => $data['reference'] ?? null,
        ]);

        return back()->with('success', 'Paiement enregistré.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        return $this->pdfService->streamResponse($invoice);
    }
}

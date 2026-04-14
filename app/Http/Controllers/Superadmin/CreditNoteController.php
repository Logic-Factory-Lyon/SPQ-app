<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\VatRate;
use App\Services\Billing\CreditNoteService;
use App\Services\Billing\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNoteService,
        private readonly InvoicePdfService $pdfService,
    ) {}

    public function create(Invoice $invoice): View
    {
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        return view('superadmin.billing.credit-notes.create', compact('invoice', 'vatRates'));
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'issue_date' => 'nullable|date',
            'reason'     => 'nullable|string',
            'notes'      => 'nullable|string',
            'lines'                    => 'required|array|min:1',
            'lines.*.description'      => 'required|string',
            'lines.*.quantity'         => 'required|numeric|min:0.01',
            'lines.*.unit_price_ht'    => 'required|numeric|min:0',
            'lines.*.vat_rate_id'      => 'required|exists:vat_rates,id',
        ]);
        $creditNote = $this->creditNoteService->create($invoice, $data, auth()->user());
        return redirect()->route('admin.credit-notes.show', $creditNote)
            ->with('success', "Avoir {$creditNote->number} créé.");
    }

    public function show(CreditNote $creditNote): View
    {
        $creditNote->load(['client', 'invoice', 'lines.vatRate', 'creator']);
        return view('superadmin.billing.credit-notes.show', compact('creditNote'));
    }

    public function issue(CreditNote $creditNote): RedirectResponse
    {
        $this->creditNoteService->issue($creditNote);
        return back()->with('success', "Avoir {$creditNote->number} émis.");
    }

    public function downloadPdf(CreditNote $creditNote)
    {
        return $this->pdfService->streamResponse($creditNote);
    }
}

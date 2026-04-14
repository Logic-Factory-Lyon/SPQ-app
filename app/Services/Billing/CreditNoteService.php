<?php
namespace App\Services\Billing;

use App\Models\CreditNote;
use App\Models\CreditNoteLine;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function __construct(
        private readonly InvoicePdfService $pdfService
    ) {}

    public function create(Invoice $invoice, array $data, User $creator): CreditNote
    {
        return DB::transaction(function () use ($invoice, $data, $creator): CreditNote {
            $number = InvoiceSequenceService::nextNumber('credit_note');

            $creditNote = CreditNote::create([
                'invoice_id' => $invoice->id,
                'client_id'  => $invoice->client_id,
                'created_by' => $creator->id,
                'number'     => $number,
                'status'     => 'draft',
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'reason'     => $data['reason'] ?? null,
                'notes'      => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $sort => $lineData) {
                CreditNoteLine::create([
                    'credit_note_id' => $creditNote->id,
                    'vat_rate_id'    => $lineData['vat_rate_id'],
                    'description'    => $lineData['description'],
                    'quantity'       => $lineData['quantity'],
                    'unit_price_ht'  => $lineData['unit_price_ht'],
                    'sort_order'     => $sort,
                ]);
            }

            $creditNote->recalculateTotals();
            return $creditNote->fresh();
        });
    }

    public function issue(CreditNote $creditNote): void
    {
        if (! $creditNote->pdf_path) {
            $this->pdfService->generate('credit_note', $creditNote);
        }
        $creditNote->update([
            'status'     => 'issued',
            'issue_date' => $creditNote->issue_date ?? now()->toDateString(),
        ]);
    }
}

<?php
namespace App\Services\Billing;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Quote;
use App\Models\User;
use App\Models\VatRate;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly InvoicePdfService $pdfService
    ) {}

    public function create(array $data, User $creator): Invoice
    {
        return DB::transaction(function () use ($data, $creator): Invoice {
            $number = InvoiceSequenceService::nextNumber('invoice');

            $invoice = Invoice::create([
                'client_id'    => $data['client_id'],
                'quote_id'     => $data['quote_id'] ?? null,
                'created_by'   => $creator->id,
                'number'       => $number,
                'status'       => 'draft',
                'issue_date'   => $data['issue_date'] ?? now()->toDateString(),
                'due_date'     => $data['due_date'] ?? now()->addDays(30)->toDateString(),
                'notes'        => $data['notes'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? 'Paiement à 30 jours.',
            ]);

            foreach ($data['lines'] as $sort => $lineData) {
                InvoiceLine::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $lineData['service_id'] ?? null,
                    'vat_rate_id'   => $lineData['vat_rate_id'],
                    'description'   => $lineData['description'],
                    'quantity'      => $lineData['quantity'],
                    'unit_price_ht' => $lineData['unit_price_ht'],
                    'sort_order'    => $sort,
                ]);
            }

            $invoice->recalculateTotals();
            return $invoice->fresh();
        });
    }

    public function createFromQuote(Quote $quote, User $creator): Invoice
    {
        return DB::transaction(function () use ($quote, $creator): Invoice {
            $invoice = $this->create([
                'client_id'    => $quote->client_id,
                'quote_id'     => $quote->id,
                'issue_date'   => now()->toDateString(),
                'due_date'     => now()->addDays(30)->toDateString(),
                'notes'        => $quote->notes,
                'payment_terms' => 'Paiement à 30 jours.',
                'lines'        => $quote->lines->map(fn($l) => [
                    'service_id'    => $l->service_id,
                    'vat_rate_id'   => $l->vat_rate_id,
                    'description'   => $l->description,
                    'quantity'      => $l->quantity,
                    'unit_price_ht' => $l->unit_price_ht,
                ])->toArray(),
            ], $creator);

            $quote->update(['converted_to_invoice_id' => $invoice->id]);
            return $invoice;
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data): Invoice {
            $invoice->update([
                'client_id'    => $data['client_id'],
                'issue_date'   => $data['issue_date'],
                'due_date'     => $data['due_date'],
                'notes'        => $data['notes'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
            ]);

            // Replace lines
            $invoice->lines()->delete();
            foreach ($data['lines'] as $sort => $lineData) {
                InvoiceLine::create([
                    'invoice_id'    => $invoice->id,
                    'service_id'    => $lineData['service_id'] ?? null,
                    'vat_rate_id'   => $lineData['vat_rate_id'],
                    'description'   => $lineData['description'],
                    'quantity'      => $lineData['quantity'],
                    'unit_price_ht' => $lineData['unit_price_ht'],
                    'sort_order'    => $sort,
                ]);
            }

            $invoice->recalculateTotals();
            return $invoice->fresh();
        });
    }

    public function sendToClient(Invoice $invoice): void
    {
        if (! $invoice->pdf_path) {
            $this->pdfService->generate('invoice', $invoice);
        }
        $invoice->update([
            'status'     => 'sent',
            'issue_date' => $invoice->issue_date ?? now()->toDateString(),
        ]);
        // TODO: dispatch SendInvoiceMail::class
    }

    public function markOverdue(): int
    {
        return Invoice::where('status', 'sent')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }
}

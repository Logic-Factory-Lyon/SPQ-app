<?php
namespace App\Services\Billing;

use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        private readonly InvoicePdfService $pdfService
    ) {}

    public function create(array $data, User $creator): Quote
    {
        return DB::transaction(function () use ($data, $creator): Quote {
            $number = InvoiceSequenceService::nextNumber('quote');

            $quote = Quote::create([
                'client_id'   => $data['client_id'],
                'created_by'  => $creator->id,
                'number'      => $number,
                'status'      => 'draft',
                'issue_date'  => $data['issue_date'] ?? now()->toDateString(),
                'expiry_date' => $data['expiry_date'] ?? now()->addDays(30)->toDateString(),
                'notes'       => $data['notes'] ?? null,
                'conditions'  => $data['conditions'] ?? null,
            ]);

            foreach ($data['lines'] as $sort => $lineData) {
                QuoteLine::create([
                    'quote_id'      => $quote->id,
                    'service_id'    => $lineData['service_id'] ?? null,
                    'vat_rate_id'   => $lineData['vat_rate_id'],
                    'description'   => $lineData['description'],
                    'quantity'      => $lineData['quantity'],
                    'unit_price_ht' => $lineData['unit_price_ht'],
                    'sort_order'    => $sort,
                ]);
            }

            $quote->recalculateTotals();
            return $quote->fresh();
        });
    }

    public function update(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data): Quote {
            $quote->update([
                'client_id'   => $data['client_id'],
                'issue_date'  => $data['issue_date'],
                'expiry_date' => $data['expiry_date'],
                'notes'       => $data['notes'] ?? null,
                'conditions'  => $data['conditions'] ?? null,
            ]);

            $quote->lines()->delete();
            foreach ($data['lines'] as $sort => $lineData) {
                QuoteLine::create([
                    'quote_id'      => $quote->id,
                    'service_id'    => $lineData['service_id'] ?? null,
                    'vat_rate_id'   => $lineData['vat_rate_id'],
                    'description'   => $lineData['description'],
                    'quantity'      => $lineData['quantity'],
                    'unit_price_ht' => $lineData['unit_price_ht'],
                    'sort_order'    => $sort,
                ]);
            }

            $quote->recalculateTotals();
            return $quote->fresh();
        });
    }

    public function sendToClient(Quote $quote): void
    {
        if (! $quote->pdf_path) {
            $this->pdfService->generate('quote', $quote);
        }
        $quote->update([
            'status'     => 'sent',
            'issue_date' => $quote->issue_date ?? now()->toDateString(),
        ]);
        // TODO: dispatch SendQuoteMail::class
    }

    public function accept(Quote $quote): void
    {
        $quote->update(['status' => 'accepted']);
    }

    public function reject(Quote $quote): void
    {
        $quote->update(['status' => 'rejected']);
    }
}

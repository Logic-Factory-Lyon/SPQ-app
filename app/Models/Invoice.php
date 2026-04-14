<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'quote_id', 'created_by', 'number', 'status', 'issue_date', 'due_date',
        'paid_at', 'subtotal_ht', 'total_vat', 'total_ttc', 'notes', 'payment_terms',
        'pdf_path', 'stripe_invoice_id', 'stripe_payment_intent_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function quote(): BelongsTo { return $this->belongsTo(Quote::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(InvoiceLine::class)->orderBy('sort_order'); }
    public function creditNotes(): HasMany { return $this->hasMany(CreditNote::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isPaid(): bool { return $this->status === 'paid'; }
    public function isEditable(): bool { return $this->status === 'draft'; }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->lt(now())
            && ! in_array($this->status, ['paid', 'cancelled', 'refunded']);
    }

    public function markPaid(?Carbon $paidAt = null): void
    {
        $this->update(['status' => 'paid', 'paid_at' => $paidAt ?? now()]);
    }

    public function recalculateTotals(): void
    {
        $lines = $this->lines()->get();
        $subtotal = $lines->sum('line_total_ht');
        $totalVat = $lines->sum('line_total_vat');

        $this->update([
            'subtotal_ht' => $subtotal,
            'total_vat' => $totalVat,
            'total_ttc' => $subtotal + $totalVat,
        ]);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return round((float)$this->total_ttc - $this->total_paid, 2);
    }

    public function scopeForClient($query, int $clientId) { return $query->where('client_id', $clientId); }
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereNotIn('status', ['paid', 'cancelled', 'refunded']);
    }
}

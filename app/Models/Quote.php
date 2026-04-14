<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'client_id', 'created_by', 'number', 'status', 'issue_date', 'expiry_date',
        'subtotal_ht', 'total_vat', 'total_ttc', 'notes', 'conditions', 'pdf_path', 'converted_to_invoice_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'subtotal_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function convertedToInvoice(): BelongsTo { return $this->belongsTo(Invoice::class, 'converted_to_invoice_id'); }
    public function lines(): HasMany { return $this->hasMany(QuoteLine::class)->orderBy('sort_order'); }

    public function isConvertible(): bool
    {
        return $this->status === 'accepted' && $this->converted_to_invoice_id === null;
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isEditable(): bool { return in_array($this->status, ['draft']); }
    public function isExpired(): bool { return $this->expiry_date && $this->expiry_date->isPast() && $this->status === 'sent'; }

    public function recalculateTotals(): void
    {
        $this->lines()->get(); // refresh
        $subtotal = $this->lines->sum('line_total_ht');
        $totalVat = $this->lines->sum('line_total_vat');

        $this->update([
            'subtotal_ht' => $subtotal,
            'total_vat' => $totalVat,
            'total_ttc' => $subtotal + $totalVat,
        ]);
    }

    public function scopeForClient($query, int $clientId) { return $query->where('client_id', $clientId); }
}

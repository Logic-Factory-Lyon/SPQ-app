<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditNote extends Model
{
    protected $fillable = [
        'invoice_id', 'client_id', 'created_by', 'number', 'status', 'issue_date',
        'subtotal_ht', 'total_vat', 'total_ttc', 'reason', 'notes', 'pdf_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'subtotal_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(CreditNoteLine::class)->orderBy('sort_order'); }

    public function isDraft(): bool { return $this->status === 'draft'; }

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
}

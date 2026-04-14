<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    protected $fillable = [
        'quote_id', 'service_id', 'vat_rate_id', 'description', 'quantity',
        'unit_price_ht', 'line_total_ht', 'line_total_vat', 'line_total_ttc', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price_ht' => 'decimal:2',
        'line_total_ht' => 'decimal:2',
        'line_total_vat' => 'decimal:2',
        'line_total_ttc' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (QuoteLine $line) {
            $line->calculateTotals();
        });
    }

    public function quote(): BelongsTo { return $this->belongsTo(Quote::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function vatRate(): BelongsTo { return $this->belongsTo(VatRate::class); }

    public function calculateTotals(): void
    {
        $ht = round((float)$this->quantity * (float)$this->unit_price_ht, 2);
        $vatRate = $this->vatRate ?? VatRate::find($this->vat_rate_id);
        $vat = $vatRate ? round($ht * $vatRate->rate, 2) : 0;

        $this->line_total_ht = $ht;
        $this->line_total_vat = $vat;
        $this->line_total_ttc = $ht + $vat;
    }
}

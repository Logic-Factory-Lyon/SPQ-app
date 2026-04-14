<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'unit_price_ht', 'vat_rate_id', 'billing_type', 'active'];

    protected $casts = [
        'unit_price_ht' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function vatRate(): BelongsTo { return $this->belongsTo(VatRate::class); }

    public function scopeActive($query) { return $query->where('active', true); }

    public function getPriceTtcAttribute(): float
    {
        return round($this->unit_price_ht * (1 + ($this->vatRate->rate ?? 0)), 2);
    }
}

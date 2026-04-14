<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VatRate extends Model
{
    protected $fillable = ['name', 'rate', 'is_default', 'active'];

    protected $casts = [
        'rate' => 'float',
        'is_default' => 'boolean',
        'active' => 'boolean',
    ];

    public function quoteLines(): HasMany { return $this->hasMany(QuoteLine::class); }
    public function invoiceLines(): HasMany { return $this->hasMany(InvoiceLine::class); }
    public function creditNoteLines(): HasMany { return $this->hasMany(CreditNoteLine::class); }
    public function services(): HasMany { return $this->hasMany(Service::class); }

    public function scopeActive($query) { return $query->where('active', true); }
    public function scopeDefault($query) { return $query->where('is_default', true); }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->where('active', true)->first();
    }

    public function getRatePercentAttribute(): string
    {
        return number_format($this->rate * 100, 1) . '%';
    }
}

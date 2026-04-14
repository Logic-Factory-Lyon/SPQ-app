<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Client extends Model
{
    use Billable;

    protected $fillable = [
        'name', 'contact_first_name', 'contact_last_name', 'email', 'phone',
        'address_line1', 'address_line2', 'city', 'zip_code', 'country_code',
        'vat_number', 'stripe_customer_id', 'notes', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getFullContactNameAttribute(): string
    {
        return trim("{$this->contact_first_name} {$this->contact_last_name}");
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            trim("{$this->zip_code} {$this->city}"),
            $this->country_code,
        ]);
        return implode(', ', $parts);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total_ttc');
    }
}

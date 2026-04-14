<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MacMachine extends Model
{
    protected $fillable = ['project_id', 'name', 'token', 'status', 'last_seen_at', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
        'last_seen_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (MacMachine $machine) {
            if (empty($machine->token)) {
                $machine->token = bin2hex(random_bytes(32));
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }

    public function markSeen(): void
    {
        $this->update(['status' => 'online', 'last_seen_at' => now()]);
    }

    public function markOffline(): void
    {
        $this->update(['status' => 'offline']);
    }

    public function isStale(int $minutes = 5): bool
    {
        if (! $this->last_seen_at) return true;
        return $this->last_seen_at->lt(now()->subMinutes($minutes));
    }

    public function regenerateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['token' => $token]);
        return $token;
    }
}

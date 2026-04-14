<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = ['email', 'project_id', 'invited_by', 'role', 'token', 'accepted_at', 'expires_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Invitation $inv) {
            if (empty($inv->token)) {
                $inv->token = bin2hex(random_bytes(32));
            }
            if (empty($inv->expires_at)) {
                $inv->expires_at = now()->addDays(7);
            }
        });
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function inviter(): BelongsTo { return $this->belongsTo(User::class, 'invited_by'); }

    public function isPending(): bool { return $this->accepted_at === null && $this->expires_at->isFuture(); }
    public function isExpired(): bool  { return $this->expires_at->isPast() && $this->accepted_at === null; }
    public function isAccepted(): bool { return $this->accepted_at !== null; }
}

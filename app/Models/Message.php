<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'conversation_id', 'direction', 'message_type', 'content', 'status',
        'error_message', 'metadata', 'processed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'out');
    }

    public function isFromUser(): bool { return $this->direction === 'in'; }
    public function isFromAgent(): bool { return $this->direction === 'out'; }
    public function isDone(): bool { return $this->status === 'done'; }
    public function isPending(): bool { return $this->status === 'pending'; }
}

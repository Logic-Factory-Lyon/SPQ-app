<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTask extends Model
{
    protected $fillable = [
        'agent_id', 'mac_machine_id', 'type', 'status',
        'payload', 'result', 'error_message', 'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function agent(): ?BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function macMachine(): BelongsTo
    {
        return $this->belongsTo(MacMachine::class);
    }
}
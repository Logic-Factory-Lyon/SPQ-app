<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['client_id', 'name', 'description', 'status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function macMachines(): HasMany
    {
        return $this->hasMany(MacMachine::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /** Agents linked via Mac Machine (legacy daemon). */
    public function agents(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Agent::class, MacMachine::class);
    }

    /** Agents configured as Telegram bots directly on this project. */
    public function telegramAgents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Agent::class)->whereNotNull('telegram_bot_token');
    }

    /** All agents for this project (mac + telegram), for assignment checks. */
    public function allAgents(): \Illuminate\Database\Eloquent\Builder
    {
        return Agent::where(fn($q) =>
            $q->whereHas('macMachine', fn($m) => $m->where('project_id', $this->id))
              ->orWhere('project_id', $this->id)
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function activeMachine(): ?MacMachine
    {
        return $this->macMachines()->where('status', 'online')->first();
    }
}

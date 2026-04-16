<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'mac_machine_id', 'project_id', 'name', 'profile', 'description',
        'system_prompt', 'workspace_path', 'status', 'parent_agent_id',
        'openclaw_profile_synced_at',
        'telegram_bot_username', 'telegram_bot_token',
    ];

    protected $casts = [
        'openclaw_profile_synced_at' => 'datetime',
    ];

    public function macMachine(): BelongsTo
    {
        return $this->belongsTo(MacMachine::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentAgent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_agent_id');
    }

    public function childAgents(): HasMany
    {
        return $this->hasMany(self::class, 'parent_agent_id');
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'agent_skill')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AgentTask::class);
    }

    public function hasSkill(string $slug): bool
    {
        return $this->skills()->where('slug', $slug)->where('is_active', true)->exists();
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isTelegram(): bool
    {
        return !is_null($this->telegram_bot_username);
    }

    public function telegramUrl(): ?string
    {
        return $this->telegram_bot_username
            ? 'https://t.me/' . ltrim($this->telegram_bot_username, '@')
            : null;
    }
}
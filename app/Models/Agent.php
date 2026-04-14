<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'mac_machine_id', 'project_id', 'name', 'profile',
        'telegram_bot_username', 'telegram_bot_token',
    ];

    public function macMachine(): BelongsTo
    {
        return $this->belongsTo(MacMachine::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
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

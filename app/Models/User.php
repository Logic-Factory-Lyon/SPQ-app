<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'client_id', 'name', 'email', 'password', 'role',
        'avatar', 'locale', 'timezone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }
    public function isClient(): bool { return $this->role === 'client'; }
    public function isManager(): bool { return $this->role === 'manager'; }
    public function isEmployee(): bool { return $this->role === 'employee'; }

    public function belongsToProject(Project $project): bool
    {
        return $this->projectMembers()->where('project_id', $project->id)->exists();
    }

    public function memberInProject(int $projectId): ?ProjectMember
    {
        return $this->projectMembers()->where('project_id', $projectId)->first();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'category',
        'prompt_template', 'allowed_tools', 'is_active',
    ];

    protected $casts = [
        'allowed_tools' => 'array',
        'is_active' => 'boolean',
    ];

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'agent_skill')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
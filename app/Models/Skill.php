<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'category',
        'prompt_template', 'parameter_schema', 'output_schema',
        'handler_type', 'allowed_tools', 'action_handlers',
        'is_active', 'version',
    ];

    protected $casts = [
        'allowed_tools'    => 'array',
        'parameter_schema' => 'array',
        'output_schema'    => 'array',
        'action_handlers'  => 'array',
        'is_active'        => 'boolean',
        'version'          => 'integer',
    ];

    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'agent_skill')
            ->withTimestamps();
    }

    public function executions(): HasMany
    {
        return $this->hasMany(SkillExecution::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Extract parameter field definitions from parameter_schema (JSON Schema).
     * Returns an array of {key, label, type, required, description, format} for the UI.
     */
    public function getParamFieldsAttribute(): array
    {
        // Prefer structured parameter_schema
        if ($this->parameter_schema && isset($this->parameter_schema['properties'])) {
            $required = $this->parameter_schema['required'] ?? [];
            $fields = [];
            foreach ($this->parameter_schema['properties'] as $key => $def) {
                $fields[] = [
                    'key'         => $key,
                    'label'       => $def['description'] ?? ucfirst(str_replace('_', ' ', $key)),
                    'type'        => $def['type'] ?? 'string',
                    'format'      => $def['format'] ?? null,
                    'required'    => in_array($key, $required),
                    'description' => $def['description'] ?? null,
                    'enum'        => $def['enum'] ?? null,
                ];
            }
            return $fields;
        }

        // Fallback: extract from {{param}} placeholders in prompt_template
        preg_match_all('/\{\{(\w+)\}\}/', $this->prompt_template ?? '', $matches);
        $fields = [];
        foreach ($matches[1] as $param) {
            $fields[] = [
                'key'         => $param,
                'label'       => ucfirst(str_replace('_', ' ', $param)),
                'type'        => 'string',
                'format'      => null,
                'required'    => true,
                'description' => null,
                'enum'        => null,
            ];
        }
        return $fields;
    }

    /**
     * Convert to the .skill.json format used on disk by the daemon.
     */
    public function toSkillJson(): array
    {
        return [
            'name'             => $this->name,
            'slug'             => $this->slug,
            'icon'             => $this->icon,
            'category'         => $this->category,
            'handler_type'     => $this->handler_type,
            'version'          => $this->version,
            'parameter_schema'  => $this->parameter_schema,
            'output_schema'    => $this->output_schema,
            'allowed_tools'    => $this->allowed_tools ?? [],
            'action_handlers'  => $this->action_handlers ?? [],
            'prompt_template'  => $this->prompt_template,
        ];
    }
}
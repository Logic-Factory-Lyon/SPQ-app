<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillExecution extends Model
{
    protected $fillable = [
        'skill_id', 'message_id', 'project_id', 'agent_id', 'employee_id',
        'parameters', 'status', 'tool_calls', 'output', 'artifacts',
        'started_at', 'finished_at', 'duration_ms',
    ];

    protected $casts = [
        'parameters'  => 'array',
        'tool_calls'  => 'array',
        'output'      => 'array',
        'artifacts'   => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'duration_ms'  => 'integer',
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Mark the execution as running.
     */
    public function markRunning(): void
    {
        $this->update([
            'status'     => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the execution as successful.
     */
    public function markSuccess(array $output, ?array $artifacts = null, ?array $toolCalls = null): void
    {
        $this->update([
            'status'      => 'success',
            'output'      => $output,
            'artifacts'   => $artifacts,
            'tool_calls'  => $toolCalls,
            'finished_at' => now(),
            'duration_ms' => $this->started_at
                ? now()->diffInMilliseconds($this->started_at)
                : null,
        ]);
    }

    /**
     * Mark the execution as failed.
     */
    public function markError(string $errorMessage): void
    {
        $this->update([
            'status'      => 'error',
            'output'      => ['error' => $errorMessage],
            'finished_at' => now(),
            'duration_ms' => $this->started_at
                ? now()->diffInMilliseconds($this->started_at)
                : null,
        ]);
    }

    /**
     * Mark execution as waiting for human approval.
     */
    public function markHumanApproval(array $question): void
    {
        $this->update([
            'status' => 'human_approval',
            'output'  => ['question' => $question],
        ]);
    }
}
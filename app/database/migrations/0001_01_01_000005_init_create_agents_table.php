<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mac_machine_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('name');
            $table->string('profile')->nullable();
            $table->text('description')->nullable();
            $table->text('system_prompt')->nullable();
            $table->string('workspace_path')->nullable();
            $table->enum('status', ['draft', 'initializing', 'ready', 'error'])->default('draft');
            $table->timestamp('openclaw_profile_synced_at')->nullable();
            $table->string('telegram_bot_username')->nullable();
            $table->string('telegram_bot_token')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
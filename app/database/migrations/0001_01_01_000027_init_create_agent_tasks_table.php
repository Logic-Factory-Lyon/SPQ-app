<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('mac_machine_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['initialize', 'resync', 'destroy']);
            $table->enum('status', ['pending', 'processing', 'done', 'error'])->default('pending');
            $table->json('payload')->nullable();
            $table->text('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
            $table->index(['mac_machine_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_tasks');
    }
};
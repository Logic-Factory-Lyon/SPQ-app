<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['in', 'out'])->comment('in=from user, out=to openclaw');
            $table->enum('message_type', ['text', 'skill'])->default('text');
            $table->longText('content');
            $table->enum('status', ['pending', 'processing', 'done', 'error', 'response'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['conversation_id', 'status']);
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
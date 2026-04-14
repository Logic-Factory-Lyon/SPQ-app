<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mac_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->char('token', 64)->unique();
            $table->enum('status', ['online', 'offline', 'unknown'])->default('unknown');
            $table->timestamp('last_seen_at')->nullable();
            $table->json('metadata')->nullable(); // OS info, openclaw version, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mac_machines');
    }
};

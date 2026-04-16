<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend skills table with structured skill fields
        Schema::table('skills', function (Blueprint $table) {
            $table->json('parameter_schema')->nullable()->after('prompt_template');
            $table->json('output_schema')->nullable()->after('parameter_schema');
            $table->string('handler_type', 30)->default('prompt')->after('output_schema');
            $table->json('action_handlers')->nullable()->after('allowed_tools');
            $table->unsignedInteger('version')->default(1)->after('is_active');
        });

        // Create skill_executions table for audit trail
        Schema::create('skill_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->json('parameters');
            $table->string('status', 30)->default('pending');
            $table->json('tool_calls')->nullable();
            $table->json('output')->nullable();
            $table->json('artifacts')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['agent_id', 'skill_id']);
            $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
        });

        // Create documents table for skill artifacts
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('doc_type', 50)->default('report');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->index(['project_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_executions');
        Schema::dropIfExists('documents');

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn([
                'parameter_schema', 'output_schema', 'handler_type',
                'action_handlers', 'version',
            ]);
        });
    }
};
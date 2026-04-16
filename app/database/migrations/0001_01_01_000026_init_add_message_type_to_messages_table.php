<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->enum('message_type', ['text', 'skill'])->default('text')->after('direction');
            $table->json('metadata')->nullable()->after('error_message');
        });

        // Add 'response' to the status enum
        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('pending','processing','done','error','response') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('message_type');
            $table->dropColumn('metadata');
        });

        DB::statement("ALTER TABLE messages MODIFY COLUMN status ENUM('pending','processing','done','error') NOT NULL DEFAULT 'pending'");
    }
};
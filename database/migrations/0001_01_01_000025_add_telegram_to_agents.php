<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Step 1: add Telegram columns
        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->after('id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();

            // Telegram bot username (e.g. "nestor_bot") → https://t.me/nestor_bot
            $table->string('telegram_bot_username', 100)->nullable()->after('profile');
            // Telegram bot token (optional — used only if SPQ registers the webhook for history sync)
            $table->string('telegram_bot_token', 200)->nullable()->unique()->after('telegram_bot_username');
        });

        // Step 2: make mac_machine_id and profile nullable (Telegram agents don't need them)
        Schema::table('agents', function (Blueprint $table) {
            $table->unsignedBigInteger('mac_machine_id')->nullable()->change();
            $table->string('profile')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn(['project_id', 'telegram_bot_username', 'telegram_bot_token']);
            $table->unsignedBigInteger('mac_machine_id')->nullable(false)->change();
            $table->string('profile')->nullable(false)->change();
        });
    }
};

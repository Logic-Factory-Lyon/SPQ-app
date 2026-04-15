<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('lang', 5);
            $table->string('subject');
            $table->text('body');
            $table->timestamps();

            $table->unique(['key', 'lang']);
        });

        Schema::create('email_settings', function (Blueprint $table) {
            $table->id();
            $table->string('lang', 5)->unique();
            $table->text('footer_html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_settings');
        Schema::dropIfExists('email_templates');
    }
};
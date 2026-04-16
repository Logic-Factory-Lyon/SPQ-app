<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vat_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->decimal('rate', 5, 4);
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_rates');
    }
};
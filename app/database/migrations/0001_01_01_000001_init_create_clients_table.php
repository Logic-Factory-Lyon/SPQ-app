<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country_code', 2)->default('FR');
            $table->string('vat_number', 30)->nullable();
            $table->string('stripe_customer_id', 191)->unique()->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
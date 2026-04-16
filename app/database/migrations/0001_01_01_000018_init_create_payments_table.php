<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['stripe', 'bank_transfer', 'cheque', 'cash', 'other'])->default('stripe');
            $table->string('stripe_payment_intent_id', 191)->unique()->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['invoice_id', 'paid_at']);
            $table->index(['client_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
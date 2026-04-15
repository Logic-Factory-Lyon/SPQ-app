<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('quote_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('number')->unique();
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded'])->default('draft');
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('subtotal_ht', 10, 2)->default(0);
            $table->decimal('total_vat', 10, 2)->default(0);
            $table->decimal('total_ttc', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('stripe_invoice_id', 191)->unique()->nullable();
            $table->string('stripe_payment_intent_id', 191)->unique()->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index('due_date');
        });

        // Add FK from quotes.converted_to_invoice_id to invoices.id (circular reference)
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreign('converted_to_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropForeign(['converted_to_invoice_id']);
        });

        Schema::dropIfExists('invoices');
    }
};
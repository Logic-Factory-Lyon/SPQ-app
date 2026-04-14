<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained('quotes')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('vat_rate_id')->constrained('vat_rates');
            $table->text('description');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price_ht', 10, 2)->default(0);
            $table->decimal('line_total_ht', 10, 2)->default(0);
            $table->decimal('line_total_vat', 10, 2)->default(0);
            $table->decimal('line_total_ttc', 10, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
    }
};

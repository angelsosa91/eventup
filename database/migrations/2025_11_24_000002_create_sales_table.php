<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('sale_number', 20)->unique();
            $table->date('sale_date');

            // Totales separados por tasa de IVA (Paraguay)
            $table->decimal('subtotal_exento', 15, 2)->default(0);
            $table->decimal('subtotal_5', 15, 2)->default(0);
            $table->decimal('iva_5', 15, 2)->default(0);
            $table->decimal('subtotal_10', 15, 2)->default(0);
            $table->decimal('iva_10', 15, 2)->default(0);

            $table->decimal('total', 15, 2)->default(0);

            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->string('payment_method', 50)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'sale_number']);
            $table->index(['tenant_id', 'sale_date']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

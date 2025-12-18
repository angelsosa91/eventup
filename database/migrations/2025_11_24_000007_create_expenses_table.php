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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('expense_number', 20)->unique();
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();

            // Documento de respaldo
            $table->string('document_number', 50)->nullable()->comment('Número de factura/recibo');
            $table->string('description', 255);

            // Montos
            $table->decimal('amount', 15, 2);
            $table->integer('tax_rate')->default(10)->comment('Tasa de IVA: 0, 5, 10');
            $table->decimal('tax_amount', 15, 2)->default(0);

            // Estado y pago
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->string('payment_method', 50)->default('Efectivo');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('expense_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('purchase_number', 20)->unique();
            $table->date('purchase_date');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();

            // Documento del proveedor
            $table->string('invoice_number', 50)->nullable()->comment('Número de factura del proveedor');

            // Totales por tipo de IVA
            $table->decimal('subtotal_exento', 15, 2)->default(0);
            $table->decimal('subtotal_5', 15, 2)->default(0);
            $table->decimal('iva_5', 15, 2)->default(0);
            $table->decimal('subtotal_10', 15, 2)->default(0);
            $table->decimal('iva_10', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // Estado y pago
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->string('payment_method', 50)->default('Contado');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('tenant_id');
            $table->index('purchase_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};

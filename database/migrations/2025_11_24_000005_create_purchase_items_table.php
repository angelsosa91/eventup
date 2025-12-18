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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name', 255);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2)->comment('Precio de compra unitario');
            $table->integer('tax_rate')->default(10)->comment('Tasa de IVA: 0, 5, 10');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('iva', 15, 2)->default(0);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};

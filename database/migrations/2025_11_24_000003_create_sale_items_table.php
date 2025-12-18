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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->string('product_name', 255); // Guardar nombre por si se elimina el producto
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->integer('tax_rate')->default(10); // 0, 5, 10

            $table->decimal('subtotal', 15, 2); // quantity * unit_price
            $table->decimal('tax_amount', 15, 2); // IVA calculado
            $table->decimal('total', 15, 2); // subtotal (IVA incluido en Paraguay)

            $table->timestamps();

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

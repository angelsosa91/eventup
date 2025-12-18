<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('barcode', 50)->nullable();
            $table->string('unit', 20)->default('UND'); // Unidad de medida
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('stock', 15, 2)->default(0);
            $table->decimal('min_stock', 15, 2)->default(0);
            $table->decimal('max_stock', 15, 2)->nullable();
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'code']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

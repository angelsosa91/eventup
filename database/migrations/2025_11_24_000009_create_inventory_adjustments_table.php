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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('adjustment_number', 20)->unique();
            $table->date('adjustment_date');
            $table->foreignId('user_id')->constrained();
            $table->enum('type', ['in', 'out'])->comment('in=entrada, out=salida');
            $table->string('reason', 100)->comment('Motivo del ajuste');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('adjustment_date');
        });

        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('product_name', 255);
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
        Schema::dropIfExists('inventory_adjustments');
    }
};

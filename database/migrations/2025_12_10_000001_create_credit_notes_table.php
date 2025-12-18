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
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('credit_note_number')->unique();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('reason', ['return', 'discount', 'error', 'cancellation'])->default('return');
            $table->enum('type', ['total', 'partial'])->default('total');
            $table->decimal('subtotal_0', 15, 2)->default(0);
            $table->decimal('subtotal_5', 15, 2)->default(0);
            $table->decimal('subtotal_10', 15, 2)->default(0);
            $table->decimal('iva_5', 15, 2)->default(0);
            $table->decimal('iva_10', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'sale_id']);
        });

        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 15, 2);
            $table->decimal('price', 15, 2);
            $table->enum('iva_type', ['0', '5', '10'])->default('10');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('iva_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();

            $table->index('credit_note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
    }
};

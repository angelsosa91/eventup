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
        Schema::create('remissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('remission_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('delivery_address')->nullable();
            $table->enum('reason', ['transfer', 'consignment', 'demo', 'delivery'])->default('delivery');
            $table->enum('status', ['draft', 'confirmed', 'delivered', 'invoiced', 'cancelled'])->default('draft');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'sale_id']);
        });

        Schema::create('remission_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remission_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 15, 2);
            $table->decimal('reserved_quantity', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('remission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remission_items');
        Schema::dropIfExists('remissions');
    }
};

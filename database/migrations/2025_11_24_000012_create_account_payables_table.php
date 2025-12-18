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
        Schema::create('account_payables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('document_number', 20)->unique();
            $table->date('document_date');
            $table->date('due_date');
            $table->foreignId('supplier_id')->constrained();
            $table->string('supplier_name', 255);
            $table->foreignId('purchase_id')->nullable()->constrained();
            $table->string('purchase_number', 20)->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2);
            $table->enum('status', ['pending', 'partial', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('document_date');
            $table->index('due_date');
            $table->index('supplier_id');
            $table->index('status');
        });

        Schema::create('account_payable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_payable_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number', 20);
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'check', 'card', 'other'])->default('cash');
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_payable_payments');
        Schema::dropIfExists('account_payables');
    }
};

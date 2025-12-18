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
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('register_number', 20)->unique();
            $table->date('register_date');
            $table->foreignId('user_id')->constrained()->comment('Usuario que abre la caja');
            $table->decimal('opening_balance', 15, 2)->comment('Saldo inicial');
            $table->decimal('sales_cash', 15, 2)->default(0)->comment('Ventas en efectivo');
            $table->decimal('collections', 15, 2)->default(0)->comment('Cobros recibidos');
            $table->decimal('payments', 15, 2)->default(0)->comment('Pagos realizados');
            $table->decimal('expenses', 15, 2)->default(0)->comment('Gastos');
            $table->decimal('expected_balance', 15, 2)->default(0)->comment('Saldo teórico');
            $table->decimal('actual_balance', 15, 2)->nullable()->comment('Saldo real contado');
            $table->decimal('difference', 15, 2)->default(0)->comment('Diferencia (sobrante/faltante)');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('register_date');
            $table->index('status');
            $table->index(['user_id', 'register_date', 'status']);
        });

        Schema::create('cash_register_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense'])->comment('Tipo: ingreso o egreso');
            $table->enum('concept', ['sale', 'collection', 'payment', 'expense', 'other'])->comment('Concepto del movimiento');
            $table->string('description', 255);
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->nullable()->comment('Referencia al documento relacionado');
            $table->timestamps();

            $table->index('type');
            $table->index('concept');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_movements');
        Schema::dropIfExists('cash_registers');
    }
};

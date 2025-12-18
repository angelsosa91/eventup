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
        Schema::create('account_chart', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('account_chart')->nullOnDelete();

            $table->string('code', 20)->comment('Código de cuenta (ej: 1.1.01.001)');
            $table->string('name')->comment('Nombre de la cuenta');
            $table->text('description')->nullable();

            $table->enum('account_type', ['asset', 'liability', 'equity', 'income', 'expense'])
                ->comment('Tipo: Activo, Pasivo, Patrimonio, Ingreso, Gasto');

            $table->enum('nature', ['debit', 'credit'])
                ->comment('Naturaleza: Deudora o Acreedora');

            $table->integer('level')->default(1)->comment('Nivel de jerarquía (1, 2, 3, etc.)');

            $table->boolean('is_detail')->default(false)
                ->comment('Si es cuenta de detalle (recibe movimientos)');

            $table->boolean('is_active')->default(true);

            $table->decimal('opening_balance', 15, 2)->default(0)
                ->comment('Saldo de apertura');

            $table->decimal('current_balance', 15, 2)->default(0)
                ->comment('Saldo actual');

            $table->timestamps();

            // Índices
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'account_type']);
            $table->index(['tenant_id', 'is_detail']);
            $table->index(['tenant_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_chart');
    }
};

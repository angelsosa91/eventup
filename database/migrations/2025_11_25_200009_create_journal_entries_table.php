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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('entry_number')->comment('Número de asiento');
            $table->date('entry_date')->comment('Fecha del asiento');
            $table->string('period', 7)->comment('Período contable (YYYY-MM)');

            $table->enum('entry_type', ['manual', 'automatic'])
                ->default('manual')
                ->comment('Tipo: Manual o Automático');

            $table->enum('status', ['draft', 'posted', 'cancelled'])
                ->default('draft')
                ->comment('Estado: Borrador, Contabilizado, Anulado');

            $table->text('description')->comment('Descripción del asiento');
            $table->text('notes')->nullable();

            // Referencias opcionales a documentos de origen
            $table->string('reference_type')->nullable()
                ->comment('Tipo de referencia: sale, purchase, payment, etc.');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('ID del documento de referencia');

            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);

            $table->boolean('is_balanced')->default(false)
                ->comment('Si está balanceado (débitos = créditos)');

            $table->timestamp('posted_at')->nullable()
                ->comment('Fecha de contabilización');

            $table->timestamps();

            // Índices
            $table->unique(['tenant_id', 'entry_number']);
            $table->index(['tenant_id', 'entry_date']);
            $table->index(['tenant_id', 'period']);
            $table->index(['tenant_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};

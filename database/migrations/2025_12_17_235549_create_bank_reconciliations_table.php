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
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->string('reconciliation_number', 50)->unique();
            $table->date('reconciliation_date');
            $table->date('statement_start_date');
            $table->date('statement_end_date');
            $table->decimal('opening_balance', 15, 2); // Saldo inicial del estado de cuenta
            $table->decimal('closing_balance', 15, 2); // Saldo final del estado de cuenta
            $table->decimal('system_balance', 15, 2); // Saldo en el sistema
            $table->decimal('difference', 15, 2)->default(0); // Diferencia entre estado de cuenta y sistema
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bank_account_id'], 'idx_bank_rec_tenant_account');
            $table->index(['reconciliation_date', 'status'], 'idx_bank_rec_date_status');
            $table->index('reconciliation_number', 'idx_bank_rec_number');
        });

        Schema::create('bank_reconciliation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->onDelete('cascade');
            $table->foreignId('bank_transaction_id')->constrained('bank_transactions')->onDelete('cascade');
            $table->boolean('matched_in_statement')->default(true); // Si aparece en el estado de cuenta
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('bank_reconciliation_id', 'idx_bank_rec_line_rec_id');
            $table->index('bank_transaction_id', 'idx_bank_rec_line_trans_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_lines');
        Schema::dropIfExists('bank_reconciliations');
    }
};

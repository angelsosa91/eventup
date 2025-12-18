<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('account_number', 50)->unique();
            $table->string('account_name', 100);
            $table->string('bank_name', 100);
            $table->string('bank_code', 20)->nullable(); // Código del banco
            $table->enum('account_type', ['checking', 'savings', 'credit'])->default('checking'); // Cuenta corriente, ahorro, crédito
            $table->string('currency', 10)->default('PYG'); // Guaraníes
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('account_holder', 100)->nullable(); // Titular de la cuenta
            $table->string('swift_code', 20)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index('account_number');
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->enum('type', ['deposit', 'withdrawal', 'transfer_in', 'transfer_out', 'check', 'charge', 'interest']);
            // deposit: depósito, withdrawal: retiro, transfer_in/out: transferencia, check: cheque, charge: cargo bancario, interest: interés
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->nullable(); // Número de cheque, transferencia, etc.
            $table->string('concept', 255);
            $table->text('description')->nullable();

            // Relación con otros módulos
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->onDelete('set null');
            $table->foreignId('account_receivable_payment_id')->nullable()->constrained('account_receivable_payments')->onDelete('set null');
            $table->foreignId('account_payable_payment_id')->nullable()->constrained('account_payable_payments')->onDelete('set null');

            // Para transferencias entre cuentas
            $table->foreignId('related_transaction_id')->nullable()->constrained('bank_transactions')->onDelete('set null');
            $table->foreignId('destination_account_id')->nullable()->constrained('bank_accounts')->onDelete('set null');

            $table->decimal('balance_after', 15, 2); // Saldo después de la transacción
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->boolean('reconciled')->default(false); // Para conciliación bancaria
            $table->date('reconciled_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bank_account_id'], 'idx_bank_trans_tenant_account');
            $table->index(['transaction_date', 'status'], 'idx_bank_trans_date_status');
            $table->index('reconciled', 'idx_bank_trans_reconciled');
        });

        Schema::create('checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('check_number', 50);
            $table->date('issue_date');
            $table->date('due_date')->nullable(); // Fecha de cobro/pago
            $table->decimal('amount', 15, 2);

            $table->enum('type', ['issued', 'received']); // Emitido o recibido

            // Para cheques emitidos
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->onDelete('cascade');
            $table->string('payee', 100)->nullable(); // A la orden de (beneficiario)

            // Para cheques recibidos
            $table->string('bank_name', 100)->nullable(); // Banco del cheque recibido
            $table->string('issuer', 100)->nullable(); // Emisor del cheque recibido

            $table->string('concept', 255);
            $table->text('notes')->nullable();

            // Relaciones
            $table->foreignId('bank_transaction_id')->nullable()->constrained('bank_transactions')->onDelete('set null');
            $table->foreignId('account_receivable_payment_id')->nullable()->constrained('account_receivable_payments')->onDelete('set null');
            $table->foreignId('account_payable_payment_id')->nullable()->constrained('account_payable_payments')->onDelete('set null');

            $table->enum('status', ['pending', 'deposited', 'cashed', 'bounced', 'cancelled'])->default('pending');
            // pending: pendiente, deposited: depositado, cashed: cobrado, bounced: rechazado, cancelled: anulado

            $table->date('cashed_date')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['check_number', 'bank_account_id']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checks');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};

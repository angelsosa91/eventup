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
        Schema::table('expense_categories', function (Blueprint $table) {
            // Agregar columna account_id para vincular con el plan de cuentas
            if (!Schema::hasColumn('expense_categories', 'account_id')) {
                $table->foreignId('account_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('account_chart')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            if (Schema::hasColumn('expense_categories', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
        });
    }
};

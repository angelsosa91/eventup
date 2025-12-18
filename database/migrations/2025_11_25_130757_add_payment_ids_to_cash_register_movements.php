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
        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->foreignId('account_receivable_payment_id')->nullable()->after('purchase_id')->constrained()->nullOnDelete();
            $table->foreignId('account_payable_payment_id')->nullable()->after('account_receivable_payment_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->dropForeign(['account_receivable_payment_id']);
            $table->dropForeign(['account_payable_payment_id']);
            $table->dropColumn(['account_receivable_payment_id', 'account_payable_payment_id']);
        });
    }
};

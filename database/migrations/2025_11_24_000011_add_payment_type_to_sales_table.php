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
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('status');
            $table->integer('credit_days')->nullable()->after('payment_type')->comment('Días de crédito');
            $table->date('credit_due_date')->nullable()->after('credit_days')->comment('Fecha de vencimiento del crédito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'credit_days', 'credit_due_date']);
        });
    }
};

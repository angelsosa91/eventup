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
        Schema::table('account_receivable_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('user_id');
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_receivable_payments', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });
    }
};

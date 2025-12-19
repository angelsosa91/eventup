<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->unsignedBigInteger('refunded_from_id')->nullable()->after('journal_entry_id');
            $table->foreign('refunded_from_id')->references('id')->on('contributions')->onDelete('set null');

            // Agregar índice para mejorar búsquedas
            $table->index('refunded_from_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropForeign(['refunded_from_id']);
            $table->dropColumn('refunded_from_id');
        });
    }
};

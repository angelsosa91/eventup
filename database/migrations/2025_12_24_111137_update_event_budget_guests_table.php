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
        Schema::table('event_budget_guests', function (Blueprint $table) {
            $table->renameColumn('phone', 'cedula');
            $table->dropForeign(['table_id']);
            $table->dropColumn('table_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_budget_guests', function (Blueprint $table) {
            $table->renameColumn('cedula', 'phone');
            $table->foreignId('table_id')->nullable()->constrained('event_tables')->onDelete('set null');
        });
    }
};

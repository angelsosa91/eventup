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
        Schema::table('event_tables', function (Blueprint $table) {
            $table->string('color')->default('#e0e0e0')->after('capacity');
        });

        Schema::table('event_budgets', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('notes')->constrained('event_tables')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_budgets', function (Blueprint $table) {
            $table->dropForeign(['table_id']);
            $table->dropColumn('table_id');
        });

        Schema::table('event_tables', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};

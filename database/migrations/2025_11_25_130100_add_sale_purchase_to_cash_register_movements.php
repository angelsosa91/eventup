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
        $tableName = 'cash_register_movements';
        $databaseName = config('database.connections.mysql.database');

        // Check if columns already exist
        $columns = \DB::select(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = ?
             AND COLUMN_NAME IN ('sale_id', 'purchase_id')",
            [$databaseName, $tableName]
        );

        $existingColumns = collect($columns)->pluck('COLUMN_NAME')->toArray();

        Schema::table('cash_register_movements', function (Blueprint $table) use ($existingColumns) {
            if (!in_array('sale_id', $existingColumns)) {
                $table->foreignId('sale_id')->nullable()->after('reference')->constrained()->nullOnDelete();
            }
            if (!in_array('purchase_id', $existingColumns)) {
                $table->foreignId('purchase_id')->nullable()->after('sale_id')->constrained()->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = 'cash_register_movements';
        $databaseName = config('database.connections.mysql.database');

        // Check if columns exist before dropping
        $columns = \DB::select(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = ?
             AND COLUMN_NAME IN ('sale_id', 'purchase_id')",
            [$databaseName, $tableName]
        );

        $existingColumns = collect($columns)->pluck('COLUMN_NAME')->toArray();

        Schema::table('cash_register_movements', function (Blueprint $table) use ($existingColumns) {
            if (in_array('sale_id', $existingColumns)) {
                $table->dropForeign(['sale_id']);
                $table->dropColumn('sale_id');
            }
            if (in_array('purchase_id', $existingColumns)) {
                $table->dropForeign(['purchase_id']);
                $table->dropColumn('purchase_id');
            }
        });
    }
};

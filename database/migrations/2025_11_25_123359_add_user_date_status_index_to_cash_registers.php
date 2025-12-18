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
        // Check if index already exists using raw SQL
        $indexName = 'cash_registers_user_id_register_date_status_index';
        $tableName = 'cash_registers';
        $databaseName = config('database.connections.mysql.database');

        $indexExists = \DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?",
            [$databaseName, $tableName, $indexName]
        );

        if ($indexExists[0]->count == 0) {
            Schema::table('cash_registers', function (Blueprint $table) {
                $table->index(['user_id', 'register_date', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexName = 'cash_registers_user_id_register_date_status_index';
        $tableName = 'cash_registers';
        $databaseName = config('database.connections.mysql.database');

        $indexExists = \DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?",
            [$databaseName, $tableName, $indexName]
        );

        if ($indexExists[0]->count > 0) {
            Schema::table('cash_registers', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'register_date', 'status']);
            });
        }
    }
};

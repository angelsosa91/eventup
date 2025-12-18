<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait SafeMigration
{
    /**
     * Check if a column exists in a table
     */
    protected function columnExists(string $table, string $column): bool
    {
        $database = config('database.connections.mysql.database');

        $result = DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = ?
             AND COLUMN_NAME = ?",
            [$database, $table, $column]
        );

        return $result[0]->count > 0;
    }

    /**
     * Check if multiple columns exist in a table
     */
    protected function columnsExist(string $table, array $columns): array
    {
        $database = config('database.connections.mysql.database');

        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $params = array_merge([$database, $table], $columns);

        $result = DB::select(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = ?
             AND COLUMN_NAME IN ($placeholders)",
            $params
        );

        return collect($result)->pluck('COLUMN_NAME')->toArray();
    }

    /**
     * Check if an index exists in a table
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $database = config('database.connections.mysql.database');

        $result = DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.statistics
             WHERE table_schema = ?
             AND table_name = ?
             AND index_name = ?",
            [$database, $table, $indexName]
        );

        return $result[0]->count > 0;
    }

    /**
     * Check if a foreign key exists in a table
     */
    protected function foreignKeyExists(string $table, string $foreignKeyName): bool
    {
        $database = config('database.connections.mysql.database');

        $result = DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ?
             AND TABLE_NAME = ?
             AND CONSTRAINT_NAME = ?
             AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $foreignKeyName]
        );

        return $result[0]->count > 0;
    }

    /**
     * Check if a table exists
     */
    protected function tableExists(string $table): bool
    {
        $database = config('database.connections.mysql.database');

        $result = DB::select(
            "SELECT COUNT(*) as count
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = ?
             AND TABLE_NAME = ?",
            [$database, $table]
        );

        return $result[0]->count > 0;
    }
}

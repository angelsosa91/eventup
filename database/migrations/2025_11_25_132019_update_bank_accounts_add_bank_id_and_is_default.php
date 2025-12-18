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
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Agregar bank_id (nullable porque ya hay datos)
            $table->foreignId('bank_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();

            // Agregar is_default para cuenta predeterminada
            $table->boolean('is_default')->default(false)->after('status');

            // Hacer bank_name nullable ya que ahora tendremos bank_id
            $table->string('bank_name')->nullable()->change();

            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropIndex(['is_default']);
            $table->dropColumn(['bank_id', 'is_default']);
            $table->string('bank_name')->nullable(false)->change();
        });
    }
};

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
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('key')->comment('Clave de configuración');
            $table->unsignedBigInteger('account_id')->nullable()->comment('ID de la cuenta contable');
            $table->string('description')->nullable()->comment('Descripción de la configuración');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('account_chart')->onDelete('set null');

            // Cada tenant tiene una configuración única por key
            $table->unique(['tenant_id', 'key']);

            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};

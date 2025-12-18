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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->string('company_name');
            $table->string('ruc')->nullable()->comment('RUC de la empresa');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('slogan')->nullable();
            $table->string('currency', 10)->default('PYG')->comment('Código de moneda');
            $table->string('currency_symbol', 5)->default('Gs.');
            $table->integer('decimal_places')->default(0)->comment('Decimales para moneda');
            $table->string('date_format', 20)->default('d/m/Y');
            $table->string('timezone', 50)->default('America/Asuncion');
            $table->boolean('invoice_requires_tax_id')->default(false)->comment('Requerir RUC del cliente');
            $table->integer('low_stock_threshold')->default(10)->comment('Umbral de stock bajo');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};

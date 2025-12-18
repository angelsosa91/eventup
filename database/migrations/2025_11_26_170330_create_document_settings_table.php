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
        Schema::create('document_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('document_type')->comment('sale, purchase, expense, adjustment, etc.');
            $table->string('prefix')->nullable()->comment('Prefijo del documento (FAC-, REC-, etc.)');
            $table->string('series')->nullable()->comment('Serie del documento (A, B, 001, etc.)');
            $table->integer('next_number')->default(1)->comment('Siguiente número a usar');
            $table->integer('padding')->default(5)->comment('Cantidad de ceros a la izquierda');
            $table->string('format')->comment('Formato: prefix-series-number o prefix-number');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'document_type', 'series']);
            $table->index(['tenant_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_settings');
    }
};

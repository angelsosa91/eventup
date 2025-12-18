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
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name')->comment('Nombre del impuesto (IVA 10%, IVA 5%, Exento)');
            $table->decimal('rate', 5, 2)->comment('Tasa del impuesto (0, 5, 10, etc.)');
            $table->string('code', 10)->nullable()->comment('Código del impuesto (IVA10, IVA5, EXE)');
            $table->boolean('is_default')->default(false)->comment('Impuesto por defecto');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};

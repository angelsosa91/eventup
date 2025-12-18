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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nombre oficial del banco');
            $table->string('short_name', 50)->comment('Nombre corto del banco');
            $table->string('code', 10)->nullable()->comment('Código SET o BCP');
            $table->string('swift_code', 20)->nullable()->comment('Código SWIFT/BIC');
            $table->string('country', 50)->default('Paraguay');
            $table->string('logo')->nullable()->comment('URL o path del logo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};

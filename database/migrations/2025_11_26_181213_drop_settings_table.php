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
        Schema::dropIfExists('settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index('tenant_id');
        });
    }
};

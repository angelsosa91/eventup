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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('email');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->string('failure_reason')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->index(['user_id', 'logged_at']);
            $table->index(['tenant_id', 'logged_at']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};

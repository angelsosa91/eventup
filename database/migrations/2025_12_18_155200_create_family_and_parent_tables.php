<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('budget_type', ['unique', 'individual'])->default('unique');
            $table->string('billing_name')->nullable();
            $table->string('billing_ruc')->nullable();
            $table->string('billing_email')->nullable();
            $table->timestamps();
        });

        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('document_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Customer is the student
            $table->string('relationship')->nullable(); // Father, Mother, etc.
            $table->timestamps();
        });

        Schema::create('delegates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable(); // Presidente, Tesorero, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegates');
        Schema::dropIfExists('parent_student');
        Schema::dropIfExists('parents');
        Schema::dropIfExists('families');
    }
};

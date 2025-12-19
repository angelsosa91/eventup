<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('event_date');
            $table->decimal('estimated_budget', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_date']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('event_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('event_id');
        });

        Schema::create('event_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('capacity')->default(0);
            $table->timestamps();

            $table->index('event_id');
        });

        Schema::create('event_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->nullable()->constrained('event_tables')->onDelete('set null');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamps();

            $table->index('event_id');
            $table->index(['event_id', 'table_id']);
            $table->index(['event_id', 'is_validated']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_guests');
        Schema::dropIfExists('event_tables');
        Schema::dropIfExists('event_budget_items');
        Schema::dropIfExists('events');
    }
};

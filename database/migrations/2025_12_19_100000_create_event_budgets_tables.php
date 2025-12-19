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
        Schema::create('event_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('family_name')->nullable();
            $table->date('budget_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'event_id']);
            $table->index(['tenant_id', 'customer_id']);
        });

        Schema::create('event_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_budget_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('event_budget_id');
        });

        Schema::create('event_budget_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_budget_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone')->nullable();
            // table_id linking to event_tables created in previous migration
            $table->foreignId('table_id')->nullable()->constrained('event_tables')->onDelete('set null');
            $table->timestamps();

            $table->index('event_budget_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_budget_guests');
        Schema::dropIfExists('event_budget_items');
        Schema::dropIfExists('event_budgets');
    }
};

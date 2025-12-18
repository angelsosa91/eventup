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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // El Alumno
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('contribution_number', 20)->unique();
            $table->date('contribution_date');
            $table->decimal('amount', 15, 2);

            $table->enum('payment_method', ['Efectivo', 'Transferencia'])->default('Efectivo');
            $table->string('reference')->nullable(); // Para el número de transferencia
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');

            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'contribution_number']);
            $table->index(['tenant_id', 'contribution_date']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->foreignId('contribution_id')->nullable()->after('sale_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_register_movements', function (Blueprint $table) {
            $table->dropForeign(['contribution_id']);
            $table->dropColumn('contribution_id');
        });
        Schema::dropIfExists('contributions');
    }
};

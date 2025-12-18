<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add new fields if they don't exist
            if (!Schema::hasColumn('customers', 'family_name')) {
                $table->string('family_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('customers', 'delegate_id')) {
                $table->foreignId('delegate_id')->nullable()->after('bachillerato_id')->constrained('delegates')->onDelete('set null');
            }
            if (!Schema::hasColumn('customers', 'billing_name')) {
                $table->string('billing_name')->nullable()->after('delegate_id');
            }
            if (!Schema::hasColumn('customers', 'billing_ruc')) {
                $table->string('billing_ruc')->nullable()->after('billing_name');
            }
            if (!Schema::hasColumn('customers', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('billing_ruc');
            }
            if (!Schema::hasColumn('customers', 'budget_type')) {
                $table->enum('budget_type', ['unique', 'parents'])->default('unique')->after('billing_email');
            }

            // Remove unused fields if they exist
            if (Schema::hasColumn('customers', 'family_id')) {
                // Check if foreign key exists before dropping
                try {
                    $table->dropForeign(['family_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                $table->dropColumn('family_id');
            }
            if (Schema::hasColumn('customers', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('customers', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('customers', 'credit_limit')) {
                $table->dropColumn('credit_limit');
            }
            if (Schema::hasColumn('customers', 'credit_days')) {
                $table->dropColumn('credit_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'family_id')) {
                $table->foreignId('family_id')->nullable()->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('customers', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('customers', 'country')) {
                $table->string('country')->nullable();
            }
            if (!Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('customers', 'credit_days')) {
                $table->integer('credit_days')->default(0);
            }

            $table->dropForeign(['delegate_id']);
            $table->dropColumn(['family_name', 'delegate_id', 'billing_name', 'billing_ruc', 'billing_email', 'budget_type']);
        });
    }
};

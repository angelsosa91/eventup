<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('tenant_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->date('birth_date')->nullable()->after('ruc');

            $table->foreignId('family_id')->nullable()->after('tenant_id')->constrained()->onDelete('set null');
            $table->foreignId('grade_id')->nullable()->after('family_id')->constrained('academic_grades')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->after('grade_id')->constrained('academic_sections')->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->after('section_id')->constrained('academic_shifts')->onDelete('set null');
            $table->foreignId('bachillerato_id')->nullable()->after('shift_id')->constrained('academic_bachilleratos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['bachillerato_id']);
            $table->dropForeign(['shift_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['family_id']);

            $table->dropColumn([
                'first_name',
                'last_name',
                'birth_date',
                'family_id',
                'grade_id',
                'section_id',
                'shift_id',
                'bachillerato_id'
            ]);
        });
    }
};

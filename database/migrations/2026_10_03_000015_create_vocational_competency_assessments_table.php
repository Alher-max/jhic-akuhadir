<?php

declare(strict_types=1);

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
        Schema::create('vocational_competency_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('scheme_name', 150);
            $table->string('assessor_name', 100);
            $table->string('institution_name', 150);
            $table->decimal('theory_score', 5, 2)->nullable();
            $table->decimal('practice_score', 5, 2);
            $table->decimal('final_score', 5, 2);
            $table->string('predicate', 30); // "Sangat Kompeten", "Kompeten", "Belum Kompeten"
            $table->string('certificate_number', 100)->nullable();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'academic_year_id', 'student_id'],
                'uniq_vocational_ukk'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocational_competency_assessments');
    }
};

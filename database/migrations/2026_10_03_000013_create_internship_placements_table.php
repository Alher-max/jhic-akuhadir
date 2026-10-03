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
        Schema::create('internship_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('industry_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('company_name', 150);
            $table->text('company_address')->nullable();
            $table->string('mentor_name', 100)->nullable();
            $table->string('mentor_position', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'academic_year_id', 'student_id'],
                'uniq_internship_placement'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_placements');
    }
};

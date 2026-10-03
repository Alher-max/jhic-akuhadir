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
        Schema::create('student_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sick_count')->default(0);
            $table->integer('permission_count')->default(0);
            $table->integer('alpha_count')->default(0);
            $table->text('homeroom_notes')->nullable();
            $table->string('promotion_status', 50)->nullable(); // Misal: "Naik ke Kelas XI", "Tinggal di Kelas X", "Lulus"
            $table->string('status')->default('draft'); // 'draft', 'submitted', 'locked' (ANSI SQL compliant - no enum)
            $table->timestamps();

            // Unique composite index untuk memastikan 1 rapor per siswa per semester/tahun ajaran
            $table->unique(
                ['tenant_id', 'academic_year_id', 'class_id', 'student_id'],
                'uniq_student_report_entry'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_reports');
    }
};

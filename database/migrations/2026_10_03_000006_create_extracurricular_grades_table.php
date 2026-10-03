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
        Schema::create('extracurricular_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('student_report_id')->constrained('student_reports')->cascadeOnDelete();
            $table->string('activity_name', 100); // Contoh: "Pramuka", "PMR", "Paskibra"
            $table->string('predicate', 30); // Contoh: "Sangat Baik", "Baik"
            $table->text('description')->nullable();
            $table->timestamps();

            // Index gabungan untuk performa per laporan siswa
            $table->index(['tenant_id', 'student_report_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracurricular_grades');
    }
};

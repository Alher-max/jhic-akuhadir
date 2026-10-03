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
        Schema::table('student_reports', function (Blueprint $table) {
            $table->string('verification_hash', 64)->nullable()->unique('uniq_student_report_hash');
            $table->timestamp('published_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_reports', function (Blueprint $table) {
            $table->dropUnique('uniq_student_report_hash');
            $table->dropColumn(['verification_hash', 'published_at']);
        });
    }
};

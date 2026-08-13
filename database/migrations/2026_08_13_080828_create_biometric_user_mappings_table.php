<?php

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
        Schema::create('biometric_user_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('biometric_pin');
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Unique constraint: one biometric PIN per school
            $table->unique(['school_id', 'biometric_pin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_user_mappings');
    }
};
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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('activity_schedule_id')->nullable()->constrained('activity_schedules')->nullOnDelete();
            $table->dateTime('punch_time');
            $table->enum('punch_type', ['in', 'out']);
            $table->enum('status', ['tepat_waktu', 'terlambat', 'pulang_cepat', 'izin_sakit'])->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'punch_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};

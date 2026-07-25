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
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('attendance_mode', ['formal_daily', 'non_formal_session'])->default('formal_daily')->after('attendance_method');
            $table->integer('session_late_tolerance_minutes')->default(10)->after('attendance_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['attendance_mode', 'session_late_tolerance_minutes']);
        });
    }
};

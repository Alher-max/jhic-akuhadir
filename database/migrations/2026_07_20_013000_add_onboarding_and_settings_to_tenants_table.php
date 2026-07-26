<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->integer('onboarding_step')->default(1);
            $table->boolean('onboarding_completed')->default(false);
            $table->string('attendance_method')->nullable();
            $table->string('device_token')->nullable();
            $table->string('wifi_bssid')->nullable();
            $table->string('gps_lat')->nullable();
            $table->string('gps_lng')->nullable();
            $table->integer('gps_radius')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_step',
                'onboarding_completed',
                'attendance_method',
                'device_token',
                'wifi_bssid',
                'gps_lat',
                'gps_lng',
                'gps_radius'
            ]);
        });
    }
};

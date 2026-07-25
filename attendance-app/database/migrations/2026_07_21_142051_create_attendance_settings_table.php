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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->boolean('method_rfid')->default(true);
            $table->boolean('method_qrcode')->default(true);
            $table->boolean('method_biometric')->default(false);
            $table->boolean('method_manual')->default(true);
            $table->boolean('method_wifi')->default(false);
            $table->json('wifi_allowed_ssids')->nullable();
            $table->json('wifi_allowed_macs')->nullable();
            $table->string('rfid_secret_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};

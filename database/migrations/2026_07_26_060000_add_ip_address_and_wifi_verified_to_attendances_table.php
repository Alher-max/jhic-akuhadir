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
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('attendances', 'is_wifi_verified')) {
                $table->boolean('is_wifi_verified')->default(false)->after('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'is_wifi_verified')) {
                $table->dropColumn('is_wifi_verified');
            }
            if (Schema::hasColumn('attendances', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};

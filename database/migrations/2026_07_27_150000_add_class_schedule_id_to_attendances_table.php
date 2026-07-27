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
            if (!Schema::hasColumn('attendances', 'class_schedule_id')) {
                $table->foreignId('class_schedule_id')
                    ->nullable()
                    ->after('tenant_id')
                    ->constrained('class_schedules')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'class_schedule_id')) {
                $table->dropForeign(['class_schedule_id']);
                $table->dropColumn('class_schedule_id');
            }
        });
    }
};

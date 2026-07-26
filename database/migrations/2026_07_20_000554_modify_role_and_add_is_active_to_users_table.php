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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff_student')->change();
            $table->boolean('is_active')->default(true)->after('role');
        });

        // Update existing 'admin' to 'owner'
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'admin')
            ->update(['role' => 'owner']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};

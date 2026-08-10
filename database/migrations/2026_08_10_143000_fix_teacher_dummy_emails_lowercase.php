<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update users table - convert dummy emails to lowercase
        DB::statement("UPDATE users SET email = LOWER(email) WHERE email LIKE 'guru.%@hadirsekolah.id'");
        
        // Update teachers table if it exists and has email column
        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'email')) {
            DB::statement("UPDATE teachers SET email = LOWER(email) WHERE email LIKE 'guru.%@hadirsekolah.id'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed - this is a one-way data fix
    }
};
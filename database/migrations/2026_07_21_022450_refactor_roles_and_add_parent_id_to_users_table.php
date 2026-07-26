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
            $table->unsignedBigInteger('parent_id')->nullable()->after('tenant_id');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            
            // Note: Since 'role' was converted to string in previous migration, 
            // we don't need to change the column type, just update the defaults
            $table->string('role')->default('student')->change();
        });

        // Convert existing roles
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'owner')->update(['role' => 'kepala_sekolah']);
        
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'super_admin')->update(['role' => 'admin_dapodik']);
            
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'manager_teacher')->update(['role' => 'wali_kelas']);
            
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'staff_student')->update(['role' => 'student']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->string('role')->default('staff_student')->change();
        });

        // Revert roles
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'kepala_sekolah')->update(['role' => 'owner']);
            
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'admin_dapodik')->update(['role' => 'super_admin']);
            
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'wali_kelas')->update(['role' => 'manager_teacher']);
            
        \Illuminate\Support\Facades\DB::table('users')
            ->where('role', 'student')->update(['role' => 'staff_student']);
    }
};

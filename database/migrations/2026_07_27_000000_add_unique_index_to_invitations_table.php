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
        // Clean duplicate invitation records by tenant_id and email, keeping the latest record
        $duplicates = DB::table('invitations')
            ->select('tenant_id', 'email', DB::raw('MAX(id) as max_id'))
            ->groupBy('tenant_id', 'email')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('invitations')
                ->where('tenant_id', $dup->tenant_id)
                ->where('email', $dup->email)
                ->where('id', '<', $dup->max_id)
                ->delete();
        }

        Schema::table('invitations', function (Blueprint $table) {
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tenants', 'npsn')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->string('npsn', 50)->nullable()->unique()->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tenants', 'npsn')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropUnique(['npsn']);
                $table->dropColumn('npsn');
            });
        }
    }
};

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
            $table->string('nis')->nullable()->after('nisn');
            $table->string('nik')->nullable()->after('nis');
            $table->enum('gender', ['L', 'P'])->nullable()->after('nik');
            $table->string('birth_place')->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('religion')->nullable()->after('birth_date');
            $table->string('father_name')->nullable()->after('religion');
            $table->string('mother_name')->nullable()->after('father_name');
            $table->string('parent_phone')->nullable()->after('mother_name');
            $table->text('address')->nullable()->after('parent_phone');
            $table->string('blood_type', 10)->nullable()->after('address');
            $table->text('medical_notes')->nullable()->after('blood_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nis',
                'nik',
                'gender',
                'birth_place',
                'birth_date',
                'religion',
                'father_name',
                'mother_name',
                'parent_phone',
                'address',
                'blood_type',
                'medical_notes',
            ]);
        });
    }
};

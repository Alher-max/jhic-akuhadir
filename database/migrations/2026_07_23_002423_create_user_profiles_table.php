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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // I. DATA IDENTITAS PRIBADI
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable(); // Laki-laki, Perempuan
            $table->string('religion')->nullable(); // Islam, Kristen, etc.
            $table->text('address')->nullable();
            $table->string('phone_number')->nullable();
            
            // II. DATA KEPEGAWAIAN & STATUS PEGAWAI
            $table->string('employee_id')->nullable(); // NIP/NIK
            $table->string('nuptk')->nullable();
            $table->string('employment_status')->nullable(); // PNS, PPPK, dll
            $table->string('rank_group')->nullable(); // Golongan
            $table->string('functional_position')->nullable(); // Jabatan fungsional
            
            // III. DATA PENUGASAN SEBAGAI KEPALA SEKOLAH (Optional)
            $table->string('school_name')->nullable();
            $table->string('school_npsn')->nullable();
            $table->string('school_ownership')->nullable();
            $table->string('school_level')->nullable();
            $table->string('sk_appointment')->nullable();
            $table->date('tmt_position')->nullable();
            $table->string('tenure_period')->nullable();
            
            // IV. RIWAYAT PENDIDIKAN TERTINGGI
            $table->string('highest_education')->nullable();
            $table->string('university_major')->nullable();
            
            // V. SERTIFIKASI & LEGALITAS KEPEMIMPINAN
            $table->string('has_educator_certificate')->nullable();
            $table->string('leadership_training_certificate')->nullable();
            
            // VI. RIWAYAT PENGALAMAN MANAJERIAL SEBELUMNYA
            $table->string('managerial_experience')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};

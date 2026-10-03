<?php

declare(strict_types=1);

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
        Schema::create('internship_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('internship_placement_id')->constrained('internship_placements')->cascadeOnDelete();
            $table->decimal('technical_score', 5, 2);
            $table->decimal('softskill_score', 5, 2);
            $table->decimal('attendance_score', 5, 2);
            $table->decimal('final_score', 5, 2);
            $table->string('predicate', 30);
            $table->text('technical_notes')->nullable();
            $table->text('softskill_notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'internship_placement_id'],
                'uniq_internship_assess'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internship_assessments');
    }
};

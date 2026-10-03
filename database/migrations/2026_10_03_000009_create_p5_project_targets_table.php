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
        Schema::create('p5_project_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
            $table->string('target_type', 30); // 'pancasila' atau 'rahmatan_lil_alamin'
            $table->string('dimension', 100);
            $table->string('element', 150)->nullable();
            $table->string('sub_element', 255);
            $table->text('target_description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'p5_project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p5_project_targets');
    }
};

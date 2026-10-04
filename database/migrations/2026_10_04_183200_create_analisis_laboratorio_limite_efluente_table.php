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
        Schema::create('analisis_laboratorio_limite_efluente', function (Blueprint $table) {
            $table->foreignId('analisis_laboratorio_id')
                ->constrained('analisis_laboratorio', 'analisis_laboratorio_id')
                ->cascadeOnDelete();
            $table->foreignId('limite_efluente_id')
                ->constrained('limite_efluente', 'limite_efluente_id')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['analisis_laboratorio_id', 'limite_efluente_id'], 'analisis_limite_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_laboratorio_limite_efluente');
    }
};

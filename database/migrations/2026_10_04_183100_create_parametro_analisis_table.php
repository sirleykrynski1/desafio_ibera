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
        Schema::create('parametro_analisis', function (Blueprint $table) {
            $table->id('parametro_analisis_id');
            $table->foreignId('analisis_laboratorio_id')
                ->constrained('analisis_laboratorio', 'analisis_laboratorio_id')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->string('valor_medido');
            $table->string('unidad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametro_analisis');
    }
};

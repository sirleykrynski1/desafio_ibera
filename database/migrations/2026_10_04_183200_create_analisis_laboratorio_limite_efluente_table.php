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
            $table->unsignedBigInteger('analisis_laboratorio_id');
            $table->unsignedBigInteger('limite_efluente_id');

            $table->timestamps();

            $table->foreign('analisis_laboratorio_id', 'fk_analisis_limite_analisis')
                ->references('analisis_laboratorio_id')
                ->on('analisis_laboratorio')
                ->onDelete('cascade');

            $table->foreign('limite_efluente_id', 'fk_analisis_limite_limite')
                ->references('limite_efluente_id')
                ->on('limite_efluente')
                ->onDelete('cascade');
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

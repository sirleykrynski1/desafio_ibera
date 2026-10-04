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
        Schema::create('analisis_laboratorio', function (Blueprint $table) {
            $table->id('analisis_laboratorio_id');
            $table->foreignId('establecimiento_id')
                ->constrained('establecimientos', 'id')
                ->cascadeOnDelete();
            $table->date('fecha_muestra');
            $table->string('laboratorio');
            $table->enum('resultado_final', ['Aprobado', 'Rechazado', 'Pendiente'])->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_laboratorio');
    }
};

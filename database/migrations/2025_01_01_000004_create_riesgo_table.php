<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('riesgos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establecimiento_id')
                ->constrained('establecimientos')
                ->cascadeOnDelete();
            $table->enum('nivel_riesgo', ['verde', 'amarillo', 'rojo']);
            $table->decimal('lluvia_pronosticada', 8, 2)->default(0);
            $table->timestamp('fecha_evaluacion');
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('riesgos');
    }
};

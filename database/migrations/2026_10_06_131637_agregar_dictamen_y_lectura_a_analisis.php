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
        Schema::table('analisis_laboratorio', function (Blueprint $table) {
            $table->enum('resultado_sugerido', ['cumple', 'alerta', 'incumple'])
                ->nullable()
                ->after('laboratorio')
                ->comment('Dictamen automático del evaluador, la decisión humana sigue en resultado_final');
            $table->enum('estado', ['pendiente_lectura', 'observado', 'evaluado', 'revisado'])
                ->default('pendiente_lectura')
                ->after('resultado_sugerido')
                ->comment('Avance de la lectura del PDF: observado = ilegible o sin capa de texto, espera revisión humana');
            $table->string('ruta_pdf')
                ->nullable()
                ->after('resultado_final')
                ->comment('Ruta del informe en storage, permite re-extraer sin volver a subir el archivo');
        });

        Schema::table('parametro_analisis', function (Blueprint $table) {
            $table->string('detectado_por')
                ->nullable()
                ->after('unidad')
                ->comment('Etiqueta con la que el extractor reconoció el parámetro en el PDF, null si fue cargado a mano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analisis_laboratorio', function (Blueprint $table) {
            $table->dropColumn(['resultado_sugerido', 'estado', 'ruta_pdf']);
        });

        Schema::table('parametro_analisis', function (Blueprint $table) {
            $table->dropColumn('detectado_por');
        });
    }
};

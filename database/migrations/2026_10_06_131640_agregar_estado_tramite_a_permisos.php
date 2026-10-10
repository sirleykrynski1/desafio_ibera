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
        Schema::table('permiso_vuelco', function (Blueprint $table) {
            $table->enum('estado_tramite', ['iniciado', 'pendiente_documentacion', 'en_evaluacion', 'resuelto'])
                ->default('pendiente_documentacion')
                ->after('estado')
                ->comment('Avance del expediente, independiente de la vigencia del permiso que guarda estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permiso_vuelco', function (Blueprint $table) {
            $table->dropColumn('estado_tramite');
        });
    }
};

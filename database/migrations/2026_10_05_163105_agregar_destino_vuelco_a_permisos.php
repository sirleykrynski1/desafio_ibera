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
            $table->enum('tipo_destino_vuelco', ['cursos_agua', 'laguna', 'conducto_pluvial', 'absorcion_suelo'])
                ->nullable()
                ->after('numero_expediente')
                ->comment('Destino fijado en este permiso, se conserva como histórico aunque el destino actual cambie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permiso_vuelco', function (Blueprint $table) {
            $table->dropColumn('tipo_destino_vuelco');
        });
    }
};

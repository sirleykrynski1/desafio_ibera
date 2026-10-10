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
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->string('cuit', 20)->nullable()->after('nombre')->comment('CUIT del responsable, formato 11 dígitos sin guiones');
            $table->string('ubicacion')->nullable()->after('cuit')->comment('Domicilio o referencia de ubicación del establecimiento');
            $table->enum('tipo_destino_vuelco', ['cursos_agua', 'laguna', 'conducto_pluvial', 'absorcion_suelo'])
                ->nullable()
                ->after('ubicacion')
                ->comment('Destino actual de vuelco, se actualiza al renovar el permiso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('establecimientos', function (Blueprint $table) {
            $table->dropColumn(['cuit', 'ubicacion', 'tipo_destino_vuelco']);
        });
    }
};

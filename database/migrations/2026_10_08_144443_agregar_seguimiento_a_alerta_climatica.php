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
        Schema::table('alerta_climatica', function (Blueprint $table): void {
            $table->string('clave_consulta', 64)->nullable()->unique();
            $table->date('periodo_hasta')->nullable();
            $table->timestamp('consultado_en')->nullable();
            $table->json('detalle')->nullable();
            $table->timestamp('revisada_en')->nullable();
            $table->foreignId('revisada_por')->nullable()->constrained('users')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerta_climatica', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('revisada_por');
            $table->dropUnique(['clave_consulta']);
            $table->dropColumn(['clave_consulta', 'periodo_hasta', 'consultado_en', 'detalle', 'revisada_en']);
        });
    }
};

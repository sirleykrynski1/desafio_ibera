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
        Schema::create('permiso_vuelco', function (Blueprint $table) {
            $table->id('permiso_vuelco_id');
            $table->foreignId('establecimiento_id')
                ->unique()
                ->constrained('establecimientos', 'id')
                ->cascadeOnDelete();
            $table->string('numero_expediente');
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['Activo', 'Vencido', 'Revocado'])->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permiso_vuelco');
    }
};

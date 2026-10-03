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
        Schema::create('establecimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->enum('rubro', ['hotel', 'gastronomico', 'comercio']);
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->unsignedInteger('capacidad_maxima');
            $table->unsignedInteger('capacidad_biodigestor');
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('establecimientos');
    }
};

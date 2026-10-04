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
        Schema::create('alerta_climatica', function (Blueprint $table) {
            $table->id('alerta_climatica_id');
            $table->foreignId('establecimiento_id')
                ->constrained('establecimientos', 'id')
                ->cascadeOnDelete();
            $table->date('fecha_evento');
            $table->string('tipo');
            $table->decimal('milimetros_lluvia', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerta_climatica');
    }
};

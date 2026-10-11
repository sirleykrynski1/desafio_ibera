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
        Schema::create('insignia_cumplimiento', function (Blueprint $table) {
            $table->id('insignia_cumplimiento_id');
            $table->foreignId('establecimiento_id')
                ->constrained('establecimientos', 'id')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->date('fecha_otorgamiento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insignia_cumplimiento');
    }
};

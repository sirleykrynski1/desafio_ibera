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
        Schema::create('limite_efluente', function (Blueprint $table) {
            $table->id('limite_efluente_id');
            $table->integer('item');
            $table->string('parametro');
            $table->string('unidad');
            $table->string('cursos_agua')->nullable();
            $table->string('laguna')->nullable();
            $table->string('conducto_pluvial')->nullable();
            $table->string('absorcion_suelo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('limite_efluente');
    }
};

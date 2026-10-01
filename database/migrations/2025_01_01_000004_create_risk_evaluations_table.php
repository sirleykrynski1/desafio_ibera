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
        Schema::create('risk_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')
                ->constrained('establishments')
                ->cascadeOnDelete();
            $table->enum('risk_level', ['verde', 'amarillo', 'rojo']);
            $table->decimal('rain_forecast_mm', 8, 2)->default(0);
            $table->timestamp('evaluation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_evaluations');
    }
};

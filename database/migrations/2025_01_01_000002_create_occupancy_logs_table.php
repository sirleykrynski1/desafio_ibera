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
        Schema::create('occupancy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')
                ->constrained('establishments')
                ->cascadeOnDelete();
            $table->unsignedInteger('current_guests');
            $table->date('date_reported');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupancy_logs');
    }
};

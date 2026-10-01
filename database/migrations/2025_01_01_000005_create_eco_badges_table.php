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
        Schema::create('eco_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('establishment_id')
                ->constrained('establishments')
                ->cascadeOnDelete();
            $table->string('badge_name');
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eco_badges');
    }
};

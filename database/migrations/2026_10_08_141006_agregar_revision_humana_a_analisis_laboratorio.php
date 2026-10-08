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
        Schema::table('analisis_laboratorio', function (Blueprint $table): void {
            $table->foreignId('revisado_por')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->text('observaciones_revision')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analisis_laboratorio', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('revisado_por');
            $table->dropColumn(['revisado_en', 'observaciones_revision']);
        });
    }
};

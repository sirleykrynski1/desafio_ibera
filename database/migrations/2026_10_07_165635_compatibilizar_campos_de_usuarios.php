<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['name', 'nombre', 'apellido', 'dni', 'direccion', 'telefono'] as $campo) {
            if (! Schema::hasColumn('users', $campo)) {
                Schema::table('users', function (Blueprint $table) use ($campo): void {
                    $table->string($campo)->nullable();
                });
            }
        }
        if (! Schema::hasColumn('users', 'rol')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->enum('rol', ['admin_gobierno', 'inspector', 'propietario'])->default('propietario');
            });
        }
        DB::table('users')->whereNull('nombre')->update(['nombre' => DB::raw('name')]);
        DB::table('users')->whereNull('name')->update(['name' => DB::raw('nombre')]);
    }

    public function down(): void
    {
        throw new RuntimeException('Revertir mediante una migración correctiva para conservar los datos de ambos esquemas.');
    }
};

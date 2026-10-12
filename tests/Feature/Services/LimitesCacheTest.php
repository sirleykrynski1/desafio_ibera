<?php

use App\Models\Establecimiento;
use App\Models\LimiteEfluente;
use App\Services\EvaluadorCumplimiento;
use Database\Seeders\LimiteEfluenteSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Estas pruebas necesitan commits reales: la transacción de RefreshDatabase
 * impediría recorrer la lectura compartida de caché. Solo se migra SQLite en memoria.
 */
function prepararBaseParaCache(): void
{
    expect(DB::connection()->getDriverName())->toBe('sqlite');
    expect(DB::connection()->getDatabaseName())->toBe(':memory:');
    test()->artisan('migrate', ['--force' => true, '--no-interaction' => true])->assertSuccessful();
}

function evaluarTemperaturaCache(string $destino = 'cursos_agua'): array
{
    return app(EvaluadorCumplimiento::class)->evaluar(
        Establecimiento::factory()->make(['tipo_destino_vuelco' => $destino]),
        ['Temperatura' => 36],
    );
}

test('reutiliza una lectura del catálogo entre evaluaciones de distintos destinos', function () {
    prepararBaseParaCache();
    LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => '35', 'laguna' => '50']);
    DB::enableQueryLog();

    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    expect(evaluarTemperaturaCache('laguna')['estado'])->toBe('cumple');
    $consultas = collect(DB::getQueryLog())->filter(fn (array $consulta): bool => str_contains($consulta['query'], 'limite_efluente'));

    expect($consultas)->toHaveCount(1);
});

test('renueva el catálogo al crear modificar y eliminar límites sin borrar otra caché', function () {
    prepararBaseParaCache();
    Cache::put('otra-funcion', 'conservar', 3600);
    expect(LimiteEfluente::catalogoParaEvaluacion())->toBeEmpty();

    $limite = LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => '35']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    $limite->update(['cursos_agua' => '50']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('cumple');
    $limite->delete();

    expect(LimiteEfluente::catalogoParaEvaluacion())->toBeEmpty();
    expect(Cache::get('otra-funcion'))->toBe('conservar');
});

test('vence en una hora incluso si un cambio masivo omite eventos del modelo', function () {
    prepararBaseParaCache();
    $this->freezeTime();
    LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => '35']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    LimiteEfluente::query()->update(['cursos_agua' => '50']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');

    $this->travel(61)->minutes();

    expect(evaluarTemperaturaCache()['estado'])->toBe('cumple');
});

test('una evaluación dentro de una transacción no publica límites que luego se revierten', function () {
    prepararBaseParaCache();
    $limite = LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => '35']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    DB::beginTransaction();
    try {
        $limite->update(['cursos_agua' => '50']);
        expect(evaluarTemperaturaCache()['estado'])->toBe('cumple');
    } finally {
        DB::rollBack();
    }

    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    $this->assertDatabaseHas('limite_efluente', ['cursos_agua' => '35']);
});

test('una actualización confirmada dentro de una transacción renueva la evaluación', function () {
    prepararBaseParaCache();
    $limite = LimiteEfluente::factory()->create(['parametro' => 'Temperatura', 'cursos_agua' => '35']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');

    DB::transaction(fn () => $limite->update(['cursos_agua' => '50']));

    expect(evaluarTemperaturaCache()['estado'])->toBe('cumple');
    $this->assertDatabaseHas('limite_efluente', ['cursos_agua' => '50']);
});

test('el seeder renueva límites aunque el llamador desactive los eventos', function () {
    prepararBaseParaCache();
    LimiteEfluente::factory()->create(['item' => 1, 'parametro' => 'Temperatura', 'cursos_agua' => '50']);
    expect(evaluarTemperaturaCache()['estado'])->toBe('cumple');

    LimiteEfluente::withoutEvents(fn () => $this->seed(LimiteEfluenteSeeder::class));

    expect(evaluarTemperaturaCache()['estado'])->toBe('incumple');
    $this->assertDatabaseCount('limite_efluente', 10);
});

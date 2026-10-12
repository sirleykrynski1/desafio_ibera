<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

class LimiteEfluente extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        $invalidar = function (LimiteEfluente $limite): void {
            $clave = $limite->claveCache();
            Cache::forget($clave);

            if ($limite->getConnection()->transactionLevel() > 0) {
                $limite->getConnection()->afterCommit(fn () => Cache::forget($clave));
            }
        };

        static::saved($invalidar);
        static::deleted($invalidar);
    }

    /**
     * Catálogo sin objetos serializados, compartido por todos los destinos durante una hora.
     * Dentro de una transacción se lee la BD para no publicar cambios sin confirmar.
     *
     * @return list<array{parametro: string, unidad: string, cursos_agua: string|null, laguna: string|null, conducto_pluvial: string|null, absorcion_suelo: string|null}>
     */
    public static function catalogoParaEvaluacion(): array
    {
        $modelo = new static;
        $leer = fn (): array => static::query()->get([
            'parametro', 'unidad', 'cursos_agua', 'laguna', 'conducto_pluvial', 'absorcion_suelo',
        ])->toArray();

        if ($modelo->getConnection()->transactionLevel() > 0) {
            return $leer();
        }

        return Cache::remember($modelo->claveCache(), 3600, $leer);
    }

    public static function olvidarCatalogo(): void
    {
        Cache::forget((new static)->claveCache());
    }

    private function claveCache(): string
    {
        $conexion = $this->getConnection();

        return 'limites-efluentes:v1:'.hash('sha256', implode('|', [
            $conexion->getName(), $conexion->getConfig('host'), $conexion->getDatabaseName(),
        ]));
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'limite_efluente';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'limite_efluente_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item',
        'parametro',
        'unidad',
        'cursos_agua',
        'laguna',
        'conducto_pluvial',
        'absorcion_suelo',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item' => 'integer',
        ];
    }

    /**
     * Análisis de laboratorio vinculados con este límite normativo.
     *
     * @return BelongsToMany<AnalisisLaboratorio, $this>
     */
    public function analisisLaboratorios(): BelongsToMany
    {
        return $this->belongsToMany(
            AnalisisLaboratorio::class,
            'analisis_laboratorio_limite_efluente',
            'limite_efluente_id',
            'analisis_laboratorio_id'
        )->withTimestamps();
    }
}

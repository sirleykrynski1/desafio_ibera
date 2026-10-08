<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalisisLaboratorio extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'analisis_laboratorio';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'analisis_laboratorio_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'establecimiento_id',
        'fecha_muestra',
        'laboratorio',
        'resultado_sugerido',
        'estado',
        'resultado_final',
        'ruta_pdf',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_muestra' => 'date',
            'revisado_en' => 'datetime',
        ];
    }

    /**
     * Usuario que registró el dictamen final.
     *
     * @return BelongsTo<User, $this>
     */
    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    /** @return BelongsTo<Establecimiento, $this> */
    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, 'establecimiento_id');
    }

    /**
     * Parámetros fisicoquímicos y biológicos analizados en esta muestra.
     *
     * @return HasMany<ParametroAnalisis, $this>
     */
    public function parametros(): HasMany
    {
        return $this->hasMany(
            ParametroAnalisis::class,
            'analisis_laboratorio_id',
            'analisis_laboratorio_id'
        );
    }

    /**
     * Límites de efluentes normativos asociados a la muestra para su contraste.
     *
     * @return BelongsToMany<LimiteEfluente, $this>
     */
    public function limitesEfluentes(): BelongsToMany
    {
        return $this->belongsToMany(
            LimiteEfluente::class,
            'analisis_laboratorio_limite_efluente',
            'analisis_laboratorio_id',
            'limite_efluente_id'
        )->withTimestamps();
    }
}

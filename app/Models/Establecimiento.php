<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Establecimiento extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'establecimientos';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nombre',
        'rubro',
        'latitud',
        'longitud',
        'capacidad_maxima',
        'capacidad_biodigestor',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'ocupacion_actual',
        'fecha_ultimo_desagote',
        'capacidad_fosa_litros',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:8',
            'longitud' => 'decimal:8',
            'capacidad_maxima' => 'integer',
            'capacidad_biodigestor' => 'integer',
        ];
    }

    /**
     * Propietario del establecimiento.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Registros de ocupación diaria/semanal.
     *
     * @return HasMany<Ocupacion, $this>
     */
    public function ocupaciones(): HasMany
    {
        return $this->hasMany(Ocupacion::class, 'establecimiento_id');
    }

    /**
     * Último registro de ocupación reportado.
     *
     * @return HasOne<Ocupacion, $this>
     */
    public function ultimaOcupacion(): HasOne
    {
        return $this->hasOne(Ocupacion::class, 'establecimiento_id')->latestOfMany('fecha_registro');
    }

    /**
     * Registros de mantenimiento y vaciado de fosa.
     *
     * @return HasMany<Mantenimiento, $this>
     */
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'establecimiento_id');
    }

    /**
     * Último mantenimiento registrado/aprobado.
     *
     * @return HasOne<Mantenimiento, $this>
     */
    public function ultimoMantenimiento(): HasOne
    {
        return $this->hasOne(Mantenimiento::class, 'establecimiento_id')->latestOfMany('fecha_mantenimiento');
    }

    /**
     * Evaluaciones del semáforo de riesgo ambiental.
     *
     * @return HasMany<Riesgo, $this>
     */
    public function riesgos(): HasMany
    {
        return $this->hasMany(Riesgo::class, 'establecimiento_id');
    }

    /**
     * Última evaluación de riesgo del establecimiento.
     *
     * @return HasOne<Riesgo, $this>
     */
    public function ultimoRiesgo(): HasOne
    {
        return $this->hasOne(Riesgo::class, 'establecimiento_id')->latestOfMany('fecha_evaluacion');
    }

    /**
     * Insignias ecológicas ganadas.
     *
     * @return HasMany<EmblemaEcologico, $this>
     */
    public function emblemasEcologicos(): HasMany
    {
        return $this->hasMany(EmblemaEcologico::class, 'establecimiento_id');
    }

    /**
     * Accessor: Ocupación actual de personas.
     */
    public function getOcupacionActualAttribute(): int
    {
        return $this->ultimaOcupacion?->numero_personas ?? 0;
    }

    /**
     * Accessor: Fecha del último desagote/mantenimiento.
     */
    public function getFechaUltimoDesagoteAttribute(): ?string
    {
        return $this->ultimoMantenimiento?->fecha_mantenimiento?->format('Y-m-d');
    }

    /**
     * Accessor: Capacidad del biodigestor en litros.
     */
    public function getCapacidadFosaLitrosAttribute(): int
    {
        return $this->capacidad_biodigestor ?? 0;
    }
}

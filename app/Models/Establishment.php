<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Establishment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'latitude',
        'longitude',
        'max_capacity',
        'biodigester_capacity_l',
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
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'max_capacity' => 'integer',
            'biodigester_capacity_l' => 'integer',
        ];
    }

    /**
     * Propietario del establecimiento.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Registros de ocupación diaria/semanal.
     *
     * @return HasMany<OccupancyLog, $this>
     */
    public function occupancyLogs(): HasMany
    {
        return $this->hasMany(OccupancyLog::class);
    }

    /**
     * Último registro de ocupación reportado.
     *
     * @return HasOne<OccupancyLog, $this>
     */
    public function latestOccupancyLog(): HasOne
    {
        return $this->hasOne(OccupancyLog::class)->latestOfMany('date_reported');
    }

    /**
     * Registros de mantenimiento y vaciado de fosa.
     *
     * @return HasMany<MaintenanceLog, $this>
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Último mantenimiento registrado/aprobado.
     *
     * @return HasOne<MaintenanceLog, $this>
     */
    public function latestMaintenanceLog(): HasOne
    {
        return $this->hasOne(MaintenanceLog::class)->latestOfMany('maintenance_date');
    }

    /**
     * Evaluaciones del semáforo de riesgo ambiental.
     *
     * @return HasMany<RiskEvaluation, $this>
     */
    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    /**
     * Última evaluación de riesgo del establecimiento.
     *
     * @return HasOne<RiskEvaluation, $this>
     */
    public function latestRiskEvaluation(): HasOne
    {
        return $this->hasOne(RiskEvaluation::class)->latestOfMany('evaluation_date');
    }

    /**
     * Insignias ecológicas ganadas.
     *
     * @return HasMany<EcoBadge, $this>
     */
    public function ecoBadges(): HasMany
    {
        return $this->hasMany(EcoBadge::class);
    }

    /**
     * Accessor: Ocupación actual de personas.
     */
    public function getOcupacionActualAttribute(): int
    {
        return $this->latestOccupancyLog?->current_guests ?? 0;
    }

    /**
     * Accessor: Fecha del último desagote/mantenimiento.
     */
    public function getFechaUltimoDesagoteAttribute(): ?string
    {
        return $this->latestMaintenanceLog?->maintenance_date?->format('Y-m-d');
    }

    /**
     * Accessor: Alias en español para la capacidad del biodigestor en litros.
     */
    public function getCapacidadFosaLitrosAttribute(): int
    {
        return $this->biodigester_capacity_l ?? 0;
    }
}

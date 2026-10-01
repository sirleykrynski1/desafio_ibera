<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * Registros de mantenimiento y vaciado de fosa.
     *
     * @return HasMany<MaintenanceLog, $this>
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
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
     * Insignias ecológicas ganadas.
     *
     * @return HasMany<EcoBadge, $this>
     */
    public function ecoBadges(): HasMany
    {
        return $this->hasMany(EcoBadge::class);
    }
}

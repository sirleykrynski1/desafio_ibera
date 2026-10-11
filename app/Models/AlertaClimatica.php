<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaClimatica extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'alerta_climatica';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'alerta_climatica_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'establecimiento_id',
        'fecha_evento',
        'tipo',
        'milimetros_lluvia',
        'clave_consulta',
        'periodo_hasta',
        'consultado_en',
        'detalle',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_evento' => 'date',
            'milimetros_lluvia' => 'decimal:2',
            'periodo_hasta' => 'date',
            'consultado_en' => 'datetime',
            'detalle' => 'array',
            'revisada_en' => 'datetime',
        ];
    }

    /**
     * Establecimiento al que afecta la alerta climática.
     *
     * @return BelongsTo<Establecimiento, $this>
     */
    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class, 'establecimiento_id');
    }
}

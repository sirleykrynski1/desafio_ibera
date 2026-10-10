<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametroAnalisis extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parametro_analisis';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'parametro_analisis_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'analisis_laboratorio_id',
        'nombre',
        'valor_medido',
        'unidad',
        'detectado_por',
    ];

    /**
     * Análisis de laboratorio al que pertenece la medición.
     *
     * @return BelongsTo<AnalisisLaboratorio, $this>
     */
    public function analisisLaboratorio(): BelongsTo
    {
        return $this->belongsTo(
            AnalisisLaboratorio::class,
            'analisis_laboratorio_id',
            'analisis_laboratorio_id'
        );
    }
}

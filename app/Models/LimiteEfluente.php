<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LimiteEfluente extends Model
{
    use HasFactory;

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

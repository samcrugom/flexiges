<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormulaComponente extends Model
{
    protected $table = 'formula_componentes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cantidad'    => 'decimal:4',
            'merma_pct'   => 'decimal:2',
            'es_opcional' => 'boolean',
        ];
    }

    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }

    /**
     * Cantidad real incluyendo merma
     */
    public function getCantidadConMerma(float $lotes = 1): float
    {
        $cantBase = (float)$this->cantidad * $lotes;
        return $cantBase * (1 + (float)$this->merma_pct / 100);
    }
}

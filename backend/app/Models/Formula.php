<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Formula extends Model
{
    protected $table = 'formulas';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'activa'              => 'boolean',
            'cantidad_producida'  => 'decimal:4',
        ];
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function almacenSalida()
    {
        return $this->belongsTo(Almacen::class, 'almacen_salida_id');
    }
    public function almacenEntrada()
    {
        return $this->belongsTo(Almacen::class, 'almacen_entrada_id');
    }
    public function componentes()
    {
        return $this->hasMany(FormulaComponente::class)->orderBy('orden');
    }
    public function ordenes()
    {
        return $this->hasMany(OrdenFabricacion::class);
    }

    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('activa', true);
    }
}

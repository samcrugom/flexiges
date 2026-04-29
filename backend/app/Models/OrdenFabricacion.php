<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrdenFabricacion extends Model
{
    protected $table = 'ordenes_fabricacion';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fecha'               => 'date',
            'fecha_prevista'      => 'date',
            'fecha_fin'           => 'date',
            'cantidad_pedida'     => 'decimal:4',
            'cantidad_fabricada'  => 'decimal:4',
            'coste_estimado'      => 'decimal:2',
            'coste_real'          => 'decimal:2',
        ];
    }

    const ESTADOS = ['borrador', 'confirmada', 'en_proceso', 'completada', 'anulada'];

    public function formula()
    {
        return $this->belongsTo(Formula::class);
    }
    public function almacenSalida()
    {
        return $this->belongsTo(Almacen::class, 'almacen_salida_id');
    }
    public function almacenEntrada()
    {
        return $this->belongsTo(Almacen::class, 'almacen_entrada_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function detalle()
    {
        return $this->hasMany(OrdenFabricacionDetalle::class, 'orden_id');
    }
    public function componentes()
    {
        return $this->detalle()->where('tipo', 'C');
    }
    public function productos()
    {
        return $this->detalle()->where('tipo', 'P');
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->whereIn('estado', ['confirmada', 'en_proceso']);
    }
    public function scopeActivas(Builder $q): Builder
    {
        return $q->where('estado', 'en_proceso');
    }

    public function isEditable(): bool
    {
        return in_array($this->estado, ['borrador', 'confirmada']);
    }

    public function isEjecutable(): bool
    {
        return in_array($this->estado, ['confirmada', 'en_proceso']);
    }
}

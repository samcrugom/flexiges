<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cobro extends Model
{
    protected $table = 'cobros';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'importe_total' => 'decimal:2',
            'importe_cobrado' => 'decimal:2'
        ];
    }
    public function factura()
    {
        return $this->belongsTo(VentaDocumento::class, 'factura_id');
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function remesa()
    {
        return $this->belongsTo(Remesa::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function movimientos()
    {
        return $this->hasMany(CobroMovimiento::class);
    }

    public function getImportePendienteAttribute(): float
    {
        return round((float)$this->importe_total - (float)$this->importe_cobrado, 2);
    }
    public function getVencidoAttribute(): bool
    {
        return $this->fecha_vencimiento->isPast() && in_array($this->estado, ['pendiente', 'parcial']);
    }
    public function scopePendientes(Builder $q): Builder
    {
        return $q->whereIn('estado', ['pendiente', 'parcial']);
    }
    public function scopeVencidos(Builder $q): Builder
    {
        return $q->pendientes()->where('fecha_vencimiento', '<', today());
    }
}

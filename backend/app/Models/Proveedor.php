<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'activo'        => 'boolean',
            'descuento_pct' => 'decimal:2',
        ];
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function tipoIva()
    {
        return $this->belongsTo(TipoIva::class);
    }
    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
    public function pedidos()
    {
        return $this->hasMany(PedidoCompra::class);
    }
    public function albaranes()
    {
        return $this->hasMany(AlbaranCompra::class);
    }
    public function facturas()
    {
        return $this->hasMany(FacturaCompra::class);
    }

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }
    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where('nombre', 'ilike', "%{$term}%")
            ->orWhere('codigo', 'ilike', "%{$term}%")
            ->orWhere('nif', 'ilike', "%{$term}%");
    }
}

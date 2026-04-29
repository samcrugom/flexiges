<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    protected $table = 'pedidos_compra';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['fecha' => 'date', 'fecha_prevista' => 'date', 'base_imponible' => 'decimal:2', 'cuota_iva' => 'decimal:2', 'total' => 'decimal:2'];
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function lineas()
    {
        return $this->hasMany(PedidoCompraLinea::class, 'pedido_id')->orderBy('linea');
    }
    public function albaranes()
    {
        return $this->hasMany(AlbaranCompra::class, 'pedido_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbaranCompra extends Model
{
    protected $table = 'albaranes_compra';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['fecha' => 'date', 'base_imponible' => 'decimal:2', 'cuota_iva' => 'decimal:2', 'total' => 'decimal:2'];
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function pedido()
    {
        return $this->belongsTo(PedidoCompra::class);
    }
    public function factura()
    {
        return $this->belongsTo(FacturaCompra::class, 'factura_compra_id');
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function lineas()
    {
        return $this->hasMany(AlbaranCompraLinea::class, 'albaran_id')->orderBy('linea');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

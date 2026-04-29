<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoCompraLinea extends Model
{
    protected $table = 'pedidos_compra_lineas';
    protected $guarded = ['id'];
    public function pedido()
    {
        return $this->belongsTo(PedidoCompra::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function tipoIva()
    {
        return $this->belongsTo(TipoIva::class);
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function getPendienteAttribute(): float
    {
        return max(0, (float)$this->cantidad - (float)$this->cantidad_recibida);
    }
}

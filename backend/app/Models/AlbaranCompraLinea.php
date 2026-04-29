<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlbaranCompraLinea extends Model
{
    protected $table = 'albaranes_compra_lineas';
    protected $guarded = ['id'];
    public function albaran()
    {
        return $this->belongsTo(AlbaranCompra::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function tipoIva()
    {
        return $this->belongsTo(TipoIva::class);
    }
    public function movimiento()
    {
        return $this->belongsTo(MovimientoAlmacen::class);
    }
}

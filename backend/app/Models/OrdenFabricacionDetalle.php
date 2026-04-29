<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenFabricacionDetalle extends Model
{
    protected $table = 'ordenes_fabricacion_detalle';
    public $timestamps = false;
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'cantidad_teorica' => 'decimal:4',
            'cantidad_real'    => 'decimal:4',
            'precio_coste'     => 'decimal:4',
            'created_at'       => 'datetime',
        ];
    }

    public function orden()
    {
        return $this->belongsTo(OrdenFabricacion::class, 'orden_id');
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function movimiento()
    {
        return $this->belongsTo(MovimientoAlmacen::class);
    }
}

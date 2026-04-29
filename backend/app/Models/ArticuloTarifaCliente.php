<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticuloTarifaCliente extends Model
{
    protected $table = 'articulos_tarifas_cliente';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['precio' => 'decimal:4', 'descuento_pct' => 'decimal:2', 'fecha_desde' => 'date', 'fecha_hasta' => 'date'];
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

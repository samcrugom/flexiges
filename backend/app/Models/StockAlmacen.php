<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlmacen extends Model
{
    public $timestamps = false;
    protected $table = 'stock_almacen';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stock'          => 'decimal:4',
            'stock_reservado' => 'decimal:4',
            'stock_pedido'   => 'decimal:4',
            'updated_at'     => 'datetime',
        ];
    }

    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function getStockDisponibleAttribute(): float
    {
        return (float)$this->stock - (float)$this->stock_reservado;
    }
}

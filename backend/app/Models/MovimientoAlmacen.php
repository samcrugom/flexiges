<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoAlmacen extends Model
{
    public $timestamps = false;
    protected $table = 'movimientos_almacen';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fecha'           => 'date',
            'cantidad'        => 'decimal:4',
            'stock_anterior'  => 'decimal:4',
            'stock_posterior' => 'decimal:4',
            'precio_coste'    => 'decimal:4',
            'created_at'      => 'datetime',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

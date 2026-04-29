<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    protected $table = 'almacenes';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'es_principal' => 'boolean'];
    }
    public function stocks()     { return $this->hasMany(StockAlmacen::class); }
    public function movimientos(){ return $this->hasMany(MovimientoAlmacen::class); }
}

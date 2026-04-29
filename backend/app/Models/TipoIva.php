<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoIva extends Model
{
    protected $table = 'tipos_iva';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'activo'         => 'boolean',
            'porcentaje_iva' => 'decimal:2',
            'porcentaje_rec' => 'decimal:2',
        ];
    }
    public function articulos()
    {
        return $this->hasMany(Articulo::class);
    }
}

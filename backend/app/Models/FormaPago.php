<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPago extends Model
{
    protected $table = 'formas_pago';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
    public function proveedores()
    {
        return $this->hasMany(Proveedor::class);
    }
}

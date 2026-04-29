<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';
    protected $guarded = ['id'];
    public function vendedores()
    {
        return $this->hasMany(Vendedor::class);
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}

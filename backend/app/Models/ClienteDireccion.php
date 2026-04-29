<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClienteDireccion extends Model
{
    protected $table = 'clientes_direcciones';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['es_principal' => 'boolean'];
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}

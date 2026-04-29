<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    protected $table = 'vendedores';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'comision_pct' => 'decimal:2'];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
    public function documentos()
    {
        return $this->hasMany(VentaDocumento::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Remesa extends Model
{
    protected $table = 'remesas';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['fecha' => 'date', 'fecha_presentacion' => 'date', 'importe_total' => 'decimal:2'];
    }
    public function cobros()
    {
        return $this->hasMany(Cobro::class);
    }
}

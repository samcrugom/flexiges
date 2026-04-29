<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CobroMovimiento extends Model
{
    protected $table = 'cobros_movimientos';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['fecha' => 'date', 'importe' => 'decimal:2', 'created_at' => 'datetime'];
    }
    public function cobro()
    {
        return $this->belongsTo(Cobro::class);
    }
}

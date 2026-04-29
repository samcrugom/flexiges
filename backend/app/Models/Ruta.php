<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $table = 'rutas';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}

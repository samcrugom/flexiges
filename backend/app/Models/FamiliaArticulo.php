<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamiliaArticulo extends Model
{
    protected $table='familias_articulo';
    protected $guarded=['id'];
    
    public function parent()
    {
        return $this->belongsTo(FamiliaArticulo::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(FamiliaArticulo::class, 'parent_id');
    }

    public function articulos()
    {
        return $this->hasMany(Articulo::class, 'familia_id');
    }
}
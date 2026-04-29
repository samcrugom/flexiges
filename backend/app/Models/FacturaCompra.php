<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacturaCompra extends Model
{
    protected $table = 'facturas_compra';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['fecha' => 'date', 'fecha_contable' => 'date', 'base_imponible1' => 'decimal:2', 'cuota_iva1' => 'decimal:2', 'total' => 'decimal:2'];
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function albaranes()
    {
        return $this->hasMany(AlbaranCompra::class, 'factura_compra_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

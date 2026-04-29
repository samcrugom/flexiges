<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaIvaDetalle extends Model
{
    protected $table = 'ventas_iva_detalle';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['base_imponible' => 'decimal:2', 'porcentaje_iva' => 'decimal:2', 'cuota_iva' => 'decimal:2', 'porcentaje_rec' => 'decimal:2', 'cuota_recargo' => 'decimal:2'];
    }
    public function documento()
    {
        return $this->belongsTo(VentaDocumento::class);
    }
    public function tipoIva()
    {
        return $this->belongsTo(TipoIva::class);
    }
}

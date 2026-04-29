<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaLinea extends Model
{
    protected $table = 'ventas_lineas';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'precio_unitario' => 'decimal:4',
            'precio_con_iva' => 'decimal:4',
            'descuento_pct' => 'decimal:2',
            'importe_neto' => 'decimal:2',
            'cuota_iva' => 'decimal:2',
            'cuota_recargo' => 'decimal:2'
        ];
    }
    public function documento()
    {
        return $this->belongsTo(VentaDocumento::class);
    }
    public function articulo()
    {
        return $this->belongsTo(Articulo::class);
    }
    public function tipoIva()
    {
        return $this->belongsTo(TipoIva::class);
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function movimiento()
    {
        return $this->belongsTo(MovimientoAlmacen::class);
    }
    public function getImporteBrutoAttribute(): float
    {
        return round((float)$this->cantidad * (float)$this->precio_unitario, 2);
    }
    public function getDescuentoImporteAttribute(): float
    {
        return round($this->importe_bruto * (float)$this->descuento_pct / 100, 2);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['num_factura' => 'integer', 'num_albaran' => 'integer', 'num_presup' => 'integer', 'num_pedido' => 'integer', 'num_pedido_compra' => 'integer'];
    }
    public static function actual(): self
    {
        return static::firstOrFail();
    }
    public function nextNumero(string $tipo): string
    {
        $map = [
            'factura' => ['serie_factura', 'num_factura'],
            'albaran' => ['serie_albaran', 'num_albaran'],
            'presupuesto' => ['serie_presup', 'num_presup'],
            'pedido' => ['serie_pedido', 'num_pedido'],
            'pedido_compra' => ['serie_pedido', 'num_pedido_compra']
        ];
        [$serieField, $numField] = $map[$tipo];
        $numero = $this->$serieField . str_pad($this->$numField, 6, '0', STR_PAD_LEFT);
        $this->increment($numField);
        return $numero;
    }
}

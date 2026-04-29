<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Articulo extends Model
{
    protected $table = 'articulos';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'activo'         => 'boolean',
            'es_fabricado'   => 'boolean',
            'precio_coste'   => 'decimal:4',
            'tarifa1'        => 'decimal:4',
            'tarifa2'        => 'decimal:4',
            'tarifa3'        => 'decimal:4',
            'tarifa4'        => 'decimal:4',
            'stock_total'    => 'decimal:4',
            'stock_minimo'   => 'decimal:4',
        ];
    }

    public function familia()     { return $this->belongsTo(FamiliaArticulo::class); }
    public function proveedor()   { return $this->belongsTo(Proveedor::class); }
    public function tipoIva()     { return $this->belongsTo(TipoIva::class); }
    public function stockAlmacen(){ return $this->hasMany(StockAlmacen::class); }
    public function movimientos() { return $this->hasMany(MovimientoAlmacen::class); }
    public function tarifasCliente(){ return $this->hasMany(ArticuloTarifaCliente::class); }
    public function formulas()    { return $this->hasMany(Formula::class); }
    public function formulaComponentes(){ return $this->hasMany(FormulaComponente::class); }

    public function getPrecioParaCliente(Cliente $cliente): float
    {
        // 1. Tarifa especial concertada
        $especial = $this->tarifasCliente()
            ->where('cliente_id', $cliente->id)
            ->where(function ($q) {
                $q->whereNull('fecha_hasta')->orWhere('fecha_hasta', '>=', today());
            })
            ->where(function ($q) {
                $q->whereNull('fecha_desde')->orWhere('fecha_desde', '<=', today());
            })
            ->first();

        if ($especial) return (float) $especial->precio;

        // 2. Tarifa del cliente (1-4)
        $field = 'tarifa' . $cliente->tarifa;
        return (float) ($this->$field ?? $this->tarifa1);
    }

    public function scopeActivos(Builder $q): Builder { return $q->where('activo', true); }
    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($sub) use ($term) {
            $sub->where('descripcion', 'ilike', "%{$term}%")
                ->orWhere('referencia', 'ilike', "%{$term}%")
                ->orWhere('ean13', $term);
        });
    }
    public function scopeBajoMinimo(Builder $q): Builder
    {
        return $q->whereColumn('stock_total', '<', 'stock_minimo');
    }
}

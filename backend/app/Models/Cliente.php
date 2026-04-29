<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'activo'          => 'boolean',
            'credito_maximo'  => 'decimal:2',
            'riesgo_actual'   => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'descuento_pct'   => 'decimal:2',
            'descuento2_pct'  => 'decimal:2',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
    public function zona()
    {
        return $this->belongsTo(Zona::class);
    }
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
    public function delegacion()
    {
        return $this->belongsTo(Delegacion::class);
    }
    public function direcciones()
    {
        return $this->hasMany(ClienteDireccion::class);
    }
    public function documentos()
    {
        return $this->hasMany(VentaDocumento::class);
    }
    public function facturas()
    {
        return $this->hasMany(VentaDocumento::class)->where('tipo', 'factura');
    }
    public function cobros()
    {
        return $this->hasMany(Cobro::class);
    }
    public function tarifasEspeciales()
    {
        return $this->hasMany(ArticuloTarifaCliente::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }
    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($sub) use ($term) {
            $sub->where('nombre', 'ilike', "%{$term}%")
                ->orWhere('codigo', 'ilike', "%{$term}%")
                ->orWhere('nif', 'ilike', "%{$term}%")
                ->orWhere('localidad', 'ilike', "%{$term}%");
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function getTarifaField(): string
    {
        return 'tarifa' . $this->tarifa;
    }

    public function getTipoLabel(): string
    {
        return match ($this->tipo_cliente) {
            'R' => 'Recargo de Equivalencia',
            'E' => 'Exento IVA',
            default => 'Normal',
        };
    }

    public function hasRecargoEquivalencia(): bool
    {
        return $this->tipo_cliente === 'R';
    }
}

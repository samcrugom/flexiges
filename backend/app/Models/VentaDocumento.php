<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VentaDocumento extends Model
{
    protected $table = 'ventas_documentos';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fecha'            => 'date',
            'fecha_entrega'    => 'date',
            'fecha_vencimiento' => 'date',
            'base_imponible'   => 'decimal:2',
            'cuota_iva'        => 'decimal:2',
            'cuota_recargo'    => 'decimal:2',
            'total_descuento'  => 'decimal:2',
            'total_factura'    => 'decimal:2',
        ];
    }

    const TIPOS   = ['presupuesto', 'pedido', 'albaran', 'factura', 'rectificativa'];
    const ESTADOS = ['borrador', 'confirmado', 'enviado', 'facturado', 'cobrado', 'anulado', 'parcial'];

    // ── Relationships ─────────────────────────────────────────────────────────
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
    public function formaPago()
    {
        return $this->belongsTo(FormaPago::class);
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }
    public function delegacion()
    {
        return $this->belongsTo(Delegacion::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function lineas()
    {
        return $this->hasMany(VentaLinea::class, 'documento_id')->orderBy('linea');
    }
    public function ivaDetalle()
    {
        return $this->hasMany(VentaIvaDetalle::class, 'documento_id');
    }
    public function cobros()
    {
        return $this->hasMany(Cobro::class, 'factura_id');
    }
    public function docOrigen()
    {
        return $this->belongsTo(VentaDocumento::class, 'doc_origen_id');
    }
    public function docRectifica()
    {
        return $this->belongsTo(VentaDocumento::class, 'doc_rectifica_id');
    }
    public function docsDerivados()
    {
        return $this->hasMany(VentaDocumento::class, 'doc_origen_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeTipo(Builder $q, string $tipo): Builder
    {
        return $q->where('tipo', $tipo);
    }
    public function scopeFacturas(Builder $q): Builder
    {
        return $q->where('tipo', 'factura');
    }
    public function scopeAlbaranes(Builder $q): Builder
    {
        return $q->where('tipo', 'albaran');
    }
    public function scopeSearch(Builder $q, string $term): Builder
    {
        return $q->where(function ($sub) use ($term) {
            $sub->where('numero', 'ilike', "%{$term}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$term}%")
                ->orWhere('cliente_nif', 'ilike', "%{$term}%")
                ->orWhere('referencia_cliente', 'ilike', "%{$term}%");
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function isEditable(): bool
    {
        return in_array($this->estado, ['borrador', 'confirmado']);
    }

    public function isAnulable(): bool
    {
        return !in_array($this->estado, ['anulado', 'cobrado']);
    }
}

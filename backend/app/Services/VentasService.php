<?php

namespace App\Services;

use App\Models\{VentaDocumento, VentaLinea, VentaIvaDetalle, Cobro, Cliente, Almacen, StockAlmacen, MovimientoAlmacen, Empresa, FormaPago};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentasService
{
    public function __construct(private readonly AlmacenService $almacenService) {}

    // ── Crear documento (presupuesto/pedido/albarán/factura) ──────────────────
    public function crear(array $data): VentaDocumento
    {
        return DB::transaction(function () use ($data) {
            $cliente  = Cliente::findOrFail($data['cliente_id']);
            $empresa  = Empresa::actual();
            $numero   = $empresa->nextNumero($data['tipo'] === 'albaran' ? 'albaran' : ($data['tipo'] === 'presupuesto' ? 'presupuesto' : ($data['tipo'] === 'pedido' ? 'pedido' : 'factura')));
            $almacen  = isset($data['almacen_id']) ? Almacen::find($data['almacen_id']) : Almacen::where('es_principal',true)->first();

            // Calcular totales
            $totales = $this->calcularTotales($data['lineas'], $cliente->tipo_cliente);

            $doc = VentaDocumento::create([
                'tipo'               => $data['tipo'],
                'numero'             => $numero,
                'serie'              => $this->getSerie($data['tipo'], $empresa),
                'fecha'              => $data['fecha'] ?? today(),
                'fecha_entrega'      => $data['fecha_entrega'] ?? null,
                'cliente_id'         => $cliente->id,
                'cliente_nombre'     => $cliente->nombre,
                'cliente_nif'        => $cliente->nif,
                'cliente_direccion'  => implode(', ', array_filter([$cliente->direccion, $cliente->codigo_postal, $cliente->localidad, $cliente->provincia])),
                'vendedor_id'        => $data['vendedor_id'] ?? $cliente->vendedor_id,
                'forma_pago_id'      => $data['forma_pago_id'] ?? $cliente->forma_pago_id,
                'almacen_id'         => $almacen?->id,
                'ruta_id'            => $data['ruta_id'] ?? $cliente->ruta_id,
                'delegacion_id'      => $data['delegacion_id'] ?? $cliente->delegacion_id,
                'doc_origen_id'      => $data['doc_origen_id'] ?? null,
                'base_imponible'     => $totales['base'],
                'cuota_iva'          => $totales['iva'],
                'cuota_recargo'      => $totales['recargo'],
                'total_descuento'    => $totales['descuento'],
                'total_factura'      => $totales['total'],
                'tipo_cliente'       => $cliente->tipo_cliente,
                'estado'             => $data['tipo'] === 'factura' ? 'enviada' : 'confirmado',
                'referencia_cliente' => $data['referencia_cliente'] ?? null,
                'notas'              => $data['notas'] ?? null,
                'notas_internas'     => $data['notas_internas'] ?? null,
                'user_id'            => auth()->id(),
            ]);

            // Calcular fecha vencimiento para facturas
            if ($data['tipo'] === 'factura') {
                $fp = FormaPago::find($data['forma_pago_id'] ?? $cliente->forma_pago_id);
                $doc->update(['fecha_vencimiento' => $doc->fecha->addDays($fp?->dias_vencimiento ?? 30)]);
            }

            // Crear líneas
            foreach ($data['lineas'] as $i => $linea) {
                $this->crearLinea($doc, $linea, $i + 1, $cliente->tipo_cliente);
            }

            // Crear desglose IVA
            $this->crearDesgloseIva($doc, $data['lineas'], $cliente->tipo_cliente);

            // Si es factura o albarán → mover stock
            if (in_array($data['tipo'], ['factura', 'albaran']) && ($data['mover_stock'] ?? true)) {
                $this->moverStockSalida($doc);
            }

            // Si es factura → crear cobro pendiente
            if ($data['tipo'] === 'factura') {
                $this->crearCobro($doc, $cliente);
            }

            return $doc->load(['cliente', 'lineas.articulo', 'lineas.tipoIva', 'ivaDetalle', 'cobros', 'vendedor', 'formaPago']);
        });
    }

    // ── Convertir albarán(es) a factura ──────────────────────────────────────
    public function facturarAlbaranes(array $albanIds, array $extra = []): VentaDocumento
    {
        return DB::transaction(function () use ($albanIds, $extra) {
            $albaranes = VentaDocumento::whereIn('id', $albanIds)
                ->where('tipo', 'albaran')
                ->whereNotIn('estado', ['facturado', 'anulado'])
                ->with(['lineas.articulo.tipoIva', 'cliente'])
                ->get();

            if ($albaranes->isEmpty()) {
                throw ValidationException::withMessages(['albaranes' => 'No hay albaranes válidos.']);
            }

            $cliente = $albaranes->first()->cliente;

            // Agregar líneas de todos los albaranes
            $lineas = [];
            foreach ($albaranes as $alb) {
                foreach ($alb->lineas as $l) {
                    $lineas[] = [
                        'articulo_id'    => $l->articulo_id,
                        'descripcion'    => $l->descripcion,
                        'cantidad'       => $l->cantidad,
                        'precio_unitario'=> $l->precio_unitario,
                        'descuento_pct'  => $l->descuento_pct,
                        'tipo_iva_id'    => $l->tipo_iva_id,
                        'almacen_id'     => $l->almacen_id,
                    ];
                }
            }

            $factura = $this->crear(array_merge([
                'tipo'              => 'factura',
                'cliente_id'        => $cliente->id,
                'lineas'            => $lineas,
                'mover_stock'       => false, // stock ya movido al crear albaranes
                'doc_origen_id'     => $albaranes->first()->id,
            ], $extra));

            // Marcar albaranes como facturados
            VentaDocumento::whereIn('id', $albanIds)->update(['estado' => 'facturado']);

            return $factura;
        });
    }

    // ── Anular documento ──────────────────────────────────────────────────────
    public function anular(VentaDocumento $doc): VentaDocumento
    {
        if (!$doc->isAnulable()) {
            throw ValidationException::withMessages(['estado' => "No se puede anular un documento en estado '{$doc->estado}'."]);
        }

        return DB::transaction(function () use ($doc) {
            // Revertir stock si era factura o albarán
            if (in_array($doc->tipo, ['factura', 'albaran'])) {
                $this->revertirStockSalida($doc);
            }

            // Anular cobros asociados
            $doc->cobros()->update(['estado' => 'anulado']);

            $doc->update(['estado' => 'anulado']);
            return $doc;
        });
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────
    private function crearLinea(VentaDocumento $doc, array $l, int $orden, string $tipoCliente): VentaLinea
    {
        $bruto   = round((float)$l['cantidad'] * (float)$l['precio_unitario'], 4);
        $descImp = round($bruto * ((float)($l['descuento_pct'] ?? 0) / 100), 4);
        $neto    = round($bruto - $descImp, 2);

        $tipoIva = \App\Models\TipoIva::find($l['tipo_iva_id']);
        $pctIva  = (float)($tipoIva?->porcentaje_iva ?? 21);
        $pctRec  = $tipoCliente === 'R' ? (float)($tipoIva?->porcentaje_rec ?? 0) : 0;

        $cuotaIva = round($neto * $pctIva / 100, 2);
        $cuotaRec = round($neto * $pctRec / 100, 2);

        return VentaLinea::create([
            'documento_id'    => $doc->id,
            'linea'           => $orden,
            'tipo_linea'      => $l['tipo_linea'] ?? 'A',
            'articulo_id'     => $l['articulo_id'] ?? null,
            'descripcion'     => $l['descripcion'],
            'cantidad'        => $l['cantidad'],
            'precio_unitario' => $l['precio_unitario'],
            'precio_con_iva'  => round($l['precio_unitario'] * (1 + $pctIva / 100), 4),
            'descuento_pct'   => $l['descuento_pct'] ?? 0,
            'importe_neto'    => $neto,
            'tipo_iva_id'     => $l['tipo_iva_id'],
            'cuota_iva'       => $cuotaIva,
            'cuota_recargo'   => $cuotaRec,
            'almacen_id'      => $l['almacen_id'] ?? $doc->almacen_id,
        ]);
    }

    private function calcularTotales(array $lineas, string $tipoCliente): array
    {
        $base = $iva = $recargo = $descuento = 0;
        foreach ($lineas as $l) {
            $bruto   = (float)$l['cantidad'] * (float)$l['precio_unitario'];
            $desc    = $bruto * ((float)($l['descuento_pct'] ?? 0) / 100);
            $neto    = $bruto - $desc;
            $tipoIva = \App\Models\TipoIva::find($l['tipo_iva_id']);
            $pctIva  = (float)($tipoIva?->porcentaje_iva ?? 21);
            $pctRec  = $tipoCliente === 'R' ? (float)($tipoIva?->porcentaje_rec ?? 0) : 0;
            $base     += $neto;
            $iva      += $neto * $pctIva / 100;
            $recargo  += $neto * $pctRec / 100;
            $descuento+= $desc;
        }
        return [
            'base'     => round($base, 2),
            'iva'      => round($iva, 2),
            'recargo'  => round($recargo, 2),
            'descuento'=> round($descuento, 2),
            'total'    => round($base + $iva + $recargo, 2),
        ];
    }

    private function crearDesgloseIva(VentaDocumento $doc, array $lineas, string $tipoCliente): void
    {
        $grupos = [];
        foreach ($lineas as $l) {
            $ivaId   = $l['tipo_iva_id'];
            $tipoIva = \App\Models\TipoIva::find($ivaId);
            $bruto   = (float)$l['cantidad'] * (float)$l['precio_unitario'];
            $desc    = $bruto * ((float)($l['descuento_pct'] ?? 0) / 100);
            $neto    = $bruto - $desc;
            $pctIva  = (float)($tipoIva?->porcentaje_iva ?? 21);
            $pctRec  = $tipoCliente === 'R' ? (float)($tipoIva?->porcentaje_rec ?? 0) : 0;

            if (!isset($grupos[$ivaId])) {
                $grupos[$ivaId] = ['base'=>0,'iva'=>0,'rec'=>0,'pct_iva'=>$pctIva,'pct_rec'=>$pctRec];
            }
            $grupos[$ivaId]['base'] += $neto;
            $grupos[$ivaId]['iva']  += $neto * $pctIva / 100;
            $grupos[$ivaId]['rec']  += $neto * $pctRec / 100;
        }

        foreach ($grupos as $ivaId => $g) {
            VentaIvaDetalle::create([
                'documento_id'   => $doc->id,
                'tipo_iva_id'    => $ivaId,
                'base_imponible' => round($g['base'], 2),
                'porcentaje_iva' => $g['pct_iva'],
                'cuota_iva'      => round($g['iva'], 2),
                'porcentaje_rec' => $g['pct_rec'],
                'cuota_recargo'  => round($g['rec'], 2),
            ]);
        }
    }

    private function moverStockSalida(VentaDocumento $doc): void
    {
        foreach ($doc->lineas()->where('tipo_linea', 'A')->with('articulo')->get() as $linea) {
            if (!$linea->articulo_id) continue;
            $almacenId    = $linea->almacen_id ?? $doc->almacen_id;
            $stockAntes   = $this->almacenService->getStock($linea->articulo_id, $almacenId);
            $stockDespues = $stockAntes - (float)$linea->cantidad;

            $mov = MovimientoAlmacen::create([
                'fecha'           => $doc->fecha,
                'articulo_id'     => $linea->articulo_id,
                'almacen_id'      => $almacenId,
                'tipo_movimiento' => 'SALIDA',
                'cantidad'        => -(float)$linea->cantidad,
                'stock_anterior'  => $stockAntes,
                'stock_posterior' => $stockDespues,
                'precio_coste'    => $linea->articulo->precio_coste,
                'documento_tipo'  => strtoupper($doc->tipo).'_VTA',
                'documento_id'    => $doc->id,
                'documento_ref'   => $doc->numero,
                'user_id'         => auth()->id(),
                'created_at'      => now(),
            ]);

            $linea->update(['movimiento_id' => $mov->id]);
            $this->almacenService->updateStock($linea->articulo_id, $almacenId, -(float)$linea->cantidad);
        }
    }

    private function revertirStockSalida(VentaDocumento $doc): void
    {
        foreach ($doc->lineas()->where('tipo_linea', 'A')->with('articulo')->get() as $linea) {
            if (!$linea->articulo_id || !$linea->movimiento_id) continue;
            $almacenId = $linea->almacen_id ?? $doc->almacen_id;
            $this->almacenService->updateStock($linea->articulo_id, $almacenId, (float)$linea->cantidad);
        }
    }

    private function crearCobro(VentaDocumento $doc, Cliente $cliente): Cobro
    {
        $fp = FormaPago::find($doc->forma_pago_id);
        $num = 'COB-' . str_pad(Cobro::count() + 1, 6, '0', STR_PAD_LEFT);

        return Cobro::create([
            'factura_id'        => $doc->id,
            'cliente_id'        => $cliente->id,
            'numero'            => $num,
            'fecha_emision'     => $doc->fecha,
            'fecha_vencimiento' => $doc->fecha_vencimiento ?? $doc->fecha->addDays($fp?->dias_vencimiento ?? 30),
            'importe_total'     => $doc->total_factura,
            'importe_cobrado'   => 0,
            'tipo_cobro'        => $fp?->tipo ?? 'transferencia',
            'estado'            => 'pendiente',
            'entidad_bancaria'  => null,
            'cuenta_bancaria'   => $cliente->iban,
            'user_id'           => auth()->id(),
        ]);
    }

    private function getSerie(string $tipo, Empresa $empresa): string
    {
        return match($tipo) {
            'albaran'      => $empresa->serie_albaran,
            'presupuesto'  => $empresa->serie_presup,
            'pedido'       => $empresa->serie_pedido,
            default        => $empresa->serie_factura,
        };
    }
}

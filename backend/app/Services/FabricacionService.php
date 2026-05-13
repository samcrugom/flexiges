<?php

namespace App\Services;

use App\Models\{Formula, OrdenFabricacion, OrdenFabricacionDetalle, StockAlmacen, MovimientoAlmacen, Empresa, Articulo};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FabricacionService
{
    // ── Verificar stock suficiente para fabricar ───────────────────────────────
    public function verificarStock(Formula $formula, float $lotes): array
    {
        $resultado = [];
        $almacenId = $formula->almacen_salida_id;

        foreach ($formula->componentes()->with('articulo')->get() as $comp) {
            $necesario = $comp->getCantidadConMerma($lotes);
            $stock = StockAlmacen::where('articulo_id', $comp->articulo_id)
                ->where('almacen_id', $almacenId)
                ->value('stock') ?? 0;

            $resultado[] = [
                'articulo_id'   => $comp->articulo_id,
                'referencia'    => $comp->articulo->referencia,
                'descripcion'   => $comp->articulo->descripcion,
                'unidad'        => $comp->unidad ?? $comp->articulo->unidad_medida,
                'necesario'     => $necesario,
                'disponible'    => (float)$stock,
                'suficiente'    => (float)$stock >= $necesario,
                'diferencia'    => (float)$stock - $necesario,
            ];
        }

        return $resultado;
    }

    // ── Crear orden de fabricación ─────────────────────────────────────────────
    public function crearOrden(Formula $formula, float $lotes, array $datos = []): OrdenFabricacion
    {
        return DB::transaction(function () use ($formula, $lotes, $datos) {
            $empresa = Empresa::actual();
            $numero  = 'OF-' . date('Y') . '-' . str_pad(
                OrdenFabricacion::whereYear('created_at', date('Y'))->count() + 1, 4, '0', STR_PAD_LEFT
            );

            $costeEstimado = $this->calcularCosteEstimado($formula, $lotes);

            $orden = OrdenFabricacion::create([
                'numero'             => $numero,
                'formula_id'         => $formula->id,
                'fecha'              => $datos['fecha'] ?? today(),
                'fecha_prevista'     => $datos['fecha_prevista'] ?? null,
                'cantidad_pedida'    => $lotes,
                'cantidad_fabricada' => 0,
                'estado'             => 'confirmada',
                'almacen_salida_id'  => $datos['almacen_salida_id']  ?? $formula->almacen_salida_id,
                'almacen_entrada_id' => $datos['almacen_entrada_id'] ?? $formula->almacen_entrada_id,
                'coste_estimado'     => $costeEstimado,
                'notas'              => $datos['notas'] ?? null,
                'user_id'            => auth()->id(),
            ]);

            // Insertar detalle teórico (componentes + producto)
            foreach ($formula->componentes()->with('articulo')->get() as $comp) {
                OrdenFabricacionDetalle::create([
                    'orden_id'         => $orden->id,
                    'articulo_id'      => $comp->articulo_id,
                    'tipo'             => 'C',
                    'cantidad_teorica' => $comp->getCantidadConMerma($lotes),
                    'cantidad_real'    => 0,
                    'precio_coste'     => $comp->articulo->precio_coste,
                ]);
            }

            // Línea del producto fabricado
            OrdenFabricacionDetalle::create([
                'orden_id'         => $orden->id,
                'articulo_id'      => $formula->articulo_id,
                'tipo'             => 'P',
                'cantidad_teorica' => $formula->cantidad_producida * $lotes,
                'cantidad_real'    => 0,
                'precio_coste'     => $formula->articulo->precio_coste,
            ]);

            return $orden->load(['formula.componentes.articulo', 'detalle.articulo']);
        });
    }

    // ── Ejecutar fabricación: consumir componentes + producir artículo ────────
    public function ejecutar(OrdenFabricacion $orden): OrdenFabricacion
    {
        if (!$orden->isEjecutable()) {
            throw ValidationException::withMessages([
                'estado' => "La orden {$orden->numero} no puede ejecutarse en estado '{$orden->estado}'."
            ]);
        }

        // Verificar stock antes de ejecutar
        $check = $this->verificarStock($orden->formula, $orden->cantidad_pedida);
        $insuficientes = array_filter($check, fn($c) => !$c['suficiente']);

        if (!empty($insuficientes)) {
            $nombres = implode(', ', array_column($insuficientes, 'descripcion'));
            throw ValidationException::withMessages([
                'stock' => "Stock insuficiente para: {$nombres}"
            ]);
        }

        return DB::transaction(function () use ($orden) {
            $orden->update(['estado' => 'en_proceso']);
            $almacenSalidaId  = $orden->almacen_salida_id  ?? $orden->formula->almacen_salida_id;
            $almacenEntradaId = $orden->almacen_entrada_id ?? $orden->formula->almacen_entrada_id;
            $costeReal = 0;

            // 1. CONSUMIR componentes (SALIDA de almacén)
            foreach ($orden->componentes()->with('articulo')->get() as $detalle) {
                $cantidadReal = (float)$detalle->cantidad_teorica;
                $stockAntes   = $this->getStock($detalle->articulo_id, $almacenSalidaId);
                $stockDespues = $stockAntes - $cantidadReal;

                $mov = MovimientoAlmacen::create([
                    'fecha'           => today(),
                    'articulo_id'     => $detalle->articulo_id,
                    'almacen_id'      => $almacenSalidaId,
                    'tipo_movimiento' => 'FABRICACION_OUT',
                    'cantidad'        => -$cantidadReal,
                    'stock_anterior'  => $stockAntes,
                    'stock_posterior' => $stockDespues,
                    'precio_coste'    => $detalle->precio_coste,
                    'documento_tipo'  => 'ORDEN_FAB',
                    'documento_id'    => $orden->id,
                    'documento_ref'   => $orden->numero,
                    'observaciones'   => "Consumo fabricación {$orden->numero}",
                    'user_id'         => auth()->id(),
                    'created_at'      => now(),
                ]);

                // Actualizar stock_almacen
                $this->updateStock($detalle->articulo_id, $almacenSalidaId, -$cantidadReal);

                // Actualizar detalle con cantidad real y movimiento
                $detalle->update(['cantidad_real' => $cantidadReal, 'movimiento_id' => $mov->id]);

                $costeReal += $cantidadReal * (float)$detalle->precio_coste;
            }

            // 2. PRODUCIR artículo final (ENTRADA en almacén)
            $prodDetalle     = $orden->productos()->with('articulo')->first();
            $cantidadProducida = $orden->formula->cantidad_producida * $orden->cantidad_pedida;
            $stockAntesProd  = $this->getStock($prodDetalle->articulo_id, $almacenEntradaId);
            $stockDespuesProd= $stockAntesProd + $cantidadProducida;

            $movProd = MovimientoAlmacen::create([
                'fecha'           => today(),
                'articulo_id'     => $prodDetalle->articulo_id,
                'almacen_id'      => $almacenEntradaId,
                'tipo_movimiento' => 'FABRICACION_IN',
                'cantidad'        => $cantidadProducida,
                'stock_anterior'  => $stockAntesProd,
                'stock_posterior' => $stockDespuesProd,
                'precio_coste'    => $costeReal > 0 ? round($costeReal / $cantidadProducida, 4) : $prodDetalle->precio_coste,
                'documento_tipo'  => 'ORDEN_FAB',
                'documento_id'    => $orden->id,
                'documento_ref'   => $orden->numero,
                'observaciones'   => "Producción fabricación {$orden->numero}",
                'user_id'         => auth()->id(),
                'created_at'      => now(),
            ]);

            $this->updateStock($prodDetalle->articulo_id, $almacenEntradaId, $cantidadProducida);

            $prodDetalle->update([
                'cantidad_real' => $cantidadProducida,
                'movimiento_id' => $movProd->id,
            ]);

            // 3. Actualizar coste real del artículo fabricado (coste medio ponderado)
            if ($cantidadProducida > 0) {
                Articulo::where('id', $prodDetalle->articulo_id)->update([
                    'ult_precio_coste' => round($costeReal / $cantidadProducida, 4),
                ]);
            }

            $orden->update([
                'estado'             => 'completada',
                'cantidad_fabricada' => $cantidadProducida,
                'fecha_fin'          => today(),
                'coste_real'         => $costeReal,
            ]);

            return $orden->fresh(['formula', 'detalle.articulo', 'almacenSalida', 'almacenEntrada']);
        });
    }

    // ── Anular orden (solo si no se ha ejecutado) ─────────────────────────────
    public function anular(OrdenFabricacion $orden, string $motivo = ''): OrdenFabricacion
    {
        if ($orden->estado === 'completada') {
            throw ValidationException::withMessages([
                'estado' => 'No se puede anular una orden completada.'
            ]);
        }

        $orden->update(['estado' => 'anulada', 'notas' => $orden->notas . "\nAnulada: {$motivo}"]);
        return $orden;
    }

    // ── Calcular coste estimado ───────────────────────────────────────────────
    private function calcularCosteEstimado(Formula $formula, float $lotes): float
    {
        $coste = 0;
        foreach ($formula->componentes()->with('articulo')->get() as $comp) {
            $coste += $comp->getCantidadConMerma($lotes) * (float)$comp->articulo->precio_coste;
        }
        return round($coste, 2);
    }

    private function getStock(int $articuloId, int $almacenId): float
    {
        return (float)(StockAlmacen::where('articulo_id', $articuloId)
            ->where('almacen_id', $almacenId)
            ->value('stock') ?? 0);
    }

    private function updateStock(int $articuloId, int $almacenId, float $delta): void
    {
        StockAlmacen::updateOrInsert(
            ['articulo_id' => $articuloId, 'almacen_id' => $almacenId],
            ['stock' => DB::raw("stock + {$delta}"), 'updated_at' => now()]
        );
    }
}

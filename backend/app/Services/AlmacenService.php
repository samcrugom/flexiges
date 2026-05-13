<?php

namespace App\Services;

use App\Models\{StockAlmacen, MovimientoAlmacen, Almacen};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlmacenService
{
    public function getStock(int $articuloId, int $almacenId): float
    {
        return (float)(StockAlmacen::where('articulo_id', $articuloId)
            ->where('almacen_id', $almacenId)->value('stock') ?? 0);
    }

    public function updateStock(int $articuloId, int $almacenId, float $delta): void
    {
        StockAlmacen::updateOrInsert(
            ['articulo_id' => $articuloId, 'almacen_id' => $almacenId],
            ['stock' => DB::raw("GREATEST(0, stock + {$delta})"), 'updated_at' => now()]
        );
    }

    public function registrarMovimiento(array $data): MovimientoAlmacen
    {
        return DB::transaction(function () use ($data) {
            $almacenId  = $data['almacen_id'];
            $articuloId = $data['articulo_id'];
            $cantidad   = (float)$data['cantidad'];
            $tipo       = strtoupper($data['tipo_movimiento']);

            $delta = in_array($tipo, ['SALIDA','FABRICACION_OUT','DEVOLUCION_CLI','TRASPASO_OUT'])
                ? -abs($cantidad) : abs($cantidad);

            $stockAntes   = $this->getStock($articuloId, $almacenId);
            $stockDespues = $stockAntes + $delta;

            if ($stockDespues < 0 && !($data['permitir_negativo'] ?? false)) {
                throw ValidationException::withMessages(['stock' => 'Stock insuficiente.']);
            }

            $mov = MovimientoAlmacen::create([
                'fecha'           => $data['fecha'] ?? today(),
                'articulo_id'     => $articuloId,
                'almacen_id'      => $almacenId,
                'tipo_movimiento' => $tipo,
                'cantidad'        => $delta,
                'stock_anterior'  => $stockAntes,
                'stock_posterior' => $stockDespues,
                'precio_coste'    => $data['precio_coste'] ?? 0,
                'documento_tipo'  => $data['documento_tipo'] ?? null,
                'documento_id'    => $data['documento_id'] ?? null,
                'documento_ref'   => $data['documento_ref'] ?? null,
                'lote'            => $data['lote'] ?? null,
                'observaciones'   => $data['observaciones'] ?? null,
                'user_id'         => auth()->id(),
                'created_at'      => now(),
            ]);

            $this->updateStock($articuloId, $almacenId, $delta);
            return $mov->load(['articulo','almacen']);
        });
    }
}
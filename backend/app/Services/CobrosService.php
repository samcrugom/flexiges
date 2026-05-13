<?php

namespace App\Services;

use App\Models\{Cobro, CobroMovimiento, VentaDocumento, Remesa};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CobrosService
{
    public function cobrar(Cobro $cobro, float $importe, array $data = []): Cobro
    {
        return DB::transaction(function () use ($cobro, $importe, $data) {
            if ($importe <= 0 || $importe > $cobro->importe_pendiente) {
                throw ValidationException::withMessages(['importe' => 'Importe no válido.']);
            }

            $nuevoCobrado = (float)$cobro->importe_cobrado + $importe;
            $nuevoEstado  = $nuevoCobrado >= (float)$cobro->importe_total ? 'cobrado' : 'parcial';

            $cobro->update([
                'importe_cobrado' => $nuevoCobrado,
                'estado'          => $nuevoEstado,
            ]);

            CobroMovimiento::create([
                'cobro_id'   => $cobro->id,
                'fecha'      => $data['fecha'] ?? today(),
                'importe'    => $importe,
                'tipo'       => $data['tipo'] ?? 'efectivo',
                'concepto'   => $data['concepto'] ?? 'Cobro registrado',
                'user_id'    => auth()->id(),
                'created_at' => now(),
            ]);

            if ($nuevoEstado === 'cobrado') {
                VentaDocumento::where('id', $cobro->factura_id)->update(['estado' => 'cobrada']);
            }

            return $cobro->fresh(['cliente','factura','movimientos']);
        });
    }

    public function crearRemesa(array $cobrosIds, array $data): Remesa
    {
        return DB::transaction(function () use ($cobrosIds, $data) {
            $cobros = Cobro::whereIn('id', $cobrosIds)->where('estado', 'pendiente')->get();
            if ($cobros->isEmpty()) {
                throw ValidationException::withMessages(['cobros' => 'No hay cobros válidos.']);
            }
            $numero = 'REM-' . date('Ymd') . '-' . str_pad(Remesa::count() + 1, 3, '0', STR_PAD_LEFT);
            $remesa = Remesa::create([
                'numero'        => $numero,
                'fecha'         => today(),
                'banco'         => $data['banco'] ?? null,
                'cuenta'        => $data['cuenta'] ?? null,
                'num_efectos'   => $cobros->count(),
                'importe_total' => $cobros->sum('importe_pendiente'),
                'estado'        => 'preparada',
                'user_id'       => auth()->id(),
            ]);
            $cobros->each(fn($c) => $c->update(['remesa_id' => $remesa->id]));
            return $remesa;
        });
    }
}
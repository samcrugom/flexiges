<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $q = Cliente::with(['formaPago','vendedor','zona','sector'])
            ->activos();

        if ($s = $request->search) $q->search($s);
        if ($v = $request->vendedor_id) $q->where('vendedor_id',$v);
        if ($z = $request->zona_id) $q->where('zona_id',$z);
        if ($t = $request->tipo_cliente) $q->where('tipo_cliente',$t);

        $perPage = min($request->per_page ?? 25, 100);
        return $q->orderBy($request->sort_by ?? 'nombre', $request->sort_dir ?? 'asc')
                 ->paginate($perPage);
    }

    public function show(Cliente $cliente)
    {
        return $cliente->load(['formaPago','vendedor','zona','sector','ruta','delegacion','direcciones','tarifasEspeciales.articulo']);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['codigo'] = $data['codigo'] ?? $this->nextCodigo();
        $cliente = Cliente::create($data);
        return response()->json($cliente->load(['formaPago','vendedor']), 201);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate($this->rules($cliente->id));
        $cliente->update($data);
        return $cliente->fresh(['formaPago','vendedor','zona','sector']);
    }

    public function destroy(Cliente $cliente)
    {
        if ($cliente->documentos()->exists()) {
            return response()->json(['message' => 'No se puede eliminar: tiene documentos asociados.'], 422);
        }
        $cliente->update(['activo' => false]);
        return response()->json(['message' => 'Cliente desactivado.']);
    }

    public function facturas(Cliente $cliente, Request $request)
    {
        return $cliente->facturas()
            ->with(['lineas','cobros','vendedor'])
            ->orderByDesc('fecha')
            ->paginate(20);
    }

    public function cobros(Cliente $cliente)
    {
        return $cliente->cobros()
            ->with('factura')
            ->orderByDesc('fecha_vencimiento')
            ->get();
    }

    public function estadisticas(Cliente $cliente)
    {
        $stats = \Illuminate\Support\Facades\DB::select("
            SELECT ejercicio, mes,
                   num_facturas, base_imponible, cuota_iva, total, margen
            FROM estadisticas_ventas_cliente
            WHERE cliente_id=?
            ORDER BY ejercicio DESC, mes DESC
            LIMIT 24
        ", [$cliente->id]);

        $totales = \Illuminate\Support\Facades\DB::selectOne("
            SELECT COUNT(id) AS num_facturas,
                   COALESCE(SUM(total_factura),0) AS total_facturado,
                   COALESCE(SUM(base_imponible),0) AS base_total
            FROM ventas_documentos
            WHERE cliente_id=? AND tipo='factura' AND estado NOT IN('anulado','borrador')
        ", [$cliente->id]);

        return response()->json(['mensual' => $stats, 'totales' => $totales]);
    }

    private function rules(?int $exceptId = null): array
    {
        return [
            'codigo'         => "sometimes|string|max:8|unique:clientes,codigo,{$exceptId}",
            'nombre'         => 'required|string|max:120',
            'nombre_comercial'=> 'nullable|string|max:80',
            'nif'            => 'nullable|string|max:20',
            'direccion'      => 'nullable|string|max:100',
            'codigo_postal'  => 'nullable|string|max:10',
            'localidad'      => 'nullable|string|max:60',
            'provincia'      => 'nullable|string|max:60',
            'telefono'       => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:100',
            'forma_pago_id'  => 'nullable|exists:formas_pago,id',
            'tipo_cliente'   => 'required|in:N,R,E',
            'tarifa'         => 'required|integer|between:1,4',
            'descuento_pct'  => 'nullable|numeric|between:0,100',
            'vendedor_id'    => 'nullable|exists:vendedores,id',
            'zona_id'        => 'nullable|exists:zonas,id',
            'sector_id'      => 'nullable|exists:sectores,id',
            'credito_maximo' => 'nullable|numeric|min:0',
            'iban'           => 'nullable|string|max:34',
            'activo'         => 'boolean',
            'observaciones'  => 'nullable|string',
        ];
    }

    private function nextCodigo(): string
    {
        $max = Cliente::max('codigo') ?? '0000';
        return str_pad((int)$max + 1, 4, '0', STR_PAD_LEFT);
    }
}

<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Cobro;
use App\Services\CobrosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ─── COBROS ───────────────────────────────────────────────────────────────────
class CobrosController extends Controller
{
    public function __construct(private readonly CobrosService $service) {}

    public function index(Request $request)
    {
        $q = Cobro::with(['cliente','factura']);
        if ($e = $request->estado) $q->where('estado',$e);
        if ($c = $request->cliente_id) $q->where('cliente_id',$c);
        if ($request->vencidos) $q->vencidos();
        if ($df = $request->fecha_desde) $q->where('fecha_vencimiento','>=',$df);
        if ($dh = $request->fecha_hasta) $q->where('fecha_vencimiento','<=',$dh);
        return $q->orderBy('fecha_vencimiento')->paginate(min($request->per_page??25,100));
    }

    public function show(Cobro $cobro) { return $cobro->load(['cliente','factura','movimientos','remesa']); }

    public function cobrar(Request $request, Cobro $cobro)
    {
        $data = $request->validate([
            'importe'  => 'required|numeric|min:0.01',
            'fecha'    => 'nullable|date',
            'tipo'     => 'nullable|string|max:30',
            'concepto' => 'nullable|string|max:200',
        ]);
        return response()->json($this->service->cobrar($cobro, $data['importe'], $data));
    }

    public function resumen()
    {
        return response()->json(DB::selectOne('SELECT * FROM v_cobros_pendientes LIMIT 0') ? // just to validate view exists
            DB::select('SELECT estado, COUNT(*) as cantidad, COALESCE(SUM(importe_total-importe_cobrado),0) as importe FROM cobros GROUP BY estado') : []
        );
    }

    public function crearRemesa(Request $request)
    {
        $data = $request->validate([
            'cobros_ids'   => 'required|array|min:1',
            'cobros_ids.*' => 'exists:cobros,id',
            'banco'        => 'nullable|string|max:80',
            'cuenta'       => 'nullable|string|max:34',
        ]);
        return response()->json($this->service->crearRemesa($data['cobros_ids'], $data), 201);
    }
}
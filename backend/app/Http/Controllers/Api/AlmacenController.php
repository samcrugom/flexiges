<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Almacen, StockAlmacen, MovimientoAlmacen};
use App\Services\AlmacenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ─── ALMACÉN ─────────────────────────────────────────────────────────────────
class AlmacenController extends Controller
{
    public function __construct(private readonly AlmacenService $service) {}

    public function stock(Request $request)
    {
        $q = StockAlmacen::with(['articulo.familia','almacen']);
        if ($aid = $request->almacen_id) $q->where('almacen_id',$aid);
        if ($s = $request->search) $q->whereHas('articulo', fn($a)=>$a->search($s));
        if ($request->bajo_minimo) $q->whereHas('articulo', fn($a)=>$a->bajoMinimo());
        return $q->orderByDesc('stock')->paginate(50);
    }

    public function movimientos(Request $request)
    {
        $q = MovimientoAlmacen::with(['articulo','almacen','user']);
        if ($aid = $request->articulo_id) $q->where('articulo_id',$aid);
        if ($alid = $request->almacen_id) $q->where('almacen_id',$alid);
        if ($t = $request->tipo) $q->where('tipo_movimiento',$t);
        if ($df = $request->fecha_desde) $q->where('fecha','>=',$df);
        if ($dh = $request->fecha_hasta) $q->where('fecha','<=',$dh);
        return $q->orderByDesc('fecha')->orderByDesc('id')->paginate(50);
    }

    public function registrar(Request $request)
    {
        $data = $request->validate([
            'articulo_id'     => 'required|exists:articulos,id',
            'almacen_id'      => 'required|exists:almacenes,id',
            'tipo_movimiento' => 'required|in:ENTRADA,SALIDA,AJUSTE_POS,AJUSTE_NEG,DEVOLUCION_CLI,DEVOLUCION_PROV,TRASPASO_IN,TRASPASO_OUT',
            'cantidad'        => 'required|numeric|min:0.0001',
            'precio_coste'    => 'nullable|numeric|min:0',
            'fecha'           => 'nullable|date',
            'lote'            => 'nullable|string|max:30',
            'observaciones'   => 'nullable|string|max:200',
        ]);
        return response()->json($this->service->registrarMovimiento($data), 201);
    }

    public function inventario(Request $request)
    {
        $almacenId = $request->almacen_id ?? Almacen::where('es_principal',true)->value('id');
        $rows = DB::select("
            SELECT a.id, a.referencia, a.descripcion, a.unidad_medida,
                   f.nombre AS familia,
                   COALESCE(sa.stock,0) AS stock,
                   a.stock_minimo,
                   COALESCE(sa.stock,0) * a.precio_coste AS valor_coste,
                   COALESCE(sa.stock,0) * a.tarifa1 AS valor_pvp
            FROM articulos a
            LEFT JOIN familias_articulo f ON f.id=a.familia_id
            LEFT JOIN stock_almacen sa ON sa.articulo_id=a.id AND sa.almacen_id=?
            WHERE a.activo=true
            ORDER BY a.descripcion
        ", [$almacenId]);
        return response()->json($rows);
    }
}
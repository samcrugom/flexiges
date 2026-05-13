<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Articulo, StockAlmacen, MovimientoAlmacen, Almacen};
use Illuminate\Http\Request;

// ─── ARTÍCULOS ────────────────────────────────────────────────────────────────
class ArticuloController extends Controller
{
    public function index(Request $request)
    {
        $q = Articulo::with(['familia','tipoIva','proveedor'])->activos();
        if ($s = $request->search)    $q->search($s);
        if ($f = $request->familia_id) $q->where('familia_id',$f);
        if ($request->bajo_minimo)     $q->bajoMinimo();
        if ($request->fabricado !== null) $q->where('es_fabricado',(bool)$request->fabricado);
        return $q->orderBy($request->sort_by ?? 'descripcion')->paginate(min($request->per_page ?? 30, 200));
    }

    public function show(Articulo $articulo)
    {
        return $articulo->load(['familia','tipoIva','proveedor','stockAlmacen.almacen','formulas.componentes.articulo']);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $articulo = Articulo::create($data);
        // Inicializar stock en almacén principal
        $alm = Almacen::where('es_principal',true)->first();
        if ($alm) StockAlmacen::create(['articulo_id'=>$articulo->id,'almacen_id'=>$alm->id,'stock'=>0]);
        return response()->json($articulo->load(['familia','tipoIva']), 201);
    }

    public function update(Request $request, Articulo $articulo)
    {
        $articulo->update($request->validate($this->rules($articulo->id)));
        return $articulo->fresh(['familia','tipoIva','proveedor']);
    }

    public function destroy(Articulo $articulo)
    {
        if ($articulo->stockAlmacen()->where('stock','>',0)->exists()) {
            return response()->json(['message'=>'No se puede eliminar: tiene stock.'],422);
        }
        $articulo->update(['activo'=>false]);
        return response()->json(['message'=>'Artículo desactivado.']);
    }

    public function stock(Articulo $articulo)
    {
        return $articulo->stockAlmacen()->with('almacen')->get();
    }

    public function movimientos(Articulo $articulo, Request $request)
    {
        return MovimientoAlmacen::where('articulo_id',$articulo->id)
            ->with(['almacen','user'])
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(50);
    }

    private function rules(?int $id=null): array {
        return [
            'referencia'    => "required|string|max:20|unique:articulos,referencia,{$id}",
            'descripcion'   => 'required|string|max:120',
            'descripcion2'  => 'nullable|string|max:120',
            'familia_id'    => 'nullable|exists:familias_articulo,id',
            'proveedor_id'  => 'nullable|exists:proveedores,id',
            'tipo_iva_id'   => 'required|exists:tipos_iva,id',
            'unidad_medida' => 'required|in:UD,KG,MT,LT,CJ,BL,ML,GR',
            'ean13'         => "nullable|string|size:13|unique:articulos,ean13,{$id}",
            'precio_coste'  => 'nullable|numeric|min:0',
            'tarifa1'       => 'nullable|numeric|min:0',
            'tarifa2'       => 'nullable|numeric|min:0',
            'tarifa3'       => 'nullable|numeric|min:0',
            'tarifa4'       => 'nullable|numeric|min:0',
            'stock_minimo'  => 'nullable|numeric|min:0',
            'es_fabricado'  => 'boolean',
            'activo'        => 'boolean',
            'observaciones' => 'nullable|string',
        ];
    }
}
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Formula, OrdenFabricacion};
use App\Services\FabricacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ─── FABRICACIÓN ─────────────────────────────────────────────────────────────
class FabricacionController extends Controller
{
    public function __construct(private readonly FabricacionService $service) {}

    // Fórmulas
    public function formulas(Request $request) {
        return Formula::with(['articulo','componentes.articulo','almacenSalida','almacenEntrada'])
            ->activas()->orderBy('nombre')->paginate(30);
    }

    public function showFormula(Formula $formula) {
        return $formula->load(['articulo.tipoIva','componentes.articulo.stockAlmacen','almacenSalida','almacenEntrada']);
    }

    public function storeFormula(Request $request) {
        $data = $request->validate([
            'codigo'              => 'required|string|max:15|unique:formulas',
            'nombre'              => 'required|string|max:120',
            'articulo_id'         => 'required|exists:articulos,id',
            'cantidad_producida'  => 'required|numeric|min:0.0001',
            'almacen_salida_id'   => 'nullable|exists:almacenes,id',
            'almacen_entrada_id'  => 'nullable|exists:almacenes,id',
            'tiempo_fabricacion'  => 'nullable|integer|min:1',
            'activa'              => 'boolean',
            'notas'               => 'nullable|string',
            'componentes'         => 'required|array|min:1',
            'componentes.*.articulo_id' => 'required|exists:articulos,id',
            'componentes.*.cantidad'    => 'required|numeric|min:0.0001',
            'componentes.*.unidad'      => 'nullable|string|max:5',
            'componentes.*.orden'       => 'nullable|integer',
            'componentes.*.merma_pct'   => 'nullable|numeric|between:0,100',
            'componentes.*.notas'       => 'nullable|string|max:200',
        ]);
        $comps = $data['componentes'];
        unset($data['componentes']);

        $formula = DB::transaction(function() use ($data, $comps) {
            $f = Formula::create($data);
            foreach ($comps as $i => $c) {
                \App\Models\FormulaComponente::create(array_merge($c, ['formula_id'=>$f->id,'orden'=>$c['orden']??$i+1]));
            }
            return $f;
        });
        return response()->json($formula->load(['componentes.articulo','articulo']), 201);
    }

    public function updateFormula(Request $request, Formula $formula) {
        $data = $request->validate([
            'nombre'             => 'sometimes|string|max:120',
            'cantidad_producida' => 'sometimes|numeric|min:0.0001',
            'almacen_salida_id'  => 'nullable|exists:almacenes,id',
            'almacen_entrada_id' => 'nullable|exists:almacenes,id',
            'tiempo_fabricacion' => 'nullable|integer',
            'activa'             => 'boolean',
            'notas'              => 'nullable|string',
            'componentes'        => 'sometimes|array|min:1',
            'componentes.*.articulo_id' => 'required_with:componentes|exists:articulos,id',
            'componentes.*.cantidad'    => 'required_with:componentes|numeric|min:0.0001',
            'componentes.*.unidad'      => 'nullable|string|max:5',
            'componentes.*.orden'       => 'nullable|integer',
            'componentes.*.merma_pct'   => 'nullable|numeric|between:0,100',
        ]);
        $comps = $data['componentes'] ?? null;
        unset($data['componentes']);

        DB::transaction(function() use ($formula, $data, $comps) {
            $formula->update($data);
            if ($comps !== null) {
                $formula->componentes()->delete();
                foreach ($comps as $i => $c) {
                    \App\Models\FormulaComponente::create(array_merge($c,['formula_id'=>$formula->id,'orden'=>$c['orden']??$i+1]));
                }
            }
        });
        return $formula->fresh(['componentes.articulo','articulo']);
    }

    public function destroyFormula(Formula $formula) {
        if ($formula->ordenes()->whereNotIn('estado',['borrador','anulada'])->exists()) {
            return response()->json(['message'=>'Fórmula con órdenes activas.'],422);
        }
        $formula->update(['activa'=>false]);
        return response()->json(['message'=>'Fórmula desactivada.']);
    }

    public function verificarStock(Request $request, Formula $formula) {
        $lotes = $request->validate(['lotes'=>'required|numeric|min:0.001'])['lotes'];
        return response()->json($this->service->verificarStock($formula, $lotes));
    }

    // Órdenes
    public function ordenes(Request $request) {
        $q = OrdenFabricacion::with(['formula.articulo','user'])->orderByDesc('fecha');
        if ($e = $request->estado) $q->where('estado',$e);
        if ($f = $request->formula_id) $q->where('formula_id',$f);
        return $q->paginate(30);
    }

    public function showOrden(OrdenFabricacion $orden) {
        return $orden->load(['formula.componentes.articulo','detalle.articulo','almacenSalida','almacenEntrada','user']);
    }

    public function crearOrden(Request $request) {
        $data = $request->validate([
            'formula_id'         => 'required|exists:formulas,id',
            'lotes'              => 'required|numeric|min:0.001',
            'fecha'              => 'nullable|date',
            'fecha_prevista'     => 'nullable|date',
            'almacen_salida_id'  => 'nullable|exists:almacenes,id',
            'almacen_entrada_id' => 'nullable|exists:almacenes,id',
            'notas'              => 'nullable|string',
        ]);
        $formula = Formula::findOrFail($data['formula_id']);
        $orden   = $this->service->crearOrden($formula, $data['lotes'], $data);
        return response()->json($orden, 201);
    }

    public function ejecutar(OrdenFabricacion $orden) {
        return response()->json($this->service->ejecutar($orden));
    }

    public function anular(Request $request, OrdenFabricacion $orden) {
        $motivo = $request->validate(['motivo'=>'nullable|string|max:200'])['motivo'] ?? '';
        return response()->json($this->service->anular($orden, $motivo));
    }
}
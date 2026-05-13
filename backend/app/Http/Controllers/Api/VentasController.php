<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\VentaDocumento;
use App\Services\{VentasService, PdfService};
use Illuminate\Http\Request;

// ─── VENTAS ───────────────────────────────────────────────────────────────────
class VentasController extends Controller
{
    public function __construct(
        private readonly VentasService $ventasService,
        private readonly PdfService    $pdfService,
    ) {}

    public function index(Request $request)
    {
        $q = VentaDocumento::with(['cliente','vendedor','formaPago']);
        if ($t = $request->tipo) $q->tipo($t);
        if ($s = $request->search) $q->search($s);
        if ($c = $request->cliente_id) $q->where('cliente_id',$c);
        if ($e = $request->estado) $q->where('estado',$e);
        if ($df = $request->fecha_desde) $q->where('fecha','>=',$df);
        if ($dh = $request->fecha_hasta) $q->where('fecha','<=',$dh);
        if ($v = $request->vendedor_id) $q->where('vendedor_id',$v);
        return $q->orderByDesc('fecha')->orderByDesc('id')->paginate(min($request->per_page??25,100));
    }

    public function show(VentaDocumento $documento)
    {
        return $documento->load(['cliente','lineas.articulo','lineas.tipoIva','ivaDetalle.tipoIva','cobros','vendedor','formaPago','almacen','docOrigen']);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        return response()->json($this->ventasService->crear($data), 201);
    }

    public function facturarAlbaranes(Request $request)
    {
        $data = $request->validate([
            'albaran_ids'    => 'required|array|min:1',
            'albaran_ids.*'  => 'exists:ventas_documentos,id',
            'forma_pago_id'  => 'nullable|exists:formas_pago,id',
            'notas'          => 'nullable|string',
        ]);
        return response()->json($this->ventasService->facturarAlbaranes($data['albaran_ids'], $data));
    }

    public function anular(VentaDocumento $documento)
    {
        return response()->json($this->ventasService->anular($documento));
    }

    public function pdf(VentaDocumento $documento)
    {
        return $this->pdfService->streamFactura($documento);
    }

    private function rules(): array {
        return [
            'tipo'               => 'required|in:presupuesto,pedido,albaran,factura,rectificativa',
            'cliente_id'         => 'required|exists:clientes,id',
            'fecha'              => 'nullable|date',
            'fecha_entrega'      => 'nullable|date',
            'vendedor_id'        => 'nullable|exists:vendedores,id',
            'forma_pago_id'      => 'nullable|exists:formas_pago,id',
            'almacen_id'         => 'nullable|exists:almacenes,id',
            'ruta_id'            => 'nullable|exists:rutas,id',
            'doc_origen_id'      => 'nullable|exists:ventas_documentos,id',
            'doc_rectifica_id'   => 'nullable|exists:ventas_documentos,id',
            'referencia_cliente' => 'nullable|string|max:30',
            'notas'              => 'nullable|string',
            'notas_internas'     => 'nullable|string',
            'mover_stock'        => 'boolean',
            'lineas'             => 'required|array|min:1',
            'lineas.*.articulo_id'    => 'nullable|exists:articulos,id',
            'lineas.*.descripcion'    => 'required|string|max:120',
            'lineas.*.cantidad'       => 'required|numeric|min:0.0001',
            'lineas.*.precio_unitario'=> 'required|numeric|min:0',
            'lineas.*.descuento_pct'  => 'nullable|numeric|between:0,100',
            'lineas.*.tipo_iva_id'    => 'required|exists:tipos_iva,id',
            'lineas.*.almacen_id'     => 'nullable|exists:almacenes,id',
            'lineas.*.tipo_linea'     => 'nullable|in:A,C,S',
        ];
    }
}
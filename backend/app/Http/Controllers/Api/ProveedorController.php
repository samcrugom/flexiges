<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\Request;

// ─── PROVEEDORES ──────────────────────────────────────────────────────────────
class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $q = Proveedor::with(['formaPago'])->activos();
        if ($s = $request->search) $q->search($s);
        return $q->orderBy('nombre')->paginate(min($request->per_page ?? 25, 100));
    }
    public function show(Proveedor $proveedor) { return $proveedor->load(['formaPago','articulos']); }
    public function store(Request $request) {
        $data = $request->validate($this->rules());
        $data['codigo'] = $data['codigo'] ?? ('P' . str_pad(Proveedor::count()+1,3,'0',STR_PAD_LEFT));
        return response()->json(Proveedor::create($data)->load(['formaPago']), 201);
    }
    public function update(Request $request, Proveedor $p) {
        $p->update($request->validate($this->rules($p->id)));
        return $p->fresh(['formaPago']);
    }
    public function destroy(Proveedor $p) {
        $p->update(['activo'=>false]);
        return response()->json(['message'=>'Proveedor desactivado.']);
    }
    private function rules(?int $id=null): array {
        return [
            'codigo'        => "sometimes|string|max:8|unique:proveedores,codigo,{$id}",
            'nombre'        => 'required|string|max:120',
            'nif'           => 'nullable|string|max:20',
            'direccion'     => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:10',
            'localidad'     => 'nullable|string|max:60',
            'provincia'     => 'nullable|string|max:60',
            'telefono'      => 'nullable|string|max:20',
            'email'         => 'nullable|email',
            'forma_pago_id' => 'nullable|exists:formas_pago,id',
            'descuento_pct' => 'nullable|numeric|between:0,100',
            'plazo_entrega' => 'nullable|integer|min:0',
            'iban'          => 'nullable|string|max:34',
            'activo'        => 'boolean',
            'observaciones' => 'nullable|string',
        ];
    }
}
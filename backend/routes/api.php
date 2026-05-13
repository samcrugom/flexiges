<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController, DashboardController,
    ClienteController, ProveedorController, ArticuloController,
    AlmacenController, FabricacionController,
    VentasController, CobrosController,
};

// ── Auth (público) ─────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

// ── Rutas protegidas ───────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // Dashboard
    Route::get('dashboard', DashboardController::class);

    // Maestros (lectura libre para usuarios autenticados)
    Route::get('maestros/formas-pago',   fn() => \App\Models\FormaPago::where('activo',true)->orderBy('nombre')->get());
    Route::get('maestros/tipos-iva',     fn() => \App\Models\TipoIva::where('activo',true)->get());
    Route::get('maestros/almacenes',     fn() => \App\Models\Almacen::where('activo',true)->orderBy('nombre')->get());
    Route::get('maestros/vendedores',    fn() => \App\Models\Vendedor::where('activo',true)->with('zona')->orderBy('nombre')->get());
    Route::get('maestros/zonas',         fn() => \App\Models\Zona::orderBy('nombre')->get());
    Route::get('maestros/sectores',      fn() => \App\Models\Sector::orderBy('nombre')->get());
    Route::get('maestros/rutas',         fn() => \App\Models\Ruta::where('activo',true)->orderBy('nombre')->get());
    Route::get('maestros/delegaciones',  fn() => \App\Models\Delegacion::orderBy('nombre')->get());
    Route::get('maestros/familias',      fn() => \App\Models\FamiliaArticulo::with('children')->whereNull('parent_id')->orderBy('orden')->get());
    Route::get('empresa',                fn() => \App\Models\Empresa::actual());
    Route::put('empresa',                function(\Illuminate\Http\Request $req) {
        $emp = \App\Models\Empresa::actual();
        $emp->update($req->validate(['nombre'=>'required|string','nif'=>'required|string','direccion'=>'nullable|string','codigo_postal'=>'nullable|string','localidad'=>'nullable|string','provincia'=>'nullable|string','telefono'=>'nullable|string','email'=>'nullable|email','iban'=>'nullable|string','pie_factura'=>'nullable|string','serie_factura'=>'nullable|string','serie_albaran'=>'nullable|string']));
        return response()->json($emp);
    });

    // Clientes
    Route::get   ('clientes',                    [ClienteController::class, 'index']);
    Route::post  ('clientes',                    [ClienteController::class, 'store']);
    Route::get   ('clientes/{cliente}',          [ClienteController::class, 'show']);
    Route::put   ('clientes/{cliente}',          [ClienteController::class, 'update']);
    Route::delete('clientes/{cliente}',          [ClienteController::class, 'destroy']);
    Route::get   ('clientes/{cliente}/facturas', [ClienteController::class, 'facturas']);
    Route::get   ('clientes/{cliente}/cobros',   [ClienteController::class, 'cobros']);
    Route::get   ('clientes/{cliente}/estadisticas', [ClienteController::class, 'estadisticas']);

    // Proveedores
    Route::apiResource('proveedores', ProveedorController::class);

    // Artículos
    Route::get   ('articulos',                      [ArticuloController::class, 'index']);
    Route::post  ('articulos',                      [ArticuloController::class, 'store']);
    Route::get   ('articulos/{articulo}',           [ArticuloController::class, 'show']);
    Route::put   ('articulos/{articulo}',           [ArticuloController::class, 'update']);
    Route::delete('articulos/{articulo}',           [ArticuloController::class, 'destroy']);
    Route::get   ('articulos/{articulo}/stock',     [ArticuloController::class, 'stock']);
    Route::get   ('articulos/{articulo}/movimientos',[ArticuloController::class,'movimientos']);

    // Almacén
    Route::get ('almacen/stock',        [AlmacenController::class, 'stock']);
    Route::get ('almacen/movimientos',  [AlmacenController::class, 'movimientos']);
    Route::post('almacen/movimientos',  [AlmacenController::class, 'registrar']);
    Route::get ('almacen/inventario',   [AlmacenController::class, 'inventario']);

    // Fabricación — Fórmulas
    Route::get   ('fabricacion/formulas',                          [FabricacionController::class, 'formulas']);
    Route::post  ('fabricacion/formulas',                          [FabricacionController::class, 'storeFormula']);
    Route::get   ('fabricacion/formulas/{formula}',                [FabricacionController::class, 'showFormula']);
    Route::put   ('fabricacion/formulas/{formula}',                [FabricacionController::class, 'updateFormula']);
    Route::delete('fabricacion/formulas/{formula}',                [FabricacionController::class, 'destroyFormula']);
    Route::post  ('fabricacion/formulas/{formula}/verificar-stock',[FabricacionController::class, 'verificarStock']);

    // Fabricación — Órdenes
    Route::get   ('fabricacion/ordenes',             [FabricacionController::class, 'ordenes']);
    Route::post  ('fabricacion/ordenes',             [FabricacionController::class, 'crearOrden']);
    Route::get   ('fabricacion/ordenes/{orden}',     [FabricacionController::class, 'showOrden']);
    Route::post  ('fabricacion/ordenes/{orden}/ejecutar', [FabricacionController::class, 'ejecutar']);
    Route::post  ('fabricacion/ordenes/{orden}/anular',   [FabricacionController::class, 'anular']);

    // Ventas
    Route::get   ('ventas',                               [VentasController::class, 'index']);
    Route::post  ('ventas',                               [VentasController::class, 'store']);
    Route::get   ('ventas/{documento}',                   [VentasController::class, 'show']);
    Route::post  ('ventas/facturar-albaranes',            [VentasController::class, 'facturarAlbaranes']);
    Route::post  ('ventas/{documento}/anular',            [VentasController::class, 'anular']);
    Route::get   ('ventas/{documento}/pdf',               [VentasController::class, 'pdf']);

    // Cobros
    Route::get   ('cobros',              [CobrosController::class, 'index']);
    Route::get   ('cobros/resumen',      [CobrosController::class, 'resumen']);
    Route::get   ('cobros/{cobro}',      [CobrosController::class, 'show']);
    Route::post  ('cobros/{cobro}/cobrar', [CobrosController::class, 'cobrar']);
    Route::post  ('cobros/remesas',      [CobrosController::class, 'crearRemesa']);

    // Admin — usuarios
    Route::middleware('auth:sanctum')->group(function () {
        Route::get ('admin/users',         fn() => \App\Models\User::with('roles')->get());
        Route::post('admin/users',         function(\Illuminate\Http\Request $r) {
            $data = $r->validate(['name'=>'required|string','email'=>'required|email|unique:users','password'=>'required|min:8','role'=>'required|string']);
            $u = \App\Models\User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>\Illuminate\Support\Facades\Hash::make($data['password']),'activo'=>true]);
            $u->syncRoles([$data['role']]);
            return response()->json($u->load('roles'),201);
        });
        Route::put ('admin/users/{user}',  function(\Illuminate\Http\Request $r, \App\Models\User $u) {
            $data = $r->validate(['name'=>'sometimes|string','activo'=>'boolean','role'=>'sometimes|string']);
            if (isset($data['role'])) { $u->syncRoles([$data['role']]); unset($data['role']); }
            $u->update($data);
            return $u->fresh('roles');
        });
        Route::get ('admin/audit-logs',    fn(\Illuminate\Http\Request $r) =>
            \App\Models\AuditLog::with('user')->orderByDesc('created_at')->paginate(50)
        );
        Route::get ('admin/roles',         fn() => \Spatie\Permission\Models\Role::with('permissions')->get());
    });
});

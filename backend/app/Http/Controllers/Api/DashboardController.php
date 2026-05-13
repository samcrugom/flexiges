<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $kpis = DB::selectOne('SELECT * FROM v_dashboard_kpis');

        // Ventas últimos 6 meses
        $ventasMensuales = DB::select("
            SELECT
                TO_CHAR(fecha,'YYYY-MM') AS mes,
                TO_CHAR(fecha,'Mon YY')  AS mes_label,
                COALESCE(SUM(total_factura),0) AS total,
                COUNT(*) AS num_facturas
            FROM ventas_documentos
            WHERE tipo = 'factura'
              AND estado NOT IN ('anulado','borrador')
              AND fecha >= CURRENT_DATE - INTERVAL '6 months'
            GROUP BY TO_CHAR(fecha,'YYYY-MM'), TO_CHAR(fecha,'Mon YY')
            ORDER BY mes ASC
        ");

        // Top 5 clientes mes actual
        $topClientes = DB::select("
            SELECT
                c.id, c.codigo, c.nombre,
                COALESCE(SUM(vd.total_factura),0) AS total,
                COUNT(vd.id) AS num_facturas
            FROM clientes c
            JOIN ventas_documentos vd ON vd.cliente_id = c.id
            WHERE vd.tipo='factura'
              AND vd.estado NOT IN ('anulado','borrador')
              AND DATE_TRUNC('month',vd.fecha)=DATE_TRUNC('month',CURRENT_DATE)
            GROUP BY c.id,c.codigo,c.nombre
            ORDER BY total DESC
            LIMIT 5
        ");

        // Artículos bajo mínimo
        $bajosMinimo = DB::select('SELECT * FROM v_stock_bajo_minimo LIMIT 10');

        // Cobros próximos a vencer (7 días)
        $cobrosProximos = DB::select("
            SELECT * FROM v_cobros_pendientes
            WHERE fecha_vencimiento <= CURRENT_DATE + INTERVAL '7 days'
            ORDER BY fecha_vencimiento ASC
            LIMIT 10
        ");

        // Órdenes fabricación activas
        $ordenesFab = DB::select("
            SELECT of.numero, of.estado, of.fecha, of.cantidad_pedida,
                   f.nombre AS formula, a.descripcion AS producto
            FROM ordenes_fabricacion of
            JOIN formulas f ON f.id=of.formula_id
            JOIN articulos a ON a.id=f.articulo_id
            WHERE of.estado IN ('confirmada','en_proceso')
            ORDER BY of.fecha DESC
            LIMIT 5
        ");

        return response()->json([
            'kpis'             => $kpis,
            'ventas_mensuales' => $ventasMensuales,
            'top_clientes'     => $topClientes,
            'bajos_minimo'     => $bajosMinimo,
            'cobros_proximos'  => $cobrosProximos,
            'ordenes_fab'      => $ordenesFab,
        ]);
    }
}

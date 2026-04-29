<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── EXTENSIONES POSTGRESQL ────────────────────────────────────────────
        DB::statement('CREATE EXTENSION IF NOT EXISTS "unaccent"');

        // ── TRIGGER: actualizar stock_total en articulos ───────────────────────
        DB::statement(<<<SQL
            CREATE OR REPLACE FUNCTION fn_update_stock_total()
            RETURNS TRIGGER AS \$\$
            BEGIN
                UPDATE articulos
                SET stock_total = (
                    SELECT COALESCE(SUM(stock), 0)
                    FROM stock_almacen
                    WHERE articulo_id = NEW.articulo_id
                ),
                updated_at = NOW()
                WHERE id = NEW.articulo_id;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        SQL);

        DB::statement('DROP TRIGGER IF EXISTS trg_update_stock_total ON stock_almacen');

		DB::statement(<<<SQL
			CREATE TRIGGER trg_update_stock_total
			AFTER INSERT OR UPDATE ON stock_almacen
			FOR EACH ROW EXECUTE FUNCTION fn_update_stock_total()
		SQL);

        // ── TRIGGER: actualizar saldo_pendiente en clientes ───────────────────
        DB::statement(<<<SQL
            CREATE OR REPLACE FUNCTION fn_update_saldo_cliente()
            RETURNS TRIGGER AS \$\$
            DECLARE v_cliente_id BIGINT;
            BEGIN
                v_cliente_id := COALESCE(NEW.cliente_id, OLD.cliente_id);
                UPDATE clientes
                SET saldo_pendiente = (
                    SELECT COALESCE(
                        SUM(importe_total - importe_cobrado), 0
                    )
                    FROM cobros
                    WHERE cliente_id = v_cliente_id
                    AND estado IN ('pendiente', 'parcial')
                ),
                updated_at = NOW()
                WHERE id = v_cliente_id;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        SQL);

        DB::statement('DROP TRIGGER IF EXISTS trg_update_saldo_cliente ON cobros');

		DB::statement(<<<SQL
			CREATE TRIGGER trg_update_saldo_cliente
			AFTER INSERT OR UPDATE OR DELETE ON cobros
			FOR EACH ROW EXECUTE FUNCTION fn_update_saldo_cliente()
		SQL);

        // ── VISTA: stock bajo mínimo ──────────────────────────────────────────
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_stock_bajo_minimo AS
            SELECT
                a.id,
                a.referencia,
                a.descripcion,
                a.unidad_medida,
                a.stock_total,
                a.stock_minimo,
                a.stock_total - a.stock_minimo AS diferencia,
                f.nombre AS familia
            FROM articulos a
            LEFT JOIN familias_articulo f ON f.id = a.familia_id
            WHERE a.activo = TRUE
              AND a.stock_total < a.stock_minimo
            ORDER BY diferencia ASC;
        SQL);

        // ── VISTA: cobros pendientes ──────────────────────────────────────────
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_cobros_pendientes AS
            SELECT
                c.id,
                c.numero,
                c.fecha_vencimiento,
                c.importe_total,
                c.importe_cobrado,
                (c.importe_total - c.importe_cobrado) AS importe_pendiente,
                c.estado,
                c.tipo_cobro,
                cl.id    AS cliente_id,
                cl.codigo AS cliente_codigo,
                cl.nombre AS cliente_nombre,
                vd.numero AS factura_numero,
                CASE
                    WHEN c.fecha_vencimiento < CURRENT_DATE
                         AND c.estado IN ('pendiente', 'parcial') THEN true
                    ELSE false
                END AS vencido,
                (CURRENT_DATE - c.fecha_vencimiento) AS dias_vencido
            FROM cobros c
            JOIN clientes   cl ON cl.id = c.cliente_id
            JOIN ventas_documentos vd ON vd.id = c.factura_id
            WHERE c.estado IN ('pendiente', 'parcial');
        SQL);

        // ── VISTA: KPIs dashboard ─────────────────────────────────────────────
        DB::statement(<<<SQL
            CREATE OR REPLACE VIEW v_dashboard_kpis AS
            SELECT
                (
                    SELECT COALESCE(SUM(total_factura), 0)
                    FROM ventas_documentos
                    WHERE tipo = 'factura'
                      AND estado NOT IN ('anulado','borrador')
                      AND DATE_TRUNC('month', fecha) = DATE_TRUNC('month', CURRENT_DATE)
                ) AS ventas_mes,
                (
                    SELECT COALESCE(SUM(importe_total - importe_cobrado), 0)
                    FROM cobros
                    WHERE estado IN ('pendiente','parcial')
                ) AS cobros_pendientes,
                (
                    SELECT COUNT(*) FROM v_stock_bajo_minimo
                ) AS articulos_bajo_minimo,
                (
                    SELECT COUNT(*)
                    FROM ordenes_fabricacion
                    WHERE estado IN ('confirmada','en_proceso')
                ) AS ordenes_fab_activas,
                (
                    SELECT COUNT(*)
                    FROM cobros
                    WHERE estado IN ('pendiente','parcial')
                ) AS num_cobros_pendientes,
                (
                    SELECT COALESCE(SUM(total_factura), 0)
                    FROM ventas_documentos
                    WHERE tipo = 'factura'
                      AND estado NOT IN ('anulado','borrador')
                      AND EXTRACT(YEAR FROM fecha) = EXTRACT(YEAR FROM CURRENT_DATE)
                ) AS ventas_anio,
                (
                    SELECT COALESCE(SUM(
                        vd.total_factura -
                        COALESCE((
                            SELECT SUM(vl.cantidad * vl.precio_unitario * (1 - vl.descuento_pct/100) *
                                (a.precio_coste / NULLIF(vl.precio_unitario, 0)))
                            FROM ventas_lineas vl
                            JOIN articulos a ON a.id = vl.articulo_id
                            WHERE vl.documento_id = vd.id
                        ), 0)
                    ), 0)
                    FROM ventas_documentos vd
                    WHERE vd.tipo = 'factura'
                      AND vd.estado NOT IN ('anulado','borrador')
                      AND DATE_TRUNC('month', vd.fecha) = DATE_TRUNC('month', CURRENT_DATE)
                ) AS beneficio_estimado_mes;
        SQL);

        // ── ÍNDICES FTS (Full Text Search) ────────────────────────────────────
        /*
		DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_clientes_fts
            ON clientes USING gin(to_tsvector('spanish', unaccent(nombre)));
        SQL);

        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_articulos_fts
            ON articulos USING gin(to_tsvector('spanish', unaccent(descripcion)));
        SQL);
		*/
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_dashboard_kpis');
        DB::statement('DROP VIEW IF EXISTS v_cobros_pendientes');
        DB::statement('DROP VIEW IF EXISTS v_stock_bajo_minimo');
        DB::statement('DROP TRIGGER IF EXISTS trg_update_saldo_cliente ON cobros');
        DB::statement('DROP FUNCTION IF EXISTS fn_update_saldo_cliente');
        DB::statement('DROP TRIGGER IF EXISTS trg_update_stock_total ON stock_almacen');
        DB::statement('DROP FUNCTION IF EXISTS fn_update_stock_total');
        //DB::statement('DROP INDEX IF EXISTS idx_articulos_fts');
        //DB::statement('DROP INDEX IF EXISTS idx_clientes_fts');
    }
};

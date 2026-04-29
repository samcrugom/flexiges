<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VentasSeeder extends Seeder
{
    public function run(): void
    {
        $fp30  = DB::table('formas_pago')->where('codigo', '30D')->value('id');
        $fp60  = DB::table('formas_pago')->where('codigo', '60D')->value('id');
        $fpCont= DB::table('formas_pago')->where('codigo', 'CONT')->value('id');
        $iva21 = DB::table('tipos_iva')->where('codigo', 'IVA21')->value('id');
        $alm1  = DB::table('almacenes')->where('codigo', 'ALM1')->value('id');
        $vV01  = DB::table('vendedores')->where('codigo', 'V01')->value('id');
        $vV02  = DB::table('vendedores')->where('codigo', 'V02')->value('id');
        $userId= DB::table('users')->where('email', 'admin@efgnext.es')->value('id');

        $cli   = fn($c) => DB::table('clientes')->where('codigo', $c)->value('id');
        $art   = fn($r) => DB::table('articulos')->where('referencia', $r)->value('id');

        // ── HELPERS PARA CÁLCULO ──────────────────────────────────────────────
        $calcLinea = function(float $cant, float $precio, float $dto): array {
            $bruto  = round($cant * $precio, 4);
            $desc   = round($bruto * $dto / 100, 4);
            $neto   = round($bruto - $desc, 2);
            $iva    = round($neto * 0.21, 2);
            return ['neto' => $neto, 'iva' => $iva];
        };

        $calcRecargo = fn(float $base): float => round($base * 0.052, 2);

        // ── FACTURA F-026223 ──────────────────────────────────────────────────
        // Cliente 0001 (Normal), 30 días
        $f1Lineas = [
            ['ref' => 'HI001', 'desc' => 'Hilo poliéster 40 blanco', 'cant' => 30, 'precio' => 3.90, 'dto' => 5.0],
            ['ref' => 'CR001', 'desc' => 'Cremallera invisible 20cm blanca', 'cant' => 100, 'precio' => 0.72, 'dto' => 0.0],
        ];

        $base1 = 0; $iva1 = 0;
        foreach ($f1Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $base1 += $c['neto'];
            $iva1  += $c['iva'];
        }
        $total1 = round($base1 + $iva1, 2);

        $docId1 = DB::table('ventas_documentos')->insertGetId([
            'tipo'             => 'factura',
            'numero'           => 'F-026223',
            'serie'            => 'F-',
            'fecha'            => '2026-05-26',
            'cliente_id'       => $cli('0001'),
            'cliente_nombre'   => 'Almacenes Guimerá SA',
            'cliente_nif'      => 'A-28123456',
            'cliente_direccion'=> 'Plaza Pontejos 2, 28012 Madrid',
            'vendedor_id'      => $vV01,
            'forma_pago_id'    => $fp30,
            'almacen_id'       => $alm1,
            'base_imponible'   => $base1,
            'cuota_iva'        => $iva1,
            'cuota_recargo'    => 0,
            'total_factura'    => $total1,
            'tipo_cliente'     => 'N',
            'estado'           => 'cobrada',
            'fecha_vencimiento'=> '2026-06-25',
            'user_id'          => $userId,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $linea = 1;
        foreach ($f1Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            DB::table('ventas_lineas')->insert([
                'documento_id'   => $docId1,
                'linea'          => $linea++,
                'tipo_linea'     => 'A',
                'articulo_id'    => $art($l['ref']),
                'descripcion'    => $l['desc'],
                'cantidad'       => $l['cant'],
                'precio_unitario'=> $l['precio'],
                'descuento_pct'  => $l['dto'],
                'importe_neto'   => $c['neto'],
                'tipo_iva_id'    => $iva21,
                'cuota_iva'      => $c['iva'],
                'cuota_recargo'  => 0,
                'almacen_id'     => $alm1,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        DB::table('ventas_iva_detalle')->insert([
            'documento_id'   => $docId1,
            'tipo_iva_id'    => $iva21,
            'base_imponible' => $base1,
            'porcentaje_iva' => 21,
            'cuota_iva'      => $iva1,
            'porcentaje_rec' => 0,
            'cuota_recargo'  => 0,
        ]);

        // Cobro cobrado
        DB::table('cobros')->insert([
            'factura_id'        => $docId1,
            'cliente_id'        => $cli('0001'),
            'numero'            => 'COB-001',
            'fecha_emision'     => '2026-05-26',
            'fecha_vencimiento' => '2026-06-25',
            'importe_total'     => $total1,
            'importe_cobrado'   => $total1,
            'tipo_cobro'        => 'transferencia',
            'estado'            => 'cobrado',
            'notas'             => 'Cobrado mediante transferencia',
            'user_id'           => $userId,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ── FACTURA F-026224 ──────────────────────────────────────────────────
        // Cliente 0004 (Normal), contado — pendiente
        $f2Lineas = [
            ['ref' => 'HA001', 'desc' => 'Hilo algodón egipcio 50g blanco', 'cant' => 20, 'precio' => 2.40, 'dto' => 0.0],
        ];

        $base2 = 0; $iva2 = 0;
        foreach ($f2Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $base2 += $c['neto']; $iva2 += $c['iva'];
        }
        $total2 = round($base2 + $iva2, 2);

        $docId2 = DB::table('ventas_documentos')->insertGetId([
            'tipo'             => 'factura',
            'numero'           => 'F-026224',
            'serie'            => 'F-',
            'fecha'            => '2026-05-26',
            'cliente_id'       => $cli('0004'),
            'cliente_nombre'   => 'Rosa Nieves Vergara López',
            'cliente_nif'      => '28456789K',
            'vendedor_id'      => $vV02,
            'forma_pago_id'    => $fpCont,
            'almacen_id'       => $alm1,
            'base_imponible'   => $base2,
            'cuota_iva'        => $iva2,
            'cuota_recargo'    => 0,
            'total_factura'    => $total2,
            'tipo_cliente'     => 'N',
            'estado'           => 'enviada',
            'fecha_vencimiento'=> '2026-05-26',
            'user_id'          => $userId,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $linea = 1;
        foreach ($f2Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            DB::table('ventas_lineas')->insert([
                'documento_id'   => $docId2, 'linea' => $linea++,
                'tipo_linea'     => 'A', 'articulo_id' => $art($l['ref']),
                'descripcion'    => $l['desc'], 'cantidad' => $l['cant'],
                'precio_unitario'=> $l['precio'], 'descuento_pct' => $l['dto'],
                'importe_neto'   => $c['neto'], 'tipo_iva_id' => $iva21,
                'cuota_iva'      => $c['iva'], 'cuota_recargo' => 0,
                'almacen_id'     => $alm1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('ventas_iva_detalle')->insert([
            'documento_id' => $docId2, 'tipo_iva_id' => $iva21,
            'base_imponible' => $base2, 'porcentaje_iva' => 21,
            'cuota_iva' => $iva2, 'porcentaje_rec' => 0, 'cuota_recargo' => 0,
        ]);

        DB::table('cobros')->insert([
            'factura_id' => $docId2, 'cliente_id' => $cli('0004'),
            'numero' => 'COB-002', 'fecha_emision' => '2026-05-26',
            'fecha_vencimiento' => '2026-05-26',
            'importe_total' => $total2, 'importe_cobrado' => 0,
            'tipo_cobro' => 'efectivo', 'estado' => 'pendiente',
            'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── FACTURA F-026231 ──────────────────────────────────────────────────
        // Cliente 0003 (Recargo Equivalencia), 30 días
        $f3Lineas = [
            ['ref' => 'HI001', 'desc' => 'Hilo poliéster 40 blanco',      'cant' => 200, 'precio' => 3.50, 'dto' => 3.0],
            ['ref' => 'HI002', 'desc' => 'Hilo poliéster 40 negro',       'cant' => 150, 'precio' => 3.50, 'dto' => 3.0],
            ['ref' => 'CM001', 'desc' => 'Cremallera metálica 40cm dorada','cant' => 200, 'precio' => 1.20, 'dto' => 0.0],
        ];

        $base3 = 0; $iva3 = 0; $rec3 = 0;
        foreach ($f3Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $base3 += $c['neto'];
            $iva3  += $c['iva'];
            $rec3  += $calcRecargo($c['neto']);
        }
        $total3 = round($base3 + $iva3 + $rec3, 2);

        $docId3 = DB::table('ventas_documentos')->insertGetId([
            'tipo'             => 'factura',
            'numero'           => 'F-026231',
            'serie'            => 'F-',
            'fecha'            => '2026-06-01',
            'cliente_id'       => $cli('0003'),
            'cliente_nombre'   => 'Servicio de Mercería y Textil SLL',
            'cliente_nif'      => 'B-29887654',
            'vendedor_id'      => $vV01,
            'forma_pago_id'    => $fp30,
            'almacen_id'       => $alm1,
            'base_imponible'   => $base3,
            'cuota_iva'        => $iva3,
            'cuota_recargo'    => $rec3,
            'total_factura'    => $total3,
            'tipo_cliente'     => 'R',
            'estado'           => 'enviada',
            'fecha_vencimiento'=> '2026-07-01',
            'notas'            => 'Entrega urgente antes del 05/06.',
            'user_id'          => $userId,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $linea = 1;
        foreach ($f3Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $r = $calcRecargo($c['neto']);
            DB::table('ventas_lineas')->insert([
                'documento_id' => $docId3, 'linea' => $linea++,
                'tipo_linea' => 'A', 'articulo_id' => $art($l['ref']),
                'descripcion' => $l['desc'], 'cantidad' => $l['cant'],
                'precio_unitario' => $l['precio'], 'descuento_pct' => $l['dto'],
                'importe_neto' => $c['neto'], 'tipo_iva_id' => $iva21,
                'cuota_iva' => $c['iva'], 'cuota_recargo' => $r,
                'almacen_id' => $alm1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('ventas_iva_detalle')->insert([
            'documento_id' => $docId3, 'tipo_iva_id' => $iva21,
            'base_imponible' => $base3, 'porcentaje_iva' => 21,
            'cuota_iva' => $iva3, 'porcentaje_rec' => 5.2,
            'cuota_recargo' => $rec3,
        ]);

        DB::table('cobros')->insert([
            'factura_id' => $docId3, 'cliente_id' => $cli('0003'),
            'numero' => 'COB-003', 'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-07-01',
            'importe_total' => $total3, 'importe_cobrado' => 0,
            'tipo_cobro' => 'transferencia', 'estado' => 'pendiente',
            'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── FACTURA F-026241 ──────────────────────────────────────────────────
        // Cliente 0002 (Recargo Equivalencia), 60 días
        $f4Lineas = [
            ['ref' => 'HI001', 'desc' => 'Hilo poliéster 40 blanco',   'cant' => 50,   'precio' => 3.90, 'dto' => 5.0],
            ['ref' => 'ET001', 'desc' => 'Etiqueta tejida marca 50x20', 'cant' => 1000, 'precio' => 0.09, 'dto' => 0.0],
        ];

        $base4 = 0; $iva4 = 0; $rec4 = 0;
        foreach ($f4Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $base4 += $c['neto'];
            $iva4  += $c['iva'];
            $rec4  += $calcRecargo($c['neto']);
        }
        $total4 = round($base4 + $iva4 + $rec4, 2);

        $docId4 = DB::table('ventas_documentos')->insertGetId([
            'tipo'             => 'factura',
            'numero'           => 'F-026241',
            'serie'            => 'F-',
            'fecha'            => '2026-06-01',
            'cliente_id'       => $cli('0002'),
            'cliente_nombre'   => 'Salas Textil SL',
            'cliente_nif'      => 'B-41123789',
            'vendedor_id'      => $vV02,
            'forma_pago_id'    => $fp60,
            'almacen_id'       => $alm1,
            'base_imponible'   => $base4,
            'cuota_iva'        => $iva4,
            'cuota_recargo'    => $rec4,
            'total_factura'    => $total4,
            'tipo_cliente'     => 'R',
            'estado'           => 'enviada',
            'fecha_vencimiento'=> '2026-08-01',
            'user_id'          => $userId,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $linea = 1;
        foreach ($f4Lineas as $l) {
            $c = $calcLinea($l['cant'], $l['precio'], $l['dto']);
            $r = $calcRecargo($c['neto']);
            DB::table('ventas_lineas')->insert([
                'documento_id' => $docId4, 'linea' => $linea++,
                'tipo_linea' => 'A', 'articulo_id' => $art($l['ref']),
                'descripcion' => $l['desc'], 'cantidad' => $l['cant'],
                'precio_unitario' => $l['precio'], 'descuento_pct' => $l['dto'],
                'importe_neto' => $c['neto'], 'tipo_iva_id' => $iva21,
                'cuota_iva' => $c['iva'], 'cuota_recargo' => $r,
                'almacen_id' => $alm1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('ventas_iva_detalle')->insert([
            'documento_id' => $docId4, 'tipo_iva_id' => $iva21,
            'base_imponible' => $base4, 'porcentaje_iva' => 21,
            'cuota_iva' => $iva4, 'porcentaje_rec' => 5.2,
            'cuota_recargo' => $rec4,
        ]);

        DB::table('cobros')->insert([
            'factura_id' => $docId4, 'cliente_id' => $cli('0002'),
            'numero' => 'COB-004', 'fecha_emision' => '2026-06-01',
            'fecha_vencimiento' => '2026-08-01',
            'importe_total' => $total4, 'importe_cobrado' => 0,
            'tipo_cobro' => 'recibo', 'estado' => 'pendiente',
            'user_id' => $userId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── MOVIMIENTOS DE ALMACÉN HISTÓRICOS ────────────────────────────────
        $movs = [
            ['fecha' => '2026-05-15', 'ref' => 'HI001', 'tipo' => 'ENTRADA',     'cant' =>  200, 'doc_tipo' => 'PEDIDO_COMPRA',  'doc_ref' => 'PED-001', 'nota' => 'Pedido Sevimer P001'],
            ['fecha' => '2026-05-26', 'ref' => 'HI001', 'tipo' => 'SALIDA',      'cant' =>  -30, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026223','nota' => 'Factura F-026223'],
            ['fecha' => '2026-05-26', 'ref' => 'CR001', 'tipo' => 'SALIDA',      'cant' => -100, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026223','nota' => 'Factura F-026223'],
            ['fecha' => '2026-06-01', 'ref' => 'HI001', 'tipo' => 'SALIDA',      'cant' =>  -50, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026241','nota' => 'Factura F-026241'],
            ['fecha' => '2026-06-01', 'ref' => 'ET001', 'tipo' => 'SALIDA',      'cant' => -1000,'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026241','nota' => 'Factura F-026241'],
            ['fecha' => '2026-06-01', 'ref' => 'HI001', 'tipo' => 'SALIDA',      'cant' => -200, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026231','nota' => 'Factura F-026231'],
            ['fecha' => '2026-06-01', 'ref' => 'HI002', 'tipo' => 'SALIDA',      'cant' => -150, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026231','nota' => 'Factura F-026231'],
            ['fecha' => '2026-06-01', 'ref' => 'CM001', 'tipo' => 'SALIDA',      'cant' => -200, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026231','nota' => 'Factura F-026231'],
            ['fecha' => '2026-05-26', 'ref' => 'HA001', 'tipo' => 'SALIDA',      'cant' =>  -20, 'doc_tipo' => 'FACTURA_VTA',    'doc_ref' => 'F-026224','nota' => 'Factura F-026224'],
        ];

        foreach ($movs as $m) {
            $artId = DB::table('articulos')->where('referencia', $m['ref'])->value('id');
            $stockActual = DB::table('stock_almacen')
                ->where('articulo_id', $artId)
                ->where('almacen_id', $alm1)
                ->value('stock') ?? 0;

            DB::table('movimientos_almacen')->insert([
                'fecha'           => $m['fecha'],
                'articulo_id'     => $artId,
                'almacen_id'      => $alm1,
                'tipo_movimiento' => $m['tipo'],
                'cantidad'        => $m['cant'],
                'stock_anterior'  => $stockActual,
                'stock_posterior' => $stockActual + $m['cant'],
                'precio_coste'    => DB::table('articulos')->where('id', $artId)->value('precio_coste') ?? 0,
                'documento_tipo'  => $m['doc_tipo'],
                'documento_ref'   => $m['doc_ref'],
                'observaciones'   => $m['nota'],
                'user_id'         => $userId,
                'created_at'      => now(),
            ]);
        }

        // ── PEDIDO DE COMPRA EJEMPLO ──────────────────────────────────────────
        $prov1 = DB::table('proveedores')->where('codigo', 'P001')->value('id');
        $prov3 = DB::table('proveedores')->where('codigo', 'P003')->value('id');

        $pedId1 = DB::table('pedidos_compra')->insertGetId([
            'numero'       => 'PED-001',
            'proveedor_id' => $prov1,
            'fecha'        => '2026-05-10',
            'fecha_prevista'=> '2026-05-17',
            'forma_pago_id'=> $fp60,
            'almacen_id'   => $alm1,
            'estado'       => 'recibido',
            'base_imponible'=> 440.00,
            'cuota_iva'    => 92.40,
            'total'        => 532.40,
            'user_id'      => $userId,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        DB::table('pedidos_compra_lineas')->insert([
            'pedido_id'       => $pedId1, 'linea' => 1,
            'articulo_id'     => $art('HI001'), 'descripcion' => 'Hilo poliéster 40 blanco',
            'cantidad'        => 200, 'cantidad_recibida' => 200,
            'precio'          => 2.20, 'descuento_pct' => 0,
            'importe_neto'    => 440.00, 'tipo_iva_id' => $iva21,
            'almacen_id'      => $alm1,
            'created_at'      => now(), 'updated_at' => now(),
        ]);

        $pedId2 = DB::table('pedidos_compra')->insertGetId([
            'numero'       => 'PED-002',
            'proveedor_id' => $prov3,
            'fecha'        => '2026-06-01',
            'fecha_prevista'=> '2026-06-11',
            'forma_pago_id'=> $fp60,
            'almacen_id'   => $alm1,
            'estado'       => 'confirmado',
            'base_imponible'=> 315.00,
            'cuota_iva'    => 66.15,
            'total'        => 381.15,
            'notas'        => 'Pedir también CM002 por bajo stock.',
            'user_id'      => $userId,
            'created_at'   => now(), 'updated_at' => now(),
        ]);

        DB::table('pedidos_compra_lineas')->insert([
            [
                'pedido_id' => $pedId2, 'linea' => 1,
                'articulo_id' => $art('CR001'), 'descripcion' => 'Cremallera invisible 20cm blanca',
                'cantidad' => 500, 'cantidad_recibida' => 0,
                'precio' => 0.35, 'descuento_pct' => 0,
                'importe_neto' => 175.00, 'tipo_iva_id' => $iva21,
                'almacen_id' => $alm1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'pedido_id' => $pedId2, 'linea' => 2,
                'articulo_id' => $art('CM001'), 'descripcion' => 'Cremallera metálica 40cm dorada',
                'cantidad' => 200, 'cantidad_recibida' => 0,
                'precio' => 0.70, 'descuento_pct' => 0,
                'importe_neto' => 140.00, 'tipo_iva_id' => $iva21,
                'almacen_id' => $alm1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }
}

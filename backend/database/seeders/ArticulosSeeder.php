<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticulosSeeder extends Seeder
{
    public function run(): void
    {
        $iva21 = DB::table('tipos_iva')->where('codigo', 'IVA21')->value('id');

        $fHilPol = DB::table('familias_articulo')->where('codigo', 'HIL-POL')->value('id');
        $fHilAlg = DB::table('familias_articulo')->where('codigo', 'HIL-ALG')->value('id');
        $fCreInv = DB::table('familias_articulo')->where('codigo', 'CRE-INV')->value('id');
        $fCreMet = DB::table('familias_articulo')->where('codigo', 'CRE-MET')->value('id');
        $fEtiTej = DB::table('familias_articulo')->where('codigo', 'ETI-TEJ')->value('id');
        $fEtiImp = DB::table('familias_articulo')->where('codigo', 'ETI-IMP')->value('id');
        $fEmb    = DB::table('familias_articulo')->where('codigo', 'EMB')->value('id');
        $fFab    = DB::table('familias_articulo')->where('codigo', 'FAB')->value('id');
        $fKit    = DB::table('familias_articulo')->where('codigo', 'KIT')->value('id');

        $pSev  = DB::table('proveedores')->where('codigo', 'P001')->value('id');
        $pHil  = DB::table('proveedores')->where('codigo', 'P002')->value('id');
        $pCre  = DB::table('proveedores')->where('codigo', 'P003')->value('id');
        $pEti  = DB::table('proveedores')->where('codigo', 'P004')->value('id');
        $pEmb  = DB::table('proveedores')->where('codigo', 'P005')->value('id');

        $alm1 = DB::table('almacenes')->where('codigo', 'ALM1')->value('id');
        $almFab = DB::table('almacenes')->where('codigo', 'FAB')->value('id');

        $articulos = [
            // ── HILOS POLIÉSTER ───────────────────────────────────────────────
            [
                'referencia'    => 'HI001',
                'descripcion'   => 'Hilo poliéster 40 blanco',
                'familia_id'    => $fHilPol,
                'proveedor_id'  => $pSev,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'KG',
                'ean13'         => '8411231001001',
                'precio_coste'  => 2.2000,
                'tarifa1'       => 4.5000,
                'tarifa2'       => 3.9000,
                'tarifa3'       => 3.5000,
                'tarifa4'       => 3.1000,
                'stock_total'   => 145.5000,
                'stock_minimo'  => 20.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'HI002',
                'descripcion'   => 'Hilo poliéster 40 negro',
                'familia_id'    => $fHilPol,
                'proveedor_id'  => $pSev,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'KG',
                'ean13'         => '8411231001002',
                'precio_coste'  => 2.2000,
                'tarifa1'       => 4.5000,
                'tarifa2'       => 3.9000,
                'tarifa3'       => 3.5000,
                'tarifa4'       => 3.1000,
                'stock_total'   => 98.2000,
                'stock_minimo'  => 20.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'HI003',
                'descripcion'   => 'Hilo poliéster 40 rojo',
                'familia_id'    => $fHilPol,
                'proveedor_id'  => $pSev,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'KG',
                'ean13'         => '8411231001003',
                'precio_coste'  => 2.2000,
                'tarifa1'       => 4.5000,
                'tarifa2'       => 3.9000,
                'tarifa3'       => 3.5000,
                'tarifa4'       => 3.1000,
                'stock_total'   => 55.0000,
                'stock_minimo'  => 15.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            // ── HILOS ALGODÓN ─────────────────────────────────────────────────
            [
                'referencia'    => 'HA001',
                'descripcion'   => 'Hilo algodón egipcio 50g blanco',
                'familia_id'    => $fHilAlg,
                'proveedor_id'  => $pHil,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231002001',
                'precio_coste'  => 1.3000,
                'tarifa1'       => 2.8000,
                'tarifa2'       => 2.4000,
                'tarifa3'       => 2.1000,
                'tarifa4'       => 1.9000,
                'stock_total'   => 520.0000,
                'stock_minimo'  => 100.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'HA002',
                'descripcion'   => 'Hilo algodón egipcio 50g negro',
                'familia_id'    => $fHilAlg,
                'proveedor_id'  => $pHil,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231002002',
                'precio_coste'  => 1.3000,
                'tarifa1'       => 2.8000,
                'tarifa2'       => 2.4000,
                'tarifa3'       => 2.1000,
                'tarifa4'       => 1.9000,
                'stock_total'   => 310.0000,
                'stock_minimo'  => 100.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            // ── CREMALLERAS INVISIBLES ─────────────────────────────────────────
            [
                'referencia'    => 'CR001',
                'descripcion'   => 'Cremallera invisible 20cm blanca',
                'familia_id'    => $fCreInv,
                'proveedor_id'  => $pCre,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231003001',
                'precio_coste'  => 0.3500,
                'tarifa1'       => 0.8500,
                'tarifa2'       => 0.7200,
                'tarifa3'       => 0.6500,
                'tarifa4'       => 0.5800,
                'stock_total'   => 1240.0000,
                'stock_minimo'  => 200.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'CR002',
                'descripcion'   => 'Cremallera invisible 20cm negra',
                'familia_id'    => $fCreInv,
                'proveedor_id'  => $pCre,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231003002',
                'precio_coste'  => 0.3500,
                'tarifa1'       => 0.8500,
                'tarifa2'       => 0.7200,
                'tarifa3'       => 0.6500,
                'tarifa4'       => 0.5800,
                'stock_total'   => 880.0000,
                'stock_minimo'  => 200.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            // ── CREMALLERAS METÁLICAS ─────────────────────────────────────────
            [
                'referencia'    => 'CM001',
                'descripcion'   => 'Cremallera metálica 40cm dorada',
                'familia_id'    => $fCreMet,
                'proveedor_id'  => $pCre,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231004001',
                'precio_coste'  => 0.7000,
                'tarifa1'       => 1.6000,
                'tarifa2'       => 1.3500,
                'tarifa3'       => 1.2000,
                'tarifa4'       => 1.0500,
                'stock_total'   => 387.0000,
                'stock_minimo'  => 100.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'CM002',
                'descripcion'   => 'Cremallera metálica 40cm plateada',
                'familia_id'    => $fCreMet,
                'proveedor_id'  => $pCre,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231004002',
                'precio_coste'  => 0.7000,
                'tarifa1'       => 1.6000,
                'tarifa2'       => 1.3500,
                'tarifa3'       => 1.2000,
                'tarifa4'       => 1.0500,
                'stock_total'   => 12.0000,
                'stock_minimo'  => 100.0000,
                'es_fabricado'  => false,
                'activo'        => true,
                'observaciones' => 'BAJO STOCK — pendiente pedido a Cremacor',
            ],
            // ── ETIQUETAS ─────────────────────────────────────────────────────
            [
                'referencia'    => 'ET001',
                'descripcion'   => 'Etiqueta tejida marca 50x20mm',
                'familia_id'    => $fEtiTej,
                'proveedor_id'  => $pEti,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231005001',
                'precio_coste'  => 0.0400,
                'tarifa1'       => 0.1200,
                'tarifa2'       => 0.1000,
                'tarifa3'       => 0.0900,
                'tarifa4'       => 0.0800,
                'stock_total'   => 8500.0000,
                'stock_minimo'  => 1000.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'ET002',
                'descripcion'   => 'Etiqueta impresa código barras 40x25mm',
                'familia_id'    => $fEtiImp,
                'proveedor_id'  => $pEti,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231005002',
                'precio_coste'  => 0.0200,
                'tarifa1'       => 0.0600,
                'tarifa2'       => 0.0500,
                'tarifa3'       => 0.0450,
                'tarifa4'       => 0.0400,
                'stock_total'   => 4200.0000,
                'stock_minimo'  => 500.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            // ── EMBALAJE ──────────────────────────────────────────────────────
            [
                'referencia'    => 'CA001',
                'descripcion'   => 'Caja cartón 40x30x20cm',
                'familia_id'    => $fEmb,
                'proveedor_id'  => $pEmb,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231006001',
                'precio_coste'  => 0.5500,
                'tarifa1'       => 1.2000,
                'tarifa2'       => 1.0000,
                'tarifa3'       => 0.9000,
                'tarifa4'       => 0.8000,
                'stock_total'   => 340.0000,
                'stock_minimo'  => 50.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            [
                'referencia'    => 'CA002',
                'descripcion'   => 'Caja cartón 20x15x10cm',
                'familia_id'    => $fEmb,
                'proveedor_id'  => $pEmb,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231006002',
                'precio_coste'  => 0.3000,
                'tarifa1'       => 0.7000,
                'tarifa2'       => 0.6000,
                'tarifa3'       => 0.5500,
                'tarifa4'       => 0.5000,
                'stock_total'   => 0.0000,
                'stock_minimo'  => 50.0000,
                'es_fabricado'  => false,
                'activo'        => true,
            ],
            // ── PRODUCTOS FABRICADOS ──────────────────────────────────────────
            [
                'referencia'    => 'BO001',
                'descripcion'   => 'Bobina hilo 500m multicolor',
                'familia_id'    => $fFab,
                'proveedor_id'  => null,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231007001',
                'precio_coste'  => 2.8000,
                'tarifa1'       => 5.9000,
                'tarifa2'       => 5.2000,
                'tarifa3'       => 4.8000,
                'tarifa4'       => 4.5000,
                'stock_total'   => 0.0000,
                'stock_minimo'  => 20.0000,
                'es_fabricado'  => true,
                'activo'        => true,
                'observaciones' => 'Producto fabricado. Ver fórmula FOR001.',
            ],
            [
                'referencia'    => 'KI001',
                'descripcion'   => 'Kit costura viaje completo',
                'familia_id'    => $fKit,
                'proveedor_id'  => null,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231008001',
                'precio_coste'  => 5.5000,
                'tarifa1'       => 12.5000,
                'tarifa2'       => 11.0000,
                'tarifa3'       => 9.8000,
                'tarifa4'       => 8.9000,
                'stock_total'   => 0.0000,
                'stock_minimo'  => 10.0000,
                'es_fabricado'  => true,
                'activo'        => true,
                'observaciones' => 'Producto fabricado. Ver fórmula FOR002.',
            ],
            [
                'referencia'    => 'CJ-BOB',
                'descripcion'   => 'Caja expositor 10 bobinas multicolor',
                'familia_id'    => $fFab,
                'proveedor_id'  => null,
                'tipo_iva_id'   => $iva21,
                'unidad_medida' => 'UD',
                'ean13'         => '8411231009001',
                'precio_coste'  => 32.0000,
                'tarifa1'       => 65.0000,
                'tarifa2'       => 58.0000,
                'tarifa3'       => 52.0000,
                'tarifa4'       => 48.0000,
                'stock_total'   => 0.0000,
                'stock_minimo'  => 5.0000,
                'es_fabricado'  => true,
                'activo'        => true,
                'observaciones' => 'Producto fabricado. Ver fórmula FOR003.',
            ],
        ];

        foreach ($articulos as $art) {
            DB::table('articulos')->updateOrInsert(
                ['referencia' => $art['referencia']],
                array_merge($art, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── STOCK INICIAL POR ALMACÉN ─────────────────────────────────────────
        // Los artículos comprados van al almacén principal ALM1
        $stockAlm1 = [
            'HI001' => 145.5,
            'HI002' => 98.2,
            'HI003' => 55.0,
            'HA001' => 520.0,
            'HA002' => 310.0,
            'CR001' => 1240.0,
            'CR002' => 880.0,
            'CM001' => 387.0,
            'CM002' => 12.0,
            'ET001' => 8500.0,
            'ET002' => 4200.0,
            'CA001' => 340.0,
            'CA002' => 0.0,
        ];

        foreach ($stockAlm1 as $ref => $qty) {
            $artId = DB::table('articulos')->where('referencia', $ref)->value('id');
            if ($artId) {
                DB::table('stock_almacen')->updateOrInsert(
                    ['articulo_id' => $artId, 'almacen_id' => $alm1],
                    ['stock' => $qty, 'stock_reservado' => 0, 'stock_pedido' => 0, 'updated_at' => now()]
                );
            }
        }

        // Los fabricados van a almacén FAB con stock 0
        $fabricados = ['BO001', 'KI001', 'CJ-BOB'];
        foreach ($fabricados as $ref) {
            $artId = DB::table('articulos')->where('referencia', $ref)->value('id');
            if ($artId) {
                DB::table('stock_almacen')->updateOrInsert(
                    ['articulo_id' => $artId, 'almacen_id' => $almFab],
                    ['stock' => 0, 'stock_reservado' => 0, 'stock_pedido' => 0, 'updated_at' => now()]
                );
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaestrosSeeder extends Seeder
{
    public function run(): void
    {
        // ── FORMAS DE PAGO ────────────────────────────────────────────────────
        $formasPago = [
            ['codigo' => 'CONT',  'nombre' => 'Contado',              'tipo' => 'efectivo',      'dias_vencimiento' => 0,  'num_vencimientos' => 1],
            ['codigo' => '30D',   'nombre' => '30 días',              'tipo' => 'transferencia', 'dias_vencimiento' => 30, 'num_vencimientos' => 1],
            ['codigo' => '60D',   'nombre' => '60 días',              'tipo' => 'transferencia', 'dias_vencimiento' => 60, 'num_vencimientos' => 1],
            ['codigo' => '90D',   'nombre' => '90 días',              'tipo' => 'transferencia', 'dias_vencimiento' => 90, 'num_vencimientos' => 1],
            ['codigo' => '30-60', 'nombre' => '30/60 días',           'tipo' => 'recibo',        'dias_vencimiento' => 30, 'num_vencimientos' => 2],
            ['codigo' => 'REC30', 'nombre' => 'Recibo 30 días',       'tipo' => 'recibo',        'dias_vencimiento' => 30, 'num_vencimientos' => 1],
            ['codigo' => 'REC60', 'nombre' => 'Recibo 60 días',       'tipo' => 'recibo',        'dias_vencimiento' => 60, 'num_vencimientos' => 1],
            ['codigo' => 'TRANS', 'nombre' => 'Transferencia 15 días','tipo' => 'transferencia', 'dias_vencimiento' => 15, 'num_vencimientos' => 1],
            ['codigo' => 'CREM',  'nombre' => 'Contra reembolso',     'tipo' => 'efectivo',      'dias_vencimiento' => 0,  'num_vencimientos' => 1],
            ['codigo' => 'TARJ',  'nombre' => 'Tarjeta',              'tipo' => 'tarjeta',       'dias_vencimiento' => 0,  'num_vencimientos' => 1],
        ];

        foreach ($formasPago as $fp) {
            DB::table('formas_pago')->updateOrInsert(
                ['codigo' => $fp['codigo']],
                array_merge($fp, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── TIPOS DE IVA ──────────────────────────────────────────────────────
        $tiposIva = [
            ['codigo' => 'IVA21', 'descripcion' => 'IVA General 21%',          'porcentaje_iva' => 21.00, 'porcentaje_rec' => 5.20],
            ['codigo' => 'IVA10', 'descripcion' => 'IVA Reducido 10%',         'porcentaje_iva' => 10.00, 'porcentaje_rec' => 1.40],
            ['codigo' => 'IVA4',  'descripcion' => 'IVA Superreducido 4%',     'porcentaje_iva' =>  4.00, 'porcentaje_rec' => 0.50],
            ['codigo' => 'IVA0',  'descripcion' => 'Exento IVA 0%',            'porcentaje_iva' =>  0.00, 'porcentaje_rec' => 0.00],
        ];

        foreach ($tiposIva as $ti) {
            DB::table('tipos_iva')->updateOrInsert(
                ['codigo' => $ti['codigo']],
                array_merge($ti, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── ZONAS ─────────────────────────────────────────────────────────────
        $zonas = [
            ['codigo' => 'AND', 'nombre' => 'Andalucía'],
            ['codigo' => 'CAT', 'nombre' => 'Cataluña'],
            ['codigo' => 'MAD', 'nombre' => 'Madrid y Centro'],
            ['codigo' => 'LEV', 'nombre' => 'Levante'],
            ['codigo' => 'NOR', 'nombre' => 'Norte'],
            ['codigo' => 'GAL', 'nombre' => 'Galicia'],
            ['codigo' => 'EXT', 'nombre' => 'Extremadura'],
            ['codigo' => 'EXP', 'nombre' => 'Exportación'],
        ];

        foreach ($zonas as $z) {
            DB::table('zonas')->updateOrInsert(
                ['codigo' => $z['codigo']],
                array_merge($z, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── SECTORES ──────────────────────────────────────────────────────────
        $sectores = [
            ['codigo' => 'MER',  'nombre' => 'Mercería'],
            ['codigo' => 'TEX',  'nombre' => 'Textil'],
            ['codigo' => 'MAY',  'nombre' => 'Mayorista'],
            ['codigo' => 'MIN',  'nombre' => 'Minorista'],
            ['codigo' => 'IND',  'nombre' => 'Industrial'],
            ['codigo' => 'MOD',  'nombre' => 'Moda y confección'],
        ];

        foreach ($sectores as $s) {
            DB::table('sectores')->updateOrInsert(
                ['codigo' => $s['codigo']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── DELEGACIONES ──────────────────────────────────────────────────────
        $delegaciones = [
            ['codigo' => 'CEN', 'nombre' => 'Central Sevilla',    'direccion' => 'Pol. Industrial Los Pinos, Nave 7, 41007 Sevilla'],
            ['codigo' => 'MAD', 'nombre' => 'Delegación Madrid',  'direccion' => 'C/ Industria 34, 28022 Madrid'],
            ['codigo' => 'BCN', 'nombre' => 'Delegación Barcelona','direccion' => 'C/ Aragón 201, 08011 Barcelona'],
        ];

        foreach ($delegaciones as $d) {
            DB::table('delegaciones')->updateOrInsert(
                ['codigo' => $d['codigo']],
                array_merge($d, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── RUTAS ─────────────────────────────────────────────────────────────
        $rutas = [
            ['codigo' => 'R01', 'nombre' => 'Ruta Sevilla Capital'],
            ['codigo' => 'R02', 'nombre' => 'Ruta Sevilla Provincia'],
            ['codigo' => 'R03', 'nombre' => 'Ruta Huelva/Cádiz'],
            ['codigo' => 'R04', 'nombre' => 'Ruta Málaga/Granada'],
            ['codigo' => 'R05', 'nombre' => 'Ruta Nacional'],
            ['codigo' => 'AGE', 'nombre' => 'Agencia de transporte'],
        ];

        foreach ($rutas as $r) {
            DB::table('rutas')->updateOrInsert(
                ['codigo' => $r['codigo']],
                array_merge($r, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── FAMILIAS DE ARTÍCULO ──────────────────────────────────────────────
        $familiasPadre = [
            ['codigo' => 'HIL',  'nombre' => 'Hilos',         'parent_id' => null, 'orden' => 1],
            ['codigo' => 'CRE',  'nombre' => 'Cremalleras',   'parent_id' => null, 'orden' => 2],
            ['codigo' => 'ETI',  'nombre' => 'Etiquetas',     'parent_id' => null, 'orden' => 3],
            ['codigo' => 'EMB',  'nombre' => 'Embalaje',      'parent_id' => null, 'orden' => 4],
            ['codigo' => 'FAB',  'nombre' => 'Fabricados',    'parent_id' => null, 'orden' => 5],
            ['codigo' => 'KIT',  'nombre' => 'Kits',          'parent_id' => null, 'orden' => 6],
            ['codigo' => 'ACC',  'nombre' => 'Accesorios',    'parent_id' => null, 'orden' => 7],
        ];

        foreach ($familiasPadre as $f) {
            DB::table('familias_articulo')->updateOrInsert(
                ['codigo' => $f['codigo']],
                array_merge($f, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Subfamilias
        $hilId  = DB::table('familias_articulo')->where('codigo', 'HIL')->value('id');
        $creId  = DB::table('familias_articulo')->where('codigo', 'CRE')->value('id');
        $etiId  = DB::table('familias_articulo')->where('codigo', 'ETI')->value('id');

        $subfamilias = [
            ['codigo' => 'HIL-POL', 'nombre' => 'Hilo Poliéster', 'parent_id' => $hilId, 'orden' => 1],
            ['codigo' => 'HIL-ALG', 'nombre' => 'Hilo Algodón',   'parent_id' => $hilId, 'orden' => 2],
            ['codigo' => 'HIL-SED', 'nombre' => 'Hilo Seda',      'parent_id' => $hilId, 'orden' => 3],
            ['codigo' => 'CRE-INV', 'nombre' => 'Cremallera Invisible', 'parent_id' => $creId, 'orden' => 1],
            ['codigo' => 'CRE-MET', 'nombre' => 'Cremallera Metálica',  'parent_id' => $creId, 'orden' => 2],
            ['codigo' => 'CRE-PLT', 'nombre' => 'Cremallera Plástico',  'parent_id' => $creId, 'orden' => 3],
            ['codigo' => 'ETI-TEJ', 'nombre' => 'Etiqueta Tejida',      'parent_id' => $etiId, 'orden' => 1],
            ['codigo' => 'ETI-IMP', 'nombre' => 'Etiqueta Impresa',     'parent_id' => $etiId, 'orden' => 2],
        ];

        foreach ($subfamilias as $sf) {
            DB::table('familias_articulo')->updateOrInsert(
                ['codigo' => $sf['codigo']],
                array_merge($sf, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── ALMACENES ─────────────────────────────────────────────────────────
        $almacenes = [
            ['codigo' => 'ALM1', 'nombre' => 'Almacén Principal Sevilla',   'es_principal' => true,  'activo' => true],
            ['codigo' => 'ALM2', 'nombre' => 'Almacén Secundario',          'es_principal' => false, 'activo' => true],
            ['codigo' => 'FAB',  'nombre' => 'Almacén Fabricación',         'es_principal' => false, 'activo' => true],
            ['codigo' => 'DEV',  'nombre' => 'Almacén Devoluciones',        'es_principal' => false, 'activo' => true],
        ];

        foreach ($almacenes as $a) {
            DB::table('almacenes')->updateOrInsert(
                ['codigo' => $a['codigo']],
                array_merge($a, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // ── VENDEDORES ────────────────────────────────────────────────────────
        $zAndId = DB::table('zonas')->where('codigo', 'AND')->value('id');
        $zMadId = DB::table('zonas')->where('codigo', 'MAD')->value('id');
        $userMaria = User::where('email', 'mgarcia@efgnext.es')->first();
        $userJuan  = User::where('email', 'jperez@efgnext.es')->first();

        $vendedores = [
            [
                'codigo'       => 'V01',
                'nombre'       => 'María García López',
                'nif'          => '12345678A',
                'email'        => 'mgarcia@efgnext.es',
                'telefono'     => '600 111 222',
                'zona_id'      => $zMadId,
                'user_id'      => $userMaria?->id,
                'comision_pct' => 3.50,
                'activo'       => true,
            ],
            [
                'codigo'       => 'V02',
                'nombre'       => 'Juan Pérez Ruiz',
                'nif'          => '87654321B',
                'email'        => 'jperez@efgnext.es',
                'telefono'     => '600 333 444',
                'zona_id'      => $zAndId,
                'user_id'      => $userJuan?->id,
                'comision_pct' => 4.00,
                'activo'       => true,
            ],
        ];

        foreach ($vendedores as $v) {
            DB::table('vendedores')->updateOrInsert(
                ['codigo' => $v['codigo']],
                array_merge($v, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FormulasSeeder extends Seeder
{
    public function run(): void
    {
        $alm1   = DB::table('almacenes')->where('codigo', 'ALM1')->value('id');
        $almFab = DB::table('almacenes')->where('codigo', 'FAB')->value('id');

        // Helper para obtener id de artículo
        $art = fn(string $ref) => DB::table('articulos')->where('referencia', $ref)->value('id');

        // ── FÓRMULA 1: Bobina hilo 500m multicolor ────────────────────────────
        $f1Id = DB::table('formulas')->insertGetId([
            'codigo'             => 'FOR001',
            'nombre'             => 'Bobina hilo 500m multicolor',
            'articulo_id'        => $art('BO001'),
            'cantidad_producida' => 1.0000,
            'almacen_salida_id'  => $alm1,
            'almacen_entrada_id' => $almFab,
            'tiempo_fabricacion' => 5,
            'activa'             => true,
            'notas'              => 'Mezcla de hilos blanco y negro. Enrollar en bobina de cartón.',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        DB::table('formula_componentes')->insert([
            ['formula_id' => $f1Id, 'articulo_id' => $art('HI001'), 'cantidad' => 0.2500, 'unidad' => 'KG', 'orden' => 1, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f1Id, 'articulo_id' => $art('HI002'), 'cantidad' => 0.2500, 'unidad' => 'KG', 'orden' => 2, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f1Id, 'articulo_id' => $art('ET001'), 'cantidad' => 1.0000, 'unidad' => 'UD', 'orden' => 3, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── FÓRMULA 2: Kit costura viaje completo ─────────────────────────────
        $f2Id = DB::table('formulas')->insertGetId([
            'codigo'             => 'FOR002',
            'nombre'             => 'Kit costura viaje completo',
            'articulo_id'        => $art('KI001'),
            'cantidad_producida' => 1.0000,
            'almacen_salida_id'  => $alm1,
            'almacen_entrada_id' => $almFab,
            'tiempo_fabricacion' => 10,
            'activa'             => true,
            'notas'              => 'Kit compuesto por 2 hilos algodón, 3 cremalleras invisibles, 2 etiquetas y caja pequeña.',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        DB::table('formula_componentes')->insert([
            ['formula_id' => $f2Id, 'articulo_id' => $art('HA001'), 'cantidad' => 2.0000, 'unidad' => 'UD', 'orden' => 1, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f2Id, 'articulo_id' => $art('HA002'), 'cantidad' => 1.0000, 'unidad' => 'UD', 'orden' => 2, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f2Id, 'articulo_id' => $art('CR001'), 'cantidad' => 3.0000, 'unidad' => 'UD', 'orden' => 3, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f2Id, 'articulo_id' => $art('ET001'), 'cantidad' => 2.0000, 'unidad' => 'UD', 'orden' => 4, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['formula_id' => $f2Id, 'articulo_id' => $art('CA002'), 'cantidad' => 1.0000, 'unidad' => 'UD', 'orden' => 5, 'merma_pct' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── FÓRMULA 3: Caja expositor 10 bobinas multicolor ───────────────────
        // Este es el ejemplo exacto del requisito: CAJA-BOBINAS
        $f3Id = DB::table('formulas')->insertGetId([
            'codigo'             => 'FOR003',
            'nombre'             => 'Caja expositor 10 bobinas multicolor',
            'articulo_id'        => $art('CJ-BOB'),
            'cantidad_producida' => 1.0000,
            'almacen_salida_id'  => $alm1,
            'almacen_entrada_id' => $almFab,
            'tiempo_fabricacion' => 20,
            'activa'             => true,
            'notas'              => 'Caja grande con 10 bobinas + 25m hilo extra + 2 etiquetas de expositor. Ejemplo canónico del requisito CAJA-BOBINAS.',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        DB::table('formula_componentes')->insert([
            // 1 caja cartón grande
            ['formula_id' => $f3Id, 'articulo_id' => $art('CA001'), 'cantidad' => 1.0000, 'unidad' => 'UD', 'orden' => 1, 'merma_pct' => 0,   'notas' => 'Caja exterior del expositor', 'created_at' => now(), 'updated_at' => now()],
            // 10 bobinas (el producto fabricado FOR001 puede usarse como componente)
            ['formula_id' => $f3Id, 'articulo_id' => $art('BO001'), 'cantidad' => 10.000, 'unidad' => 'UD', 'orden' => 2, 'merma_pct' => 0,   'notas' => '10 bobinas multicolor BO001', 'created_at' => now(), 'updated_at' => now()],
            // 25 metros hilo (0.05 KG aprox)
            ['formula_id' => $f3Id, 'articulo_id' => $art('HI001'), 'cantidad' => 0.0500, 'unidad' => 'KG', 'orden' => 3, 'merma_pct' => 2.0, 'notas' => '25m hilo de regalo', 'created_at' => now(), 'updated_at' => now()],
            // 2 etiquetas expositor
            ['formula_id' => $f3Id, 'articulo_id' => $art('ET001'), 'cantidad' => 2.0000, 'unidad' => 'UD', 'orden' => 4, 'merma_pct' => 0,   'notas' => 'Etiqueta frontal y trasera', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}

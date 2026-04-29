<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('empresa')->insert([
            'nombre'            => 'EFG Next Distribuciones SL',
            'nombre_comercial'  => 'EFG Next',
            'nif'               => 'B-12345678',
            'direccion'         => 'Polígono Industrial Los Pinos, Nave 7',
            'codigo_postal'     => '41007',
            'localidad'         => 'Sevilla',
            'provincia'         => 'Sevilla',
            'pais'              => 'España',
            'telefono'          => '954 123 456',
            'email'             => 'info@efgnext.es',
            'web'               => 'www.efgnext.es',
            'iban'              => 'ES12 1234 5678 9012 3456 7890',
            'serie_factura'     => 'F-',
            'serie_albaran'     => 'A-',
            'serie_presup'      => 'P-',
            'serie_pedido'      => 'PED-',
            'num_factura'       => 26245,
            'num_albaran'       => 1001,
            'num_presup'        => 501,
            'num_pedido'        => 301,
            'num_pedido_compra' => 101,
            'pie_factura'       => 'Gracias por su confianza. Para consultas: info@efgnext.es',
            'texto_legal'       => 'En caso de impago se aplicarán los intereses legales vigentes.',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}

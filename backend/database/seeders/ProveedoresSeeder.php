<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $fp30  = DB::table('formas_pago')->where('codigo', '30D')->value('id');
        $fp60  = DB::table('formas_pago')->where('codigo', '60D')->value('id');
        $fp90  = DB::table('formas_pago')->where('codigo', '90D')->value('id');
        $ivaId = DB::table('tipos_iva')->where('codigo', 'IVA21')->value('id');

        $proveedores = [
            [
                'codigo'          => 'P001',
                'nombre'          => 'Sevimer SL',
                'nif'             => 'B-41567890',
                'direccion'       => 'Pol. Industrial Sevilla Sur, Nave 12',
                'codigo_postal'   => '41016',
                'localidad'       => 'Sevilla',
                'provincia'       => 'Sevilla',
                'telefono'        => '954 889 900',
                'email'           => 'pedidos@sevimer.com',
                'persona_contacto'=> 'Ignacio Mérida',
                'forma_pago_id'   => $fp60,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 10.00,
                'plazo_entrega'   => 7,
                'iban'            => 'ES79 2100 5731 2413 1428 0369',
                'activo'          => true,
                'observaciones'   => 'Proveedor principal de cintas y cordones. Mínimo pedido 300€.',
            ],
            [
                'codigo'          => 'P002',
                'nombre'          => 'Hilaturas del Norte SA',
                'nif'             => 'A-48123456',
                'direccion'       => 'C/ Industria 45',
                'codigo_postal'   => '48014',
                'localidad'       => 'Bilbao',
                'provincia'       => 'Vizcaya',
                'telefono'        => '944 221 133',
                'email'           => 'ventas@hilaturasnorte.es',
                'persona_contacto'=> 'Mikel Etxebarria',
                'forma_pago_id'   => $fp30,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 5.00,
                'plazo_entrega'   => 5,
                'iban'            => 'ES21 2095 0228 4060 3238 5550',
                'activo'          => true,
                'observaciones'   => 'Especialistas en hilo algodón y seda.',
            ],
            [
                'codigo'          => 'P003',
                'nombre'          => 'Cremacor Ibérica SL',
                'nif'             => 'B-08234567',
                'direccion'       => 'C/ Aragón 201, Planta 3',
                'codigo_postal'   => '08011',
                'localidad'       => 'Barcelona',
                'provincia'       => 'Barcelona',
                'telefono'        => '932 456 789',
                'email'           => 'info@cremacor.es',
                'persona_contacto'=> 'Josep Cortés',
                'forma_pago_id'   => $fp60,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 8.00,
                'plazo_entrega'   => 10,
                'iban'            => 'ES61 0049 1500 0514 1065 9351',
                'activo'          => true,
                'observaciones'   => 'Proveedor único de cremalleras metálicas doradas.',
            ],
            [
                'codigo'          => 'P004',
                'nombre'          => 'Etitex Europa SL',
                'nif'             => 'B-17345678',
                'direccion'       => 'C/ Textil 3, Pol. Ind. Salt',
                'codigo_postal'   => '17190',
                'localidad'       => 'Salt',
                'provincia'       => 'Girona',
                'telefono'        => '972 234 455',
                'email'           => 'etitex@etitex.eu',
                'persona_contacto'=> 'Sylvie Moreau',
                'forma_pago_id'   => $fp30,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 12.00,
                'plazo_entrega'   => 14,
                'iban'            => 'ES76 0182 6010 1000 0208 5595',
                'activo'          => true,
                'observaciones'   => 'Etiquetas personalizadas. Mínimo 500 unidades por referencia.',
            ],
            [
                'codigo'          => 'P005',
                'nombre'          => 'Embalapack SA',
                'nif'             => 'A-28456789',
                'direccion'       => 'Pol. Ind. Vallecas, Nave 8',
                'codigo_postal'   => '28031',
                'localidad'       => 'Madrid',
                'provincia'       => 'Madrid',
                'telefono'        => '916 778 899',
                'email'           => 'pedidos@embalapack.com',
                'persona_contacto'=> 'Raquel Simón',
                'forma_pago_id'   => $fp30,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 5.00,
                'plazo_entrega'   => 3,
                'activo'          => true,
                'observaciones'   => 'Material de embalaje. Entrega en 48h para Madrid.',
            ],
            [
                'codigo'          => 'P006',
                'nombre'          => 'Distribuciones Textiles Martínez SL',
                'nif'             => 'B-30112233',
                'direccion'       => 'Av. del Puerto 45',
                'codigo_postal'   => '30203',
                'localidad'       => 'Cartagena',
                'provincia'       => 'Murcia',
                'telefono'        => '968 445 566',
                'email'           => 'info@dtmartinez.es',
                'persona_contacto'=> 'Pedro Martínez',
                'forma_pago_id'   => $fp90,
                'tipo_iva_id'     => $ivaId,
                'descuento_pct'   => 6.00,
                'plazo_entrega'   => 7,
                'activo'          => true,
            ],
        ];

        foreach ($proveedores as $p) {
            DB::table('proveedores')->updateOrInsert(
                ['codigo' => $p['codigo']],
                array_merge($p, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

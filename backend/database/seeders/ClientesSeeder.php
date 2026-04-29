<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientesSeeder extends Seeder
{
    public function run(): void
    {
        $fpCont  = DB::table('formas_pago')->where('codigo', 'CONT')->value('id');
        $fp30    = DB::table('formas_pago')->where('codigo', '30D')->value('id');
        $fp60    = DB::table('formas_pago')->where('codigo', '60D')->value('id');
        $fp90    = DB::table('formas_pago')->where('codigo', '90D')->value('id');
        $fpRec30 = DB::table('formas_pago')->where('codigo', 'REC30')->value('id');

        $vV01 = DB::table('vendedores')->where('codigo', 'V01')->value('id');
        $vV02 = DB::table('vendedores')->where('codigo', 'V02')->value('id');

        $zAnd = DB::table('zonas')->where('codigo', 'AND')->value('id');
        $zMad = DB::table('zonas')->where('codigo', 'MAD')->value('id');
        $zGal = DB::table('zonas')->where('codigo', 'GAL')->value('id');
        $zLev = DB::table('zonas')->where('codigo', 'LEV')->value('id');

        $sMer = DB::table('sectores')->where('codigo', 'MER')->value('id');
        $sTex = DB::table('sectores')->where('codigo', 'TEX')->value('id');
        $sMay = DB::table('sectores')->where('codigo', 'MAY')->value('id');
        $sMin = DB::table('sectores')->where('codigo', 'MIN')->value('id');

        $clientes = [
            [
                'codigo'          => '0001',
                'nombre'          => 'Almacenes Guimerá SA',
                'nombre_comercial'=> 'Guimerá',
                'nif'             => 'A-28123456',
                'direccion'       => 'Plaza Pontejos 2',
                'codigo_postal'   => '28012',
                'localidad'       => 'Madrid',
                'provincia'       => 'Madrid',
                'telefono'        => '915 551 234',
                'email'           => 'pedidos@guimera.es',
                'persona_contacto'=> 'Luis Guimerá',
                'forma_pago_id'   => $fp30,
                'tipo_cliente'    => 'N',
                'tarifa'          => 2,
                'descuento_pct'   => 5.00,
                'vendedor_id'     => $vV01,
                'zona_id'         => $zMad,
                'sector_id'       => $sMay,
                'credito_maximo'  => 15000.00,
                'saldo_pendiente' => 2840.50,
                'iban'            => 'ES76 0049 0001 5120 3360 0015',
                'activo'          => true,
                'observaciones'   => 'Cliente preferente. Entrega siempre antes de las 10:00.',
            ],
            [
                'codigo'          => '0002',
                'nombre'          => 'Salas Textil SL',
                'nombre_comercial'=> 'Salas Textil',
                'nif'             => 'B-41123789',
                'direccion'       => 'C/ Industria 14',
                'codigo_postal'   => '41510',
                'localidad'       => 'Mairena del Alcor',
                'provincia'       => 'Sevilla',
                'telefono'        => '954 442 211',
                'email'           => 'salas@salastextil.com',
                'persona_contacto'=> 'Carmen Salas',
                'forma_pago_id'   => $fp60,
                'tipo_cliente'    => 'R',
                'tarifa'          => 1,
                'descuento_pct'   => 0.00,
                'vendedor_id'     => $vV02,
                'zona_id'         => $zAnd,
                'sector_id'       => $sTex,
                'credito_maximo'  => 8000.00,
                'saldo_pendiente' => 3756.32,
                'iban'            => 'ES91 2100 0418 4502 0005 1332',
                'activo'          => true,
                'observaciones'   => 'Sujeto a recargo de equivalencia. Verificar siempre.',
            ],
            [
                'codigo'          => '0003',
                'nombre'          => 'Servicio de Mercería y Textil SLL',
                'nombre_comercial'=> 'SerMeyTex',
                'nif'             => 'B-29887654',
                'direccion'       => 'Av. Málaga 33',
                'codigo_postal'   => '29004',
                'localidad'       => 'Málaga',
                'provincia'       => 'Málaga',
                'telefono'        => '952 334 455',
                'email'           => 'info@sermeytex.com',
                'persona_contacto'=> 'Antonio Fernández',
                'forma_pago_id'   => $fp30,
                'tipo_cliente'    => 'R',
                'tarifa'          => 1,
                'descuento_pct'   => 3.00,
                'vendedor_id'     => $vV01,
                'zona_id'         => $zAnd,
                'sector_id'       => $sMer,
                'credito_maximo'  => 20000.00,
                'saldo_pendiente' => 7802.91,
                'iban'            => 'ES80 2038 3507 6960 0001 0001',
                'activo'          => true,
                'observaciones'   => 'Gran cliente. Descuento adicional en pedidos > 3000€.',
            ],
            [
                'codigo'          => '0004',
                'nombre'          => 'Rosa Nieves Vergara López',
                'nombre_comercial'=> 'Rosa Vergara',
                'nif'             => '28456789K',
                'direccion'       => 'C/ Real 7',
                'codigo_postal'   => '41400',
                'localidad'       => 'Écija',
                'provincia'       => 'Sevilla',
                'telefono'        => '671 223 344',
                'email'           => 'rnvergara@gmail.com',
                'forma_pago_id'   => $fpCont,
                'tipo_cliente'    => 'N',
                'tarifa'          => 3,
                'descuento_pct'   => 0.00,
                'vendedor_id'     => $vV02,
                'zona_id'         => $zAnd,
                'sector_id'       => $sMin,
                'credito_maximo'  => 1000.00,
                'saldo_pendiente' => 346.00,
                'activo'          => true,
            ],
            [
                'codigo'          => '0005',
                'nombre'          => 'Leirano SL',
                'nombre_comercial'=> 'Leirano',
                'nif'             => 'B-36001234',
                'direccion'       => 'Rúa do Comercio 8',
                'codigo_postal'   => '36201',
                'localidad'       => 'Vigo',
                'provincia'       => 'Pontevedra',
                'telefono'        => '986 771 234',
                'email'           => 'leirano@leirano.com',
                'persona_contacto'=> 'Manuel Leiro',
                'forma_pago_id'   => $fp60,
                'tipo_cliente'    => 'N',
                'tarifa'          => 2,
                'descuento_pct'   => 7.00,
                'vendedor_id'     => $vV01,
                'zona_id'         => $zGal,
                'sector_id'       => $sMay,
                'credito_maximo'  => 10000.00,
                'saldo_pendiente' => 916.78,
                'iban'            => 'ES66 2080 0661 3030 0101 2345',
                'activo'          => true,
            ],
            [
                'codigo'          => '0006',
                'nombre'          => 'Distribuciones Levante SLU',
                'nombre_comercial'=> 'DistLevante',
                'nif'             => 'B-46334455',
                'direccion'       => 'Pol. Ind. Fuente del Jarro, C/ Almería 8',
                'codigo_postal'   => '46988',
                'localidad'       => 'Paterna',
                'provincia'       => 'Valencia',
                'telefono'        => '961 123 456',
                'email'           => 'compras@distlevante.es',
                'persona_contacto'=> 'Pilar Soler',
                'forma_pago_id'   => $fp60,
                'tipo_cliente'    => 'N',
                'tarifa'          => 2,
                'descuento_pct'   => 5.00,
                'vendedor_id'     => $vV01,
                'zona_id'         => $zLev,
                'sector_id'       => $sMay,
                'credito_maximo'  => 12000.00,
                'saldo_pendiente' => 0.00,
                'activo'          => true,
            ],
            [
                'codigo'          => '0007',
                'nombre'          => 'Confecciones Hermanos Torres SC',
                'nombre_comercial'=> 'Torres Conf.',
                'nif'             => 'J-41223344',
                'direccion'       => 'C/ Industria 78, Local 3',
                'codigo_postal'   => '41960',
                'localidad'       => 'Gines',
                'provincia'       => 'Sevilla',
                'telefono'        => '954 789 012',
                'email'           => 'info@torresconf.es',
                'forma_pago_id'   => $fpRec30,
                'tipo_cliente'    => 'R',
                'tarifa'          => 1,
                'descuento_pct'   => 2.00,
                'vendedor_id'     => $vV02,
                'zona_id'         => $zAnd,
                'sector_id'       => $sTex,
                'credito_maximo'  => 6000.00,
                'saldo_pendiente' => 1240.00,
                'activo'          => true,
            ],
            [
                'codigo'          => '0008',
                'nombre'          => 'Mercería El Hilo de Oro SL',
                'nombre_comercial'=> 'El Hilo de Oro',
                'nif'             => 'B-28445566',
                'direccion'       => 'C/ Mayor 15',
                'codigo_postal'   => '28300',
                'localidad'       => 'Aranjuez',
                'provincia'       => 'Madrid',
                'telefono'        => '918 912 345',
                'email'           => 'merceria@hilodeoro.es',
                'forma_pago_id'   => $fp30,
                'tipo_cliente'    => 'R',
                'tarifa'          => 2,
                'descuento_pct'   => 0.00,
                'vendedor_id'     => $vV01,
                'zona_id'         => $zMad,
                'sector_id'       => $sMer,
                'credito_maximo'  => 5000.00,
                'saldo_pendiente' => 580.00,
                'activo'          => true,
            ],
        ];

        foreach ($clientes as $c) {
            DB::table('clientes')->updateOrInsert(
                ['codigo' => $c['codigo']],
                array_merge($c, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EmpresaSeeder::class,
            RolesPermissionsSeeder::class,
            UsersSeeder::class,
            MaestrosSeeder::class,
            ClientesSeeder::class,
            ProveedoresSeeder::class,
            ArticulosSeeder::class,
            FormulasSeeder::class,
            VentasSeeder::class,
        ]);
    }
}

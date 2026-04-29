<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@efgnext.es',
                'password' => Hash::make('password'),
                'activo'   => true,
                'role'     => 'superadmin',
            ],
            [
                'name'     => 'María García López',
                'email'    => 'mgarcia@efgnext.es',
                'password' => Hash::make('password'),
                'activo'   => true,
                'role'     => 'comercial',
            ],
            [
                'name'     => 'Juan Pérez Ruiz',
                'email'    => 'jperez@efgnext.es',
                'password' => Hash::make('password'),
                'activo'   => true,
                'role'     => 'comercial',
            ],
            [
                'name'     => 'Carlos Almacén',
                'email'    => 'calmacen@efgnext.es',
                'password' => Hash::make('password'),
                'activo'   => true,
                'role'     => 'almacenero',
            ],
            [
                'name'     => 'Ana Contabilidad',
                'email'    => 'acontabilidad@efgnext.es',
                'password' => Hash::make('password'),
                'activo'   => true,
                'role'     => 'contabilidad',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'email_verified_at' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ])
            );

            $user->syncRoles([$role]);
        }
    }
}

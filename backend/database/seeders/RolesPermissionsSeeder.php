<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermissionsSeeder extends Seeder
{
    // Matriz completa de permisos por módulo
    private array $permissions = [
        // Dashboard
        'dashboard.view',

        // Empresa
        'empresa.view', 'empresa.edit',

        // Clientes
        'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.delete',
        'clientes.export',

        // Proveedores
        'proveedores.view', 'proveedores.create', 'proveedores.edit', 'proveedores.delete',

        // Artículos
        'articulos.view', 'articulos.create', 'articulos.edit', 'articulos.delete',
        'articulos.precios',
        // articulos.precios = puede ver/editar precios y costes

        // Almacén
        'almacen.view', 'almacen.movimientos', 'almacen.inventario', 'almacen.ajuste',

        // Compras
        'compras.view',
        'compras.pedidos.create', 'compras.pedidos.edit', 'compras.pedidos.delete',
        'compras.albaranes.create', 'compras.albaranes.edit',
        'compras.facturas.create', 'compras.facturas.edit',

        // Ventas
        'ventas.view',
        'ventas.presupuestos.create', 'ventas.presupuestos.edit', 'ventas.presupuestos.delete',
        'ventas.pedidos.create', 'ventas.pedidos.edit',
        'ventas.albaranes.create', 'ventas.albaranes.edit',
        'ventas.facturas.create', 'ventas.facturas.edit', 'ventas.facturas.anular',
        'ventas.rectificativas.create',
        'ventas.pdf',

        // Cobros
        'cobros.view', 'cobros.create', 'cobros.cobrar', 'cobros.remesas',

        // Fabricación
        'fabricacion.view',
        'fabricacion.formulas.create', 'fabricacion.formulas.edit', 'fabricacion.formulas.delete',
        'fabricacion.ordenes.create', 'fabricacion.ordenes.ejecutar', 'fabricacion.ordenes.anular',

        // Estadísticas
        'estadisticas.view', 'estadisticas.export',

        // Administración
        'admin.users', 'admin.roles', 'admin.logs', 'admin.config',
    ];

    private array $roles = [
        'superadmin' => [], // Todos los permisos — se asignan manualmente
        'admin' => [
            'dashboard.view',
            'empresa.view', 'empresa.edit',
            'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.delete', 'clientes.export',
            'proveedores.view', 'proveedores.create', 'proveedores.edit', 'proveedores.delete',
            'articulos.view', 'articulos.create', 'articulos.edit', 'articulos.delete', 'articulos.precios',
            'almacen.view', 'almacen.movimientos', 'almacen.inventario', 'almacen.ajuste',
            'compras.view',
            'compras.pedidos.create', 'compras.pedidos.edit', 'compras.pedidos.delete',
            'compras.albaranes.create', 'compras.albaranes.edit',
            'compras.facturas.create', 'compras.facturas.edit',
            'ventas.view',
            'ventas.presupuestos.create', 'ventas.presupuestos.edit', 'ventas.presupuestos.delete',
            'ventas.pedidos.create', 'ventas.pedidos.edit',
            'ventas.albaranes.create', 'ventas.albaranes.edit',
            'ventas.facturas.create', 'ventas.facturas.edit', 'ventas.facturas.anular',
            'ventas.rectificativas.create',
            'ventas.pdf',
            'cobros.view', 'cobros.create', 'cobros.cobrar', 'cobros.remesas',
            'fabricacion.view',
            'fabricacion.formulas.create', 'fabricacion.formulas.edit', 'fabricacion.formulas.delete',
            'fabricacion.ordenes.create', 'fabricacion.ordenes.ejecutar', 'fabricacion.ordenes.anular',
            'estadisticas.view', 'estadisticas.export',
            'admin.users', 'admin.roles', 'admin.logs', 'admin.config',
        ],
        'comercial' => [
            'dashboard.view',
            'clientes.view', 'clientes.create', 'clientes.edit',
            'articulos.view',
            'almacen.view',
            'ventas.view',
            'ventas.presupuestos.create', 'ventas.presupuestos.edit',
            'ventas.pedidos.create', 'ventas.pedidos.edit',
            'ventas.albaranes.create', 'ventas.albaranes.edit',
            'ventas.facturas.create', 'ventas.facturas.edit',
            'ventas.pdf',
            'cobros.view',
            'estadisticas.view',
        ],
        'almacenero' => [
            'dashboard.view',
            'articulos.view',
            'almacen.view', 'almacen.movimientos', 'almacen.inventario', 'almacen.ajuste',
            'compras.view', 'compras.albaranes.create', 'compras.albaranes.edit',
            'fabricacion.view',
            'fabricacion.ordenes.create', 'fabricacion.ordenes.ejecutar',
        ],
        'contabilidad' => [
            'dashboard.view',
            'clientes.view', 'clientes.export',
            'proveedores.view',
            'ventas.view', 'ventas.pdf',
            'cobros.view', 'cobros.create', 'cobros.cobrar', 'cobros.remesas',
            'compras.view', 'compras.facturas.create', 'compras.facturas.edit',
            'estadisticas.view', 'estadisticas.export',
        ],
        'solo_lectura' => [
            'dashboard.view',
            'clientes.view',
            'proveedores.view',
            'articulos.view',
            'almacen.view',
            'ventas.view',
            'cobros.view',
            'estadisticas.view',
        ],
    ];

    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        foreach ($this->permissions as $permName) {
            Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // Crear roles y asignar permisos
        foreach ($this->roles as $roleName => $rolePerms) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);

            if ($roleName === 'superadmin') {
                // superadmin recibe TODOS los permisos
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($rolePerms);
            }
        }
    }
}

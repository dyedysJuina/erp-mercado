<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard', 'categorias', 'atributos', 'unidades', 'embalagens', 'marcas',
            'produtos-base', 'variacoes', 'clientes', 'fornecedores',
            'compras', 'pedidos', 'separacao', 'estoque', 'lotes', 'precos', 'lojas',
            'ofertas',
            'pdv', 'financeiro', 'relatorios',
            'admin.temas', 'admin.fiscal', 'admin.gerencial',
            'usuarios', 'papeis',
            'financeiro.conciliacao',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        $supervisor = Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'dashboard', 'clientes', 'fornecedores', 'compras', 'pedidos',
            'estoque', 'lotes', 'precos',
            'pdv', 'financeiro', 'financeiro.conciliacao', 'relatorios',
        ]);

        $operador = Role::firstOrCreate(['name' => 'Operador', 'guard_name' => 'web']);
        $operador->syncPermissions([
            'dashboard', 'clientes', 'pdv', 'estoque',
        ]);

        $separador = Role::firstOrCreate(['name' => 'Separador', 'guard_name' => 'web']);
        $separador->syncPermissions([
            'dashboard', 'pedidos', 'separacao', 'estoque',
        ]);

        $this->command->info('✅ ' . count($permissions) . ' permissões criadas.');
        $this->command->info('✅ 4 papéis criados: Admin, Supervisor, Operador, Separador');
    }
}

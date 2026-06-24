<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ThemeSeeder::class,
            EstadoCidadeSeeder::class,
            NcmSeeder::class,
            CfopSeeder::class,
            CestSeeder::class,
            IcmsCstSeeder::class,
            RegraFiscalNcmSeeder::class,
            CategoriaCompletaSeeder::class,
            AtributoDepartamentoSeeder::class,
            RolePermissionSeeder::class,
            ProdutoTestSeeder::class,
            LoteTestSeeder::class,
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'pedidos' => ['status', 'created_at'],
            'pdv_vendas' => ['status', 'created_at'],
            'compras_pedidos' => ['status'],
            'financeiro_lancamentos' => ['status', 'data_vencimento'],
            'clientes' => ['ativo'],
            'produto_variacoes' => ['ativo'],
            'pdv_caixas_aberturas' => ['status'],
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) continue;
            foreach ($columns as $col) {
                $indexName = "{$table}_{$col}_index";
                try {
                    Schema::table($table, fn(Blueprint $t) => $t->index($col, $indexName));
                } catch (\Exception $e) {
                    // Index may already exist
                }
            }
        }
    }

    public function down(): void
    {
        // No down needed; indexes are additive
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════
        // 1. GARANTIR TABELA pedidos_separacoes COM TIMESTAMPS
        // ═══════════════════════════════════════════
        if (Schema::hasTable('pedidos_separacoes')) {
            Schema::table('pedidos_separacoes', function (Blueprint $t) {
                if (!Schema::hasColumn('pedidos_separacoes', 'created_at')) {
                    $t->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('pedidos_separacoes', 'updated_at')) {
                    $t->timestamp('updated_at')->nullable();
                }
            });
        }

        // ═══════════════════════════════════════════
        // 2. COLUNAS FALTANTES EM pedidos_separacao_itens
        // ═══════════════════════════════════════════
        if (Schema::hasTable('pedidos_separacao_itens')) {
            Schema::table('pedidos_separacao_itens', function (Blueprint $t) {
                if (!Schema::hasColumn('pedidos_separacao_itens', 'motivo_falta')) {
                    $t->text('motivo_falta')->nullable()->after('observacao');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'produto_substituto_id')) {
                    $t->unsignedBigInteger('produto_substituto_id')->nullable()->after('motivo_falta');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'preco_substituto')) {
                    $t->decimal('preco_substituto', 12, 2)->nullable()->after('produto_substituto_id');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'diferenca_valor')) {
                    $t->decimal('diferenca_valor', 12, 2)->nullable()->after('preco_substituto');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'aprovacao_cliente_status')) {
                    $t->enum('aprovacao_cliente_status', ['pendente', 'aprovada', 'recusada'])->nullable()->after('diferenca_valor');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'aprovado_em')) {
                    $t->dateTime('aprovado_em')->nullable()->after('aprovacao_cliente_status');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'foto_comprovante')) {
                    $t->string('foto_comprovante')->nullable()->after('aprovado_em');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'tempo_inicio')) {
                    $t->dateTime('tempo_inicio')->nullable()->after('foto_comprovante');
                }
                if (!Schema::hasColumn('pedidos_separacao_itens', 'tempo_fim')) {
                    $t->dateTime('tempo_fim')->nullable()->after('tempo_inicio');
                }
            });
        }

        // ═══════════════════════════════════════════
        // 3. FOREIGN KEYS AUSENTES
        // ═══════════════════════════════════════════
        $fks = [
            // CARRINHO
            ['carrinho_itens', 'carrinho_id', 'carrinhos', 'id', 'CASCADE', 'CASCADE'],
            ['carrinho_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],

            // ESTOQUE
            ['estoque_saldos', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
            ['estoque_movimentacoes', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
            ['estoque_inventarios', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

            // PRECOS
            ['precos_produtos_lojas', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
            ['precos_produtos_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
            ['precos_historico', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
            ['precos_historico', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

            // OFERTAS
            ['ofertas_produtos', 'campanha_id', 'ofertas_campanhas', 'id', 'CASCADE', 'CASCADE'],
            ['ofertas_produtos', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
            ['ofertas_campanhas_lojas', 'campanha_id', 'ofertas_campanhas', 'id', 'CASCADE', 'CASCADE'],
            ['ofertas_campanhas_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

            // PEDIDOS
            ['pedidos', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
            ['pedidos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'RESTRICT'],
            ['pedidos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['pedidos', 'separador_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['pedidos', 'entregador_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['pedidos_itens', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['pedidos_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
            ['pedidos_pagamentos', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['pedidos_status_historico', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['pedidos_status_historico', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],

            // SEPARACAO
            ['pedidos_separacoes', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['pedidos_separacoes', 'separador_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['pedidos_separacao_itens', 'separacao_id', 'pedidos_separacoes', 'id', 'CASCADE', 'CASCADE'],
            ['pedidos_separacao_itens', 'pedido_item_id', 'pedidos_itens', 'id', 'CASCADE', 'CASCADE'],

            // PDV
            ['pdv_vendas', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
            ['pdv_vendas', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['pdv_venda_itens', 'venda_id', 'pdv_vendas', 'id', 'CASCADE', 'CASCADE'],
            ['pdv_venda_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
            ['pdv_venda_pagamentos', 'venda_id', 'pdv_vendas', 'id', 'CASCADE', 'CASCADE'],

            // NOTIFICACOES
            ['notificacoes', 'usuario_id', 'users', 'id', 'CASCADE', 'CASCADE'],
            ['notificacoes', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],

            // FISCAL
            ['fiscal_documentos', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'SET NULL'],
            ['fiscal_documentos', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
            ['fiscal_documento_itens', 'documento_id', 'fiscal_documentos', 'id', 'CASCADE', 'CASCADE'],
            ['fiscal_documento_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],

            // COMPRAS
            ['compras_cotacoes', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'RESTRICT'],
            ['compras_cotacao_itens', 'cotacao_id', 'compras_cotacoes', 'id', 'CASCADE', 'CASCADE'],
            ['compras_pedidos', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'RESTRICT'],
            ['compras_pedido_itens', 'pedido_id', 'compras_pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['compras_recebimentos', 'pedido_id', 'compras_pedidos', 'id', 'CASCADE', 'CASCADE'],
            ['compras_recebimento_itens', 'recebimento_id', 'compras_recebimentos', 'id', 'CASCADE', 'CASCADE'],

            // FINANCEIRO
            ['financeiro_lancamentos', 'categoria_id', 'financeiro_categorias', 'id', 'CASCADE', 'RESTRICT'],
            ['financeiro_lancamentos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
            ['financeiro_lancamentos', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'SET NULL'],
            ['financeiro_lancamentos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'SET NULL'],
            ['financeiro_contas', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],

            // CLIENTES
            ['clientes_enderecos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],

            // USUARIOS
            ['usuarios_lojas', 'usuario_id', 'users', 'id', 'CASCADE', 'CASCADE'],
            ['usuarios_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
        ];

        foreach ($fks as $fk) {
            [$table, $column, $refTable, $refColumn, $onUpdate, $onDelete] = $fk;

            if (!Schema::hasTable($table) || !Schema::hasTable($refTable)) {
                continue;
            }
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            // Verifica se ambas as tabelas usam InnoDB (FK requer InnoDB)
            try {
                $tableEngine = DB::select("SHOW TABLE STATUS WHERE Name = ?", [$table])[0]->Engine ?? '';
                $refEngine = DB::select("SHOW TABLE STATUS WHERE Name = ?", [$refTable])[0]->Engine ?? '';
                if (strtolower($tableEngine) !== 'innodb' || strtolower($refEngine) !== 'innodb') {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            // pdv_vendas.usuario_id precisa ser nullable para SET NULL
            if ($table === 'pdv_vendas' && $column === 'usuario_id' && $onDelete === 'SET NULL') {
                try {
                    Schema::table($table, function (Blueprint $t) use ($column) {
                        $t->unsignedBigInteger($column)->nullable()->change();
                    });
                } catch (\Exception $e) {}
            }

            $fkName = "fk_{$table}_{$column}";

            try {
                // Remove FK antiga se existir
                try {
                    Schema::table($table, function (Blueprint $t) use ($fkName) {
                        $t->dropForeign($fkName);
                    });
                } catch (\Exception $e) {
                    // FK nao existia, ignora
                }

                Schema::table($table, function (Blueprint $t) use ($column, $refTable, $refColumn, $onUpdate, $onDelete, $fkName) {
                    $t->foreign($column, $fkName)
                        ->references($refColumn)
                        ->on($refTable)
                        ->onUpdate($onUpdate)
                        ->onDelete($onDelete);
                });
            } catch (\Exception $e) {
                echo "  AVISO: FK {$fkName} ignorada: {$e->getMessage()}\n";
            }
        }

        // ═══════════════════════════════════════════
        // 4. INDEXES PARA PERFORMANCE (com verificacao de existencia)
        // ═══════════════════════════════════════════
        $indexes = [
            ['pedidos', ['status', 'origem', 'created_at'], 'idx_pedidos_status_origem_data'],
            ['pedidos', ['cliente_id', 'status'], 'idx_pedidos_cliente_status'],
            ['pedidos', ['loja_id', 'status', 'created_at'], 'idx_pedidos_loja_status_data'],
            ['pedidos_itens', ['pedido_id', 'status_item'], 'idx_pedidos_itens_pedido_status'],
            ['pedidos_itens', ['produto_variacao_id'], 'idx_pedidos_itens_variacao'],
            ['estoque_saldos', ['loja_id', 'produto_variacao_id'], 'idx_estoque_saldos_loja_prod'],
            ['produto_variacoes', ['sku'], 'idx_variacoes_sku'],
            ['produto_variacoes', ['produto_base_id'], 'idx_variacoes_base_id'],
            ['produto_codigos_barras', ['codigo'], 'idx_barcodes_codigo'],
            ['produto_codigos_barras', ['produto_variacao_id'], 'idx_barcodes_variacao'],
        ];

        foreach ($indexes as $idx) {
            [$table, $cols, $name] = $idx;
            if (!Schema::hasTable($table)) continue;
            try {
                // Verifica se o indice ja existe
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $doctrineTable = $sm->listTableDetails($table);
                if (!$doctrineTable->hasIndex($name)) {
                    Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                        $t->index($cols, $name);
                    });
                }
            } catch (\Exception $e) {
                // Fallback: tenta criar e ignora se ja existir
                try {
                    Schema::table($table, function (Blueprint $t) use ($cols, $name) {
                        $t->index($cols, $name);
                    });
                } catch (\Exception $e2) {}
            }
        }

        echo "✅ Migração de integridade concluída.\n";
    }

    public function down(): void
    {
        // Remove FKs adicionadas
        $tables = [
            'carrinho_itens', 'estoque_saldos', 'estoque_movimentacoes',
            'pedidos_separacoes', 'pedidos_separacao_itens',
        ];
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;
            try {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $columns = Schema::getColumnListing($table);
                    foreach ($columns as $col) {
                        if (str_ends_with($col, '_id') && $col !== 'id') {
                            try { $t->dropForeign("fk_{$table}_{$col}"); } catch (\Exception $e) {}
                        }
                    }
                });
            } catch (\Exception $e) {}
        }

        // Remove colunas adicionadas
        if (Schema::hasTable('pedidos_separacao_itens')) {
            Schema::table('pedidos_separacao_itens', function (Blueprint $t) {
                $cols = ['motivo_falta','produto_substituto_id','preco_substituto','diferenca_valor',
                         'aprovacao_cliente_status','aprovado_em','foto_comprovante','tempo_inicio','tempo_fim'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('pedidos_separacao_itens', $col)) {
                        try { $t->dropColumn($col); } catch (\Exception $e) {}
                    }
                }
            });
        }
    }
};

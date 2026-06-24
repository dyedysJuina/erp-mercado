<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fks = [
        // ─── CATEGORIAS ───
        ['categorias', 'parent_id', 'categorias', 'id', 'CASCADE', 'CASCADE'],
        ['categorias', 'categoria_raiz_id', 'categorias', 'id', 'CASCADE', 'CASCADE'],
        ['categorias_atributos', 'categoria_id', 'categorias', 'id', 'CASCADE', 'CASCADE'],
        ['categorias_atributos', 'atributo_id', 'atributos', 'id', 'CASCADE', 'RESTRICT'],

        // ─── PRODUTOS ───
        ['produtos_base', 'categoria_id', 'categorias', 'id', 'CASCADE', 'RESTRICT'],
        ['produtos_base', 'unidade_medida_id', 'unidades_medida', 'id', 'CASCADE', 'RESTRICT'],
        ['produtos_base', 'marca_id', 'marcas', 'id', 'CASCADE', 'RESTRICT'],
        ['produtos_base', 'embalagem_id', 'embalagens', 'id', 'CASCADE', 'RESTRICT'],
        ['produto_variacoes', 'produto_base_id', 'produtos_base', 'id', 'CASCADE', 'CASCADE'],
        ['produto_variacoes', 'embalagem_id', 'embalagens', 'id', 'CASCADE', 'RESTRICT'],
        ['produto_codigos_barras', 'variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['produto_imagens', 'variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['produto_apresentacoes', 'produto_base_id', 'produtos_base', 'id', 'CASCADE', 'CASCADE'],
        ['produto_variacao_atributos', 'variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['produto_variacao_atributos', 'atributo_id', 'atributos', 'id', 'CASCADE', 'RESTRICT'],
        ['produto_fornecedores', 'produto_base_id', 'produtos_base', 'id', 'CASCADE', 'CASCADE'],
        ['produto_fornecedores', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'RESTRICT'],
        ['marcas_categorias', 'marca_id', 'marcas', 'id', 'CASCADE', 'CASCADE'],
        ['marcas_categorias', 'categoria_id', 'categorias', 'id', 'CASCADE', 'CASCADE'],

        // ─── CLIENTES ───
        ['clientes_enderecos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],
        ['clientes_enderecos', 'cidade_id', 'cidades', 'id', 'CASCADE', 'RESTRICT'],
        ['crm_cupons_usos', 'cupom_id', 'crm_cupons', 'id', 'CASCADE', 'CASCADE'],
        ['crm_cupons_usos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],
        ['crm_clientes_grupos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],
        ['crm_clientes_grupos', 'grupo_id', 'crm_grupos_clientes', 'id', 'CASCADE', 'CASCADE'],
        ['crm_pontos_movimentacoes', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],
        ['notificacoes', 'usuario_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['notificacoes', 'cliente_id', 'clientes', 'id', 'CASCADE', 'CASCADE'],

        // ─── CIDADES ───
        ['cidades', 'estado_id', 'estados', 'id', 'CASCADE', 'RESTRICT'],

        // ─── FORNECEDORES ───
        ['fornecedores', 'cidade_id', 'cidades', 'id', 'CASCADE', 'RESTRICT'],
        ['fornecedores_contatos', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'CASCADE'],

        // ─── LOJAS ───
        ['lojas', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],
        ['lojas', 'cidade_id', 'cidades', 'id', 'CASCADE', 'RESTRICT'],
        ['lojas_configuracoes', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
        ['usuarios_lojas', 'usuario_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['usuarios_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

        // ─── AUDITORIA ───
        ['auditoria_logs', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['auditoria_logs', 'loja_id', 'lojas', 'id', 'CASCADE', 'SET NULL'],

        // ─── ESTOQUE ───
        ['estoque_saldos', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_saldos', 'local_id', 'estoque_locais', 'id', 'CASCADE', 'RESTRICT'],
        ['estoque_saldos', 'lote_id', 'estoque_lotes', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_movimentacoes', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_movimentacoes', 'local_id', 'estoque_locais', 'id', 'CASCADE', 'RESTRICT'],
        ['estoque_movimentacoes', 'lote_id', 'estoque_lotes', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_movimentacoes', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_lotes', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_lotes', 'local_id', 'estoque_locais', 'id', 'CASCADE', 'RESTRICT'],
        ['estoque_lotes', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_inventarios', 'local_id', 'estoque_locais', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_inventarios', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_inventario_itens', 'inventario_id', 'estoque_inventarios', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_inventario_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_transferencias', 'origem_local_id', 'estoque_locais', 'id', 'CASCADE', 'RESTRICT'],
        ['estoque_transferencias', 'destino_local_id', 'estoque_locais', 'id', 'CASCADE', 'RESTRICT'],
        ['estoque_transferencias', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['estoque_transferencia_itens', 'transferencia_id', 'estoque_transferencias', 'id', 'CASCADE', 'CASCADE'],
        ['estoque_transferencia_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],

        // ─── PRECOS ───
        ['precos_produtos_lojas', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['precos_produtos_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
        ['precos_historico', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['precos_historico', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
        ['precos_historico', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],

        // ─── OFERTAS ───
        ['ofertas_produtos', 'campanha_id', 'ofertas_campanhas', 'id', 'CASCADE', 'CASCADE'],
        ['ofertas_produtos', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'CASCADE'],
        ['ofertas_campanhas_lojas', 'campanha_id', 'ofertas_campanhas', 'id', 'CASCADE', 'CASCADE'],
        ['ofertas_campanhas_lojas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

        // ─── PEDIDOS ───
        ['pedidos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'RESTRICT'],
        ['pedidos', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
        ['pedidos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pedidos_itens', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['pedidos_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
        ['pedidos_pagamentos', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['pedidos_pagamentos', 'forma_pagamento_id', 'formas_pagamento', 'id', 'CASCADE', 'RESTRICT'],
        ['pedidos_status_historico', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['pedidos_status_historico', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pedidos_separacoes', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['pedidos_separacao_itens', 'separacao_id', 'pedidos_separacoes', 'id', 'CASCADE', 'CASCADE'],
        ['pedidos_separacao_itens', 'pedido_item_id', 'pedidos_itens', 'id', 'CASCADE', 'CASCADE'],

        // ─── PDV ───
        ['pdv_vendas', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'SET NULL'],
        ['pdv_vendas', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
        ['pdv_vendas', 'caixa_id', 'pdv_caixas', 'id', 'CASCADE', 'RESTRICT'],
        ['pdv_vendas', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pdv_venda_itens', 'venda_id', 'pdv_vendas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_venda_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
        ['pdv_venda_pagamentos', 'venda_id', 'pdv_vendas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_venda_pagamentos', 'forma_pagamento_id', 'formas_pagamento', 'id', 'CASCADE', 'RESTRICT'],
        ['pdv_caixas', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_caixas_aberturas', 'caixa_id', 'pdv_caixas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_caixas_aberturas', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pdv_caixa_movimentos', 'caixa_id', 'pdv_caixas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_caixa_movimentos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pdv_devolucoes', 'venda_id', 'pdv_vendas', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_devolucoes', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['pdv_devolucao_itens', 'devolucao_id', 'pdv_devolucoes', 'id', 'CASCADE', 'CASCADE'],
        ['pdv_devolucao_itens', 'venda_item_id', 'pdv_venda_itens', 'id', 'CASCADE', 'CASCADE'],

        // ─── ENTREGAS ───
        ['entregas_rotas_pedidos', 'rota_id', 'entregas_rotas', 'id', 'CASCADE', 'CASCADE'],
        ['entregas_rotas_pedidos', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['entregas_ocorrencias', 'rota_id', 'entregas_rotas', 'id', 'CASCADE', 'CASCADE'],

        // ─── COMPRAS ───
        ['compras_cotacoes', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'RESTRICT'],
        ['compras_cotacao_itens', 'cotacao_id', 'compras_cotacoes', 'id', 'CASCADE', 'CASCADE'],
        ['compras_pedidos', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'RESTRICT'],
        ['compras_pedidos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['compras_pedido_itens', 'pedido_id', 'compras_pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['compras_recebimentos', 'pedido_id', 'compras_pedidos', 'id', 'CASCADE', 'CASCADE'],
        ['compras_recebimento_itens', 'recebimento_id', 'compras_recebimentos', 'id', 'CASCADE', 'CASCADE'],
        ['compras_recebimento_itens', 'pedido_item_id', 'compras_pedido_itens', 'id', 'CASCADE', 'CASCADE'],
        ['compras_recebimento_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],

        // ─── FINANCEIRO ───
        ['financeiro_categorias', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_categorias', 'parent_id', 'financeiro_categorias', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_centros_custo', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_contas', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_lancamentos', 'empresa_id', 'empresas', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_lancamentos', 'categoria_id', 'financeiro_categorias', 'id', 'CASCADE', 'RESTRICT'],
        ['financeiro_lancamentos', 'centro_custo_id', 'financeiro_centros_custo', 'id', 'CASCADE', 'SET NULL'],
        ['financeiro_lancamentos', 'conta_id', 'financeiro_contas', 'id', 'CASCADE', 'RESTRICT'],
        ['financeiro_lancamentos', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],
        ['financeiro_lancamentos', 'fornecedor_id', 'fornecedores', 'id', 'CASCADE', 'SET NULL'],
        ['financeiro_lancamentos', 'cliente_id', 'clientes', 'id', 'CASCADE', 'SET NULL'],
        ['financeiro_movimentos_bancarios', 'conta_id', 'financeiro_contas', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_conciliacoes', 'movimento_bancario_id', 'financeiro_movimentos_bancarios', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_conciliacoes', 'lancamento_id', 'financeiro_lancamentos', 'id', 'CASCADE', 'CASCADE'],
        ['financeiro_conciliacoes', 'usuario_id', 'users', 'id', 'CASCADE', 'SET NULL'],

        // ─── FISCAL ───
        ['fiscal_documentos', 'pedido_id', 'pedidos', 'id', 'CASCADE', 'SET NULL'],
        ['fiscal_documentos', 'loja_id', 'lojas', 'id', 'CASCADE', 'RESTRICT'],
        ['fiscal_documento_itens', 'documento_id', 'fiscal_documentos', 'id', 'CASCADE', 'CASCADE'],
        ['fiscal_documento_itens', 'produto_variacao_id', 'produto_variacoes', 'id', 'CASCADE', 'RESTRICT'],
        ['fiscal_regras_produto', 'produto_base_id', 'produtos_base', 'id', 'CASCADE', 'CASCADE'],
        ['fiscal_regras_produto', 'perfil_id', 'fiscal_perfis', 'id', 'CASCADE', 'RESTRICT'],
        ['fiscal_regimes_tributarios', 'perfil_id', 'fiscal_perfis', 'id', 'CASCADE', 'CASCADE'],

        // ─── USUARIOS ───
        ['usuarios_permissoes_extras', 'usuario_id', 'users', 'id', 'CASCADE', 'CASCADE'],
        ['usuarios_permissoes_extras', 'loja_id', 'lojas', 'id', 'CASCADE', 'CASCADE'],

        // ─── SPATIE ───
        ['model_has_roles', 'role_id', 'roles', 'id', 'CASCADE', 'CASCADE'],
        ['model_has_permissions', 'permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE'],
        ['role_has_permissions', 'role_id', 'roles', 'id', 'CASCADE', 'CASCADE'],
        ['role_has_permissions', 'permission_id', 'permissions', 'id', 'CASCADE', 'CASCADE'],
    ];

    public function up(): void
    {
        // Backup antes de alterar
        $db = config('database.connections.mysql.database');
        $backupPath = storage_path("backups/fks_before_{$db}_" . date('Ymd_His') . '.sql');
        // Nota: backup manual recomendado via mysqldump

        foreach ($this->fks as $fk) {
            [$table, $column, $referencedTable, $referencedColumn, $onUpdate, $onDelete] = $fk;

            if (!Schema::hasTable($table) || !Schema::hasTable($referencedTable)) {
                continue;
            }

            $fkName = "fk_{$table}_{$column}";

            try {
                Schema::table($table, function (Blueprint $t) use ($column, $referencedTable, $referencedColumn, $onUpdate, $onDelete, $fkName) {
                    $t->foreign($column, $fkName)
                        ->references($referencedColumn)
                        ->on($referencedTable)
                        ->onUpdate($onUpdate)
                        ->onDelete($onDelete);
                });
            } catch (\Exception $e) {
                echo "  AVISO: FK {$fkName} ignorada: {$e->getMessage()}\n";
            }
        }
    }

    public function down(): void
    {
        foreach ($this->fks as $fk) {
            [$table, $column] = $fk;
            $fkName = "fk_{$table}_{$column}";

            try {
                Schema::table($table, function (Blueprint $t) use ($fkName) {
                    $t->dropForeign($fkName);
                });
            } catch (\Exception $e) {
                // Ignora se nao existir
            }
        }
    }
};

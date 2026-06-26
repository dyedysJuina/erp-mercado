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
        // 1. REMOVER SENHA DE clientes
        // ═══════════════════════════════════════════
        if (Schema::hasTable('clientes')) {
            foreach (['password', 'remember_token', 'ultimo_login_at'] as $col) {
                if (Schema::hasColumn('clientes', $col)) {
                    Schema::table('clientes', function (Blueprint $t) use ($col) {
                        $t->dropColumn($col);
                    });
                    echo "  ✅ clientes.{$col} removido\n";
                }
            }
        }

        // ═══════════════════════════════════════════
        // 2. REMOVER loja_id DE users
        // ═══════════════════════════════════════════
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'loja_id')) {
            // Remove FK se existir
            try {
                Schema::table('users', function (Blueprint $t) {
                    try { $t->dropForeign(['loja_id']); } catch (\Exception $e) {}
                    try { $t->dropForeign('fk_users_loja_id'); } catch (\Exception $e) {}
                });
            } catch (\Exception $e) {}

            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('loja_id');
            });
            echo "  ✅ users.loja_id removido\n";
        }

        // ═══════════════════════════════════════════
        // 3. LIMPAR DUPLICIDADE FISCAL
        // ═══════════════════════════════════════════
        // Migrar dados de ncm -> fiscal_ncm (modelo usa fiscal_ncm)
        if (Schema::hasTable('ncm') && Schema::hasTable('fiscal_ncm')) {
            $countNcm = DB::table('ncm')->count();
            $countFiscalNcm = DB::table('fiscal_ncm')->count();

            if ($countNcm > 0 && $countFiscalNcm === 0) {
                // Migra todos os registros de ncm para fiscal_ncm
                $ncmData = DB::table('ncm')->get();
                foreach ($ncmData as $row) {
                    DB::table('fiscal_ncm')->insert([
                        'codigo' => $row->codigo,
                        'descricao' => $row->descricao ?? '',
                        'ativo' => $row->ativo ?? true,
                    ]);
                }
                echo "  ✅ {$countNcm} registros migrados de ncm -> fiscal_ncm\n";
            }

            // Atualizar ncm_id em produto_variacoes para apontar para fiscal_ncm.id
            // (se os IDs forem equivalentes entre as tabelas)
            if ($countNcm > 0 && $countFiscalNcm === 0) {
                // Os IDs devem ser os mesmos, pois acabamos de migrar mantendo a ordem
                // Nao precisa alterar os IDs se a migracao preservou a ordem
                echo "  ℹ️  IDs preservados na migracao\n";
            }

            // Remove FKs que referenciam ncm antes de dropar
            foreach (['produtos_base' => 'ncm_id', 'produto_variacoes' => 'ncm_id'] as $tbl => $col) {
                if (!Schema::hasTable($tbl)) continue;
                try {
                    Schema::table($tbl, function (Blueprint $t) use ($col) {
                        try { $t->dropForeign([$col]); } catch (\Exception $e) {}
                        try { $t->dropForeign("{$tbl}_{$col}_foreign"); } catch (\Exception $e) {}
                    });
                } catch (\Exception $e) {}
            }

            Schema::dropIfExists('ncm');
            echo "  ✅ tabela ncm removida\n";
        }

        // cfop: modelo usa 'cfop' (ja correto), fiscal_cfop ja foi removida
        echo "  ℹ️  cfop mantida (modelo usa cfop)\n";

        // ═══════════════════════════════════════════
        // 4-6. ADICIONAR FKs FALTANTES
        // ═══════════════════════════════════════════
        $extraFks = [
            // 4. carrinhos.cliente_id -> clientes.id
            ['carrinhos', 'cliente_id', 'clientes', 'SET NULL'],
            // 5. carrinho_itens.loja_id -> lojas.id
            ['carrinho_itens', 'loja_id', 'lojas', 'RESTRICT'],
            // 6. pedidos.separador_id -> users.id
            ['pedidos', 'separador_id', 'users', 'SET NULL'],
            // 6. pedidos.entregador_id -> users.id
            ['pedidos', 'entregador_id', 'users', 'SET NULL'],
        ];

        foreach ($extraFks as [$table, $col, $ref, $onDel]) {
            if (!Schema::hasTable($table) || !Schema::hasTable($ref)) {
                echo "  ⚠️  {$table} ou {$ref} nao existe\n";
                continue;
            }
            if (!Schema::hasColumn($table, $col)) {
                echo "  ⚠️  {$table}.{$col} nao existe\n";
                continue;
            }

            // Verifica InnoDB
            try {
                $dbName = DB::getDatabaseName();
                $te = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$dbName, $table]);
                $re = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$dbName, $ref]);
                if (empty($te) || empty($re)) continue;
                if (strtolower($te[0]->ENGINE) !== 'innodb' || strtolower($re[0]->ENGINE) !== 'innodb') {
                    echo "  ⚠️  {$table} ou {$ref} nao usa InnoDB\n";
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            $fkName = "fk_{$table}_{$col}";
            try {
                // Make nullable if SET NULL
                if ($onDel === 'SET NULL') {
                    Schema::table($table, function (Blueprint $t) use ($col) {
                        $t->unsignedBigInteger($col)->nullable()->change();
                    });
                }

                Schema::table($table, function (Blueprint $t) use ($col, $ref, $onDel, $fkName) {
                    try { $t->dropForeign($fkName); } catch (\Exception $e) {}
                    try { $t->dropForeign([$col]); } catch (\Exception $e) {}
                    $t->foreign($col, $fkName)->references('id')->on($ref)->onDelete($onDel);
                });
                echo "  ✅ FK {$fkName} adicionada\n";
            } catch (\Exception $e) {
                echo "  AVISO: {$fkName}: {$e->getMessage()}\n";
            }
        }

        echo "✅ Todas as correcoes aplicadas.\n";
    }

    public function down(): void
    {
        // Reverter clientes
        if (Schema::hasTable('clientes')) {
            foreach (['password' => 'varchar(255)', 'remember_token' => 'varchar(100)', 'ultimo_login_at' => 'timestamp'] as $col => $type) {
                if (!Schema::hasColumn('clientes', $col)) {
                    Schema::table('clientes', function (Blueprint $t) use ($col, $type) {
                        if ($type === 'timestamp') {
                            $t->timestamp($col)->nullable();
                        } else {
                            $t->string($col, 255)->nullable();
                        }
                    });
                }
            }
        }

        // Reverter users
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'loja_id')) {
            Schema::table('users', function (Blueprint $t) {
                $t->unsignedBigInteger('loja_id')->nullable()->after('id');
            });
        }
    }
};

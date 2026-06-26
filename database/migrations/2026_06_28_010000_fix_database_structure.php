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
        // 1. UNIFICAR LOGIN: user_id em clientes
        // ═══════════════════════════════════════════
        if (Schema::hasTable('clientes')) {
            Schema::table('clientes', function (Blueprint $t) {
                if (!Schema::hasColumn('clientes', 'user_id')) {
                    $t->unsignedBigInteger('user_id')->nullable()->after('id');
                }
            });

            // Migrar dados: criar user para cada cliente com email
            $clientes = DB::table('clientes')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereNull('user_id')
                ->get();

            foreach ($clientes as $c) {
                try {
                    $existingUser = DB::table('users')->where('email', $c->email)->first();
                    if ($existingUser) {
                        DB::table('clientes')->where('id', $c->id)->update(['user_id' => $existingUser->id]);
                    } else {
                        $senha = $c->password ?: ($c->senha_hash ?: '$2y$10$' . md5(uniqid()));
                        $userId = DB::table('users')->insertGetId([
                            'name' => $c->nome,
                            'email' => $c->email,
                            'password' => $senha,
                            'whatsapp' => $c->whatsapp,
                            'ativo' => $c->ativo ?? true,
                            'created_at' => $c->created_at ?? now(),
                            'updated_at' => $c->updated_at ?? now(),
                        ]);
                        DB::table('clientes')->where('id', $c->id)->update(['user_id' => $userId]);
                    }
                } catch (\Exception $e) {
                    echo "  AVISO: Cliente #{$c->id} ({$c->email}) ignorado: {$e->getMessage()}\n";
                }
            }

            // FK de user_id
            try {
                $tablesInnoDB = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND ENGINE = 'InnoDB' AND TABLE_NAME = 'clientes'", [DB::getDatabaseName()]);
                if (!empty($tablesInnoDB)) {
                    Schema::table('clientes', function (Blueprint $t) {
                        try { $t->dropForeign('fk_clientes_user_id'); } catch (\Exception $e) {}
                        $t->foreign('user_id', 'fk_clientes_user_id')
                            ->references('id')
                            ->on('users')
                            ->onDelete('SET NULL');
                    });
                }
            } catch (\Exception $e) {}

            // Remover coluna morta senha_hash
            if (Schema::hasColumn('clientes', 'senha_hash')) {
                Schema::table('clientes', function (Blueprint $t) {
                    $t->dropColumn('senha_hash');
                });
            }

            echo "  ✅ clientes: user_id adicionado, senha_hash removido\n";
        }

        // ═══════════════════════════════════════════
        // 2. CARRINHO MULTI-LOJA
        // ═══════════════════════════════════════════
        if (Schema::hasTable('carrinhos') && Schema::hasColumn('carrinhos', 'loja_id')) {
            // Remove FK first if exists
            try {
                Schema::table('carrinhos', function (Blueprint $t) {
                    try { $t->dropForeign(['loja_id']); } catch (\Exception $e) {}
                    try { $t->dropForeign('fk_carrinhos_loja_id'); } catch (\Exception $e) {}
                });
            } catch (\Exception $e) {}

            Schema::table('carrinhos', function (Blueprint $t) {
                $t->dropColumn('loja_id');
            });
            echo "  ✅ carrinhos: loja_id removido\n";
        } else {
            echo "  ℹ️  carrinhos.loja_id ja foi removido\n";
        }

        // ═══════════════════════════════════════════
        // 3. ELIMINAR DUPLICIDADE FISCAL
        // ═══════════════════════════════════════════
        $duplicated = [
            ['ncm', 'Tabela ncm (sobra da migracao - modelo usa fiscal_ncm)'],
            ['fiscal_cfop', 'Tabela fiscal_cfop (sobra - modelo usa cfop)'],
        ];

        foreach ($duplicated as [$table, $reason]) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                if ($count === 0) {
                    Schema::dropIfExists($table);
                    echo "  ✅ {$table} removida (vazia): {$reason}\n";
                } else {
                    echo "  ⚠️  {$table} tem {$count} registros - NAO removida: {$reason}\n";
                }
            }
        }

        // ═══════════════════════════════════════════
        // 4. FKs FALTANTES EM produto_variacoes
        // ═══════════════════════════════════════════
        if (Schema::hasTable('produto_variacoes')) {
            $fkCandidates = [
                ['ncm_id', 'fiscal_ncm', 'fk_variacoes_ncm'],
                ['cfop_id', 'cfop', 'fk_variacoes_cfop'],
                ['cest_id', 'cest', 'fk_variacoes_cest'],
            ];

            foreach ($fkCandidates as [$column, $refTable, $fkName]) {
                if (!Schema::hasColumn('produto_variacoes', $column)) continue;
                if (!Schema::hasTable($refTable)) continue;

                try {
                    // Check if both tables use InnoDB
                    $tableEngine = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'produto_variacoes'", [DB::getDatabaseName()]);
                    $refEngine = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [DB::getDatabaseName(), $refTable]);
                    if (empty($tableEngine) || empty($refEngine)) continue;
                    if (strtolower($tableEngine[0]->ENGINE) !== 'innodb' || strtolower($refEngine[0]->ENGINE) !== 'innodb') continue;

                    Schema::table('produto_variacoes', function (Blueprint $t) use ($column, $refTable, $fkName) {
                        try { $t->dropForeign($fkName); } catch (\Exception $e) {}
                        $t->foreign($column, $fkName)
                            ->references('id')
                            ->on($refTable)
                            ->onDelete('SET NULL');
                    });
                    echo "  ✅ FK {$fkName} adicionada\n";
                } catch (\Exception $e) {
                    echo "  AVISO: {$fkName} ignorada: {$e->getMessage()}\n";
                }
            }
        }

        // ═══════════════════════════════════════════
        // 5. FKs RESTANTES EM OUTRAS TABELAS
        // ═══════════════════════════════════════════
        $extraFks = [
            ['pedidos', 'separador_id', 'users', 'id', 'SET NULL'],
            ['pedidos', 'entregador_id', 'users', 'id', 'SET NULL'],
            ['notificacoes', 'usuario_id', 'users', 'id', 'CASCADE'],
            ['pdv_vendas', 'usuario_id', 'users', 'id', 'SET NULL'],
        ];

        foreach ($extraFks as [$table, $col, $ref, $refCol, $onDel]) {
            if (!Schema::hasTable($table) || !Schema::hasTable($ref)) continue;
            if (!Schema::hasColumn($table, $col)) continue;

            // Check InnoDB
            $te = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [DB::getDatabaseName(), $table]);
            $re = DB::select("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [DB::getDatabaseName(), $ref]);
            if (empty($te) || empty($re)) continue;
            if (strtolower($te[0]->ENGINE) !== 'innodb' || strtolower($re[0]->ENGINE) !== 'innodb') continue;

            $fkName = "fk_{$table}_{$col}";
            try {
                // Make nullable if SET NULL
                if ($onDel === 'SET NULL') {
                    try {
                        Schema::table($table, function (Blueprint $t) use ($col) {
                            $t->unsignedBigInteger($col)->nullable()->change();
                        });
                    } catch (\Exception $e) {}
                }

                Schema::table($table, function (Blueprint $t) use ($col, $ref, $refCol, $onDel, $fkName) {
                    try { $t->dropForeign($fkName); } catch (\Exception $e) {}
                    $t->foreign($col, $fkName)->references($refCol)->on($ref)->onDelete($onDel);
                });
                echo "  ✅ FK {$fkName} adicionada\n";
            } catch (\Exception $e) {
                echo "  AVISO: {$fkName} ignorada: {$e->getMessage()}\n";
            }
        }

        echo "✅ Migracao de estrutura concluida.\n";
    }

    public function down(): void
    {
        // Reverter clientes
        if (Schema::hasTable('clientes')) {
            if (!Schema::hasColumn('clientes', 'senha_hash')) {
                Schema::table('clientes', function (Blueprint $t) {
                    $t->string('senha_hash', 255)->nullable()->after('whatsapp');
                });
            }
            try {
                Schema::table('clientes', function (Blueprint $t) {
                    $t->dropForeign('fk_clientes_user_id');
                });
            } catch (\Exception $e) {}
            if (Schema::hasColumn('clientes', 'user_id')) {
                Schema::table('clientes', function (Blueprint $t) {
                    $t->dropColumn('user_id');
                });
            }
        }

        // Reverter carrinhos
        if (Schema::hasTable('carrinhos') && !Schema::hasColumn('carrinhos', 'loja_id')) {
            Schema::table('carrinhos', function (Blueprint $t) {
                $t->unsignedBigInteger('loja_id')->nullable()->after('session_token');
            });
        }
    }
};

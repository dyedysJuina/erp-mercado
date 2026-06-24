<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            $table->unique('pdv_venda_id', 'financeiro_lancamentos_pdv_venda_id_unique');
            $table->index(['status', 'reconcilied_at'], 'financeiro_lancamentos_status_reconcilied_idx');
        });

        Schema::table('financeiro_categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('financeiro_categorias', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('financeiro_centros_custo', function (Blueprint $table) {
            if (!Schema::hasColumn('financeiro_centros_custo', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('financeiro_contas', function (Blueprint $table) {
            if (!Schema::hasColumn('financeiro_contas', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            $table->dropIndex('financeiro_lancamentos_status_reconcilied_idx');
            $table->dropUnique('financeiro_lancamentos_pdv_venda_id_unique');
        });
    }
};

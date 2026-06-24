<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('financeiro_lancamentos', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->after('conta_id');
            }
            if (!Schema::hasColumn('financeiro_lancamentos', 'fornecedor_id')) {
                $table->unsignedBigInteger('fornecedor_id')->nullable()->after('usuario_id');
            }
            if (!Schema::hasColumn('financeiro_lancamentos', 'cliente_id')) {
                $table->unsignedBigInteger('cliente_id')->nullable()->after('fornecedor_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            $columns = ['usuario_id', 'fornecedor_id', 'cliente_id'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('financeiro_lancamentos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

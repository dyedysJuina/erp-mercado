<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos', 'separador_id')) {
                $table->unsignedBigInteger('separador_id')->nullable()->after('endereco_id');
                $table->unsignedBigInteger('entregador_id')->nullable()->after('separador_id');
            }
        });
        Schema::table('pedidos_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_itens', 'separado_por')) {
                $table->unsignedBigInteger('separado_por')->nullable()->after('observacao_separador');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['separador_id', 'entregador_id']);
        });
        Schema::table('pedidos_itens', function (Blueprint $table) {
            $table->dropColumn('separado_por');
        });
    }
};

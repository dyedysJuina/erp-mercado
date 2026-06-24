<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_pedidos', function (Blueprint $table): void {
            if (!Schema::hasColumn('compras_pedidos', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('previsao_entrega');
                $table->string('condicao_pagamento', 50)->nullable()->after('observacoes');
                $table->string('tipo_frete', 10)->nullable()->default('CIF')->after('condicao_pagamento');
                $table->string('tipo_pedido', 20)->nullable()->default('normal')->after('tipo_frete');
                $table->date('data_pedido')->nullable()->after('tipo_pedido');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compras_pedidos', function (Blueprint $table): void {
            $table->dropColumn(['observacoes', 'condicao_pagamento', 'tipo_frete', 'tipo_pedido', 'data_pedido']);
        });
    }
};

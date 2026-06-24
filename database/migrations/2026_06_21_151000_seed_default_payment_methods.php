<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('formas_pagamento')) {
            return;
        }

        foreach ([
            ['nome' => 'Dinheiro', 'tipo' => 'dinheiro'],
            ['nome' => 'PIX', 'tipo' => 'pix'],
            ['nome' => 'Cartão de débito', 'tipo' => 'cartao_debito'],
            ['nome' => 'Cartão de crédito', 'tipo' => 'cartao_credito'],
        ] as $method) {
            DB::table('formas_pagamento')->updateOrInsert(
                ['nome' => $method['nome'], 'tipo' => $method['tipo']],
                ['taxa_percentual' => 0, 'prazo_recebimento_dias' => 0, 'ativo' => true],
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('formas_pagamento')) {
            DB::table('formas_pagamento')->whereIn('nome', [
                'Dinheiro', 'PIX', 'Cartão de débito', 'Cartão de crédito',
            ])->delete();
        }
    }
};

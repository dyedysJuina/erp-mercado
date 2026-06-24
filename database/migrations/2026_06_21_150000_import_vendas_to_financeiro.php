<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Importa vendas existentes para o financeiro
        $vendas = DB::table('pdv_vendas')
            ->where('status', 'concluida')
            ->whereNotIn('id', function ($q) {
                $q->select('pdv_venda_id')->from('financeiro_lancamentos')
                    ->whereNotNull('pdv_venda_id');
            })
            ->get();

        $count = 0;
        foreach ($vendas as $v) {
            DB::table('financeiro_lancamentos')->insert([
                'empresa_id' => 1,
                'loja_id' => $v->loja_id,
                'pdv_venda_id' => $v->id,
                'tipo' => 'receita',
                'descricao' => "Venda PDV #{$v->id}",
                'valor' => $v->total,
                'data_competencia' => $v->finalizada_at ?? $v->created_at,
                'data_vencimento' => $v->finalizada_at ?? $v->created_at,
                'data_pagamento' => $v->finalizada_at ?? $v->created_at,
                'status' => 'pago',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }

        if ($count > 0) {
            echo "  >> {$count} vendas importadas para o financeiro.\n";
        }
    }

    public function down(): void
    {
        DB::table('financeiro_lancamentos')->whereNotNull('pdv_venda_id')->delete();
    }
};

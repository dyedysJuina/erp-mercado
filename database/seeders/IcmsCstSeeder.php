<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcmsCstSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // CST para Regime Normal
            ['00', 'Tributada integralmente', 'normal'],
            ['10', 'Tributada e com cobrança do ICMS por substituição tributária', 'normal'],
            ['20', 'Com redução de base de cálculo', 'normal'],
            ['30', 'Isenta ou não tributada e com cobrança do ICMS por substituição tributária', 'normal'],
            ['40', 'Isenta', 'normal'],
            ['41', 'Não tributada', 'normal'],
            ['50', 'Suspensão', 'normal'],
            ['51', 'Diferimento', 'normal'],
            ['60', 'ICMS cobrado anteriormente por substituição tributária', 'normal'],
            ['70', 'Com redução de base de cálculo e cobrança do ICMS por substituição tributária', 'normal'],
            ['90', 'Outras', 'normal'],

            // CSOSN para Simples Nacional
            ['101', 'Tributada pelo Simples Nacional com permissão de crédito', 'simples_nacional'],
            ['102', 'Tributada pelo Simples Nacional sem permissão de crédito', 'simples_nacional'],
            ['103', 'Isenta ou não tributada no Simples Nacional', 'simples_nacional'],
            ['201', 'Tributada pelo Simples Nacional com permissão de crédito e com cobrança do ICMS por substituição tributária', 'simples_nacional'],
            ['202', 'Tributada pelo Simples Nacional sem permissão de crédito e com cobrança do ICMS por substituição tributária', 'simples_nacional'],
            ['203', 'Isenta ou não tributada no Simples Nacional e com cobrança do ICMS por substituição tributária', 'simples_nacional'],
            ['300', 'Imune', 'simples_nacional'],
            ['400', 'Não tributada pelo Simples Nacional', 'simples_nacional'],
            ['500', 'ICMS cobrado anteriormente por substituição tributária (substituído) ou por antecipação', 'simples_nacional'],
            ['900', 'Outros', 'simples_nacional'],
        ];

        $rows = [];
        foreach ($data as $item) {
            $rows[] = ['codigo' => $item[0], 'descricao' => $item[1], 'regime' => $item[2], 'ativo' => true];
        }
        DB::table('icms_cst')->insertOrIgnore($rows);
    }
}

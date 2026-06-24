<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoteTestSeeder extends Seeder
{
    public function run(): void
    {
        $lojaId = 2;
        $skus = ['ARZ001', 'LEI001', 'REF001', 'ACU001', 'CAF001'];

        $lotes = [
            'ARZ001' => [
                ['LOTE-ARZ-A', '2026-06-01', '2026-10-01', 500],
                ['LOTE-ARZ-B', '2026-06-10', '2026-10-05', 500],
            ],
            'LEI001' => [
                ['LOTE-LEI-A', '2026-06-05', '2026-07-20', 200],
                ['LOTE-LEI-B', '2026-06-15', '2026-09-10', 800],
            ],
            'REF001' => [
                ['LOTE-REF-A', '2026-05-01', '2026-09-01', 300],
                ['LOTE-REF-B', '2026-06-01', '2026-11-01', 700],
            ],
            'ACU001' => [
                ['LOTE-ACU-A', '2026-04-01', '2027-04-01', 1000],
            ],
            'CAF001' => [
                ['LOTE-CAF-A', '2026-03-01', '2026-08-01', 100],
                ['LOTE-CAF-B', '2026-05-01', '2026-12-01', 500],
                ['LOTE-CAF-C', '2026-06-10', '2026-06-22', 400],
            ],
        ];

        foreach ($lotes as $sku => $dados) {
            $var = DB::table('produto_variacoes')->where('sku', $sku)->first(['id']);
            if (!$var) { $this->command->warn("SKU $sku não encontrado"); continue; }

            foreach ($dados as $d) {
                $exists = DB::table('estoque_lotes')
                    ->where('produto_variacao_id', $var->id)
                    ->where('numero_lote', $d[0])
                    ->exists();

                if (!$exists) {
                    DB::table('estoque_lotes')->insert([
                        'loja_id' => $lojaId,
                        'produto_variacao_id' => $var->id,
                        'numero_lote' => $d[0],
                        'data_fabricacao' => $d[1],
                        'data_validade' => $d[2],
                        'quantidade_atual' => $d[3],
                        'custo_unitario' => 0,
                    ]);
                    $this->command->info("✓ Lote {$d[0]} — {$sku} ({$d[3]} un, vence {$d[2]})");
                }
            }
        }

        $this->command->info("\n✅ Lotes de teste criados!");
        $this->command->warn("⚠️  Lote CAF-C (Café) vence hoje! Teste a validação de vencimento.");
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadoCidadeSeeder extends Seeder
{
    public function run(): void
    {
        $ufs = [
            'RO' => 'Rondônia', 'AC' => 'Acre', 'AM' => 'Amazonas', 'RR' => 'Roraima',
            'PA' => 'Pará', 'AP' => 'Amapá', 'TO' => 'Tocantins', 'MA' => 'Maranhão',
            'PI' => 'Piauí', 'CE' => 'Ceará', 'RN' => 'Rio Grande do Norte',
            'PB' => 'Paraíba', 'PE' => 'Pernambuco', 'AL' => 'Alagoas', 'SE' => 'Sergipe',
            'BA' => 'Bahia', 'MG' => 'Minas Gerais', 'ES' => 'Espírito Santo',
            'RJ' => 'Rio de Janeiro', 'SP' => 'São Paulo', 'PR' => 'Paraná',
            'SC' => 'Santa Catarina', 'RS' => 'Rio Grande do Sul',
            'MS' => 'Mato Grosso do Sul', 'MT' => 'Mato Grosso', 'GO' => 'Goiás',
            'DF' => 'Distrito Federal',
        ];

        $order = 0;
        foreach ($ufs as $uf => $nome) {
            $order++;
            DB::table('estados')->updateOrInsert(
                ['uf' => $uf],
                ['nome' => $nome, 'id' => $order]
            );
        }

        // Pega o ID real do MT
        $mtId = DB::table('estados')->where('uf', 'MT')->value('id');

        if ($mtId) {
            $cidades = [
                ['estado_id' => $mtId, 'nome' => 'Cuiabá', 'codigo_ibge' => '5103403'],
                ['estado_id' => $mtId, 'nome' => 'Várzea Grande', 'codigo_ibge' => '5108402'],
                ['estado_id' => $mtId, 'nome' => 'Rondonópolis', 'codigo_ibge' => '5107602'],
                ['estado_id' => $mtId, 'nome' => 'Juína', 'codigo_ibge' => '5105150'],
            ];

            foreach ($cidades as $c) {
                DB::table('cidades')->updateOrInsert(
                    ['estado_id' => $c['estado_id'], 'nome' => $c['nome']],
                    $c
                );
            }
        }

        $this->command->info('27 estados e 4 cidades de MT criados.');
    }
}

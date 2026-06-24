<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegraFiscalNcmSeeder extends Seeder
{
    public function run(): void
    {
        $regras = [
            // ISENTOS / IMUNES (CSOSN 300)
            ['01xx', '300', 'Animais vivos'],

            // ISENTOS (CSOSN 103)
            ['07xx', '103', 'Hortaliças, legumes e verduras'],
            ['08xx', '103', 'Frutas frescas'],
            ['0201', '103', 'Carnes bovinas frescas'],
            ['0203', '103', 'Carnes suínas frescas'],
            ['0207', '103', 'Carnes de aves frescas'],
            ['0407', '103', 'Ovos'],
            ['0409', '103', 'Mel natural'],
            ['0901', '103', 'Café não torrado'],
            ['0902', '103', 'Chá'],
            ['1001', '103', 'Trigo'],
            ['1005', '103', 'Milho'],
            ['1006', '103', 'Arroz em casca'],
            ['1101', '103', 'Farinha de trigo'],
            ['1102', '103', 'Farinha de milho'],
            ['1507', '103', 'Óleo de soja'],
            ['1509', '103', 'Azeite de oliva'],

            // SUBSTITUIÇÃO TRIBUTÁRIA (CSOSN 500) - ST em MT
            ['2202', '500', 'Refrigerantes e outras bebidas não alcoólicas'],
            ['2203', '500', 'Cervejas e chopes'],
            ['2204', '500', 'Vinhos'],
            ['2205', '500', 'Vermutes e outros vinhos'],
            ['2206', '500', 'Saquê e outras bebidas fermentadas'],
            ['2207', '500', 'Aguardentes e destilados'],
            ['2208', '500', 'Bebidas alcoólicas destiladas'],
            ['2209', '500', 'Vinagres'],
            ['2401', '500', 'Charutos e cigarrilhas'],
            ['2402', '500', 'Cigarros'],
            ['2403', '500', 'Outros fumígenos'],
            ['2711', '500', 'GLP (gás de cozinha)'],
            ['3303', '500', 'Perfumes e águas-de-colônia'],
            ['3304', '500', 'Cosméticos e maquiagens'],
            ['3305', '500', 'Preparações capilares'],
            ['3306', '500', 'Higiene bucal'],
            ['3307', '500', 'Produtos para barbear'],
            ['3401', '500', 'Sabões e detergentes'],
            ['3402', '500', 'Produtos de limpeza'],
            ['3924', '500', 'Utensílios domésticos plásticos'],
            ['3926', '500', 'Artigos de plástico para uso doméstico'],
            ['4818', '500', 'Papel higiênico e papel para uso doméstico'],
            ['4823', '500', 'Papel toalha e guardanapos'],
            ['7013', '500', 'Artigos de vidro para mesa e copa'],
            ['8212', '500', 'Lâminas de barbear'],
            ['8414', '500', 'Ventiladores'],
            ['8415', '500', 'Aparelhos de ar condicionado'],
            ['8450', '500', 'Máquinas de lavar louça'],
            ['8451', '500', 'Máquinas de lavar roupa'],
            ['8452', '500', 'Máquinas de costura'],
            ['8509', '500', 'Eletroportáteis (aspiradores, liquidificadores)'],
            ['8516', '500', 'Ferros elétricos, secadores de cabelo'],
            ['8517', '500', 'Aparelhos telefônicos'],
            ['9003', '500', 'Óculos e armações'],
            ['9101', '500', 'Relógios de pulso'],
        ];

        $data = [];
        foreach ($regras as $r) {
            $data[] = [
                'ncm_prefix' => $r[0],
                'csosn' => $r[1],
                'descricao' => $r[2],
                'ativo' => true,
            ];
        }
        DB::table('regras_fiscais_ncm')->insertOrIgnore($data);

        $this->command->info('✓ ' . count($regras) . ' regras fiscais NCM → CSOSN inseridas');
    }
}

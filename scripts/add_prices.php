<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$precos = [
    'ARZ001' => ['preco_venda' => 22.90, 'preco_custo' => 18.00],
    'LEI001' => ['preco_venda' => 4.50, 'preco_custo' => 3.20],
    'REF001' => ['preco_venda' => 7.90, 'preco_custo' => 5.50],
    'ACU001' => ['preco_venda' => 3.99, 'preco_custo' => 2.80],
    'CAF001' => ['preco_venda' => 12.90, 'preco_custo' => 9.00],
];

foreach ($precos as $sku => $p) {
    $var = DB::table('produto_variacoes')->where('sku', $sku)->first(['id']);
    if (!$var) { echo "SKU $sku não encontrado\n"; continue; }

    $exists = DB::table('tabela_precos_itens')
        ->where('tabela_preco_id', 1)
        ->where('produto_variacao_id', $var->id)
        ->first();

    $margem = round(($p['preco_venda'] - $p['preco_custo']) / $p['preco_venda'] * 100, 1);
    $data = ['preco_venda' => $p['preco_venda'], 'preco_custo' => $p['preco_custo'], 'margem_percentual' => $margem];

    if ($exists) {
        DB::table('tabela_precos_itens')->where('id', $exists->id)->update($data);
        echo "Atualizado $sku: R$ {$p['preco_venda']}\n";
    } else {
        $data['tabela_preco_id'] = 1;
        $data['produto_variacao_id'] = $var->id;
        DB::table('tabela_precos_itens')->insert($data);
        echo "Inserido $sku: R$ {$p['preco_venda']}\n";
    }
}

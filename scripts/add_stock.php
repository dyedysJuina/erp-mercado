<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$skus = ['ARZ001','LEI001','REF001','ACU001','CAF001'];
$lojaId = 2;

foreach ($skus as $sku) {
    $var = DB::table('produto_variacoes')->where('sku', $sku)->first(['id']);
    if (!$var) { echo "SKU $sku não encontrado\n"; continue; }

    $exists = DB::table('estoque_saldos')
        ->where('loja_id', $lojaId)
        ->where('produto_variacao_id', $var->id)
        ->first();

    if ($exists) {
        DB::table('estoque_saldos')->where('id', $exists->id)->update([
            'quantidade_atual' => 1000,
            'quantidade_reservada' => 0,
        ]);
        echo "$sku: atualizado estoque 1000\n";
    } else {
        DB::table('estoque_saldos')->insert([
            'loja_id' => $lojaId,
            'produto_variacao_id' => $var->id,
            'quantidade_atual' => 1000,
            'quantidade_reservada' => 0,
            'estoque_minimo' => 10,
            'estoque_maximo' => 5000,
        ]);
        echo "$sku: inserido estoque 1000\n";
    }
}

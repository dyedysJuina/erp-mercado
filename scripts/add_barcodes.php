<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$skus = [
    'ARZ001' => '7891000012345',
    'LEI001' => '7891000023456',
    'REF001' => '7891000034567',
    'ACU001' => '7891000045678',
    'CAF001' => '7891000056789',
];

foreach ($skus as $sku => $codigo) {
    $var = DB::table('produto_variacoes')->where('sku', $sku)->first(['id']);
    if (!$var) { echo "SKU $sku não encontrado\n"; continue; }

    $exists = DB::table('produto_codigos_barras')
        ->where('produto_variacao_id', $var->id)
        ->exists();

    if ($exists) {
        echo "SKU $sku já tem código: $codigo\n";
    } else {
        DB::table('produto_codigos_barras')->insert([
            'produto_variacao_id' => $var->id,
            'codigo' => $codigo,
            'tipo' => 'ean13',
            'principal' => true,
            'descricao' => 'Código de teste',
        ]);
        echo "SKU $sku: $codigo criado\n";
    }
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testando regras fiscais ===\n\n";

$ncmCodigos = ['10063000', '04011010', '22021000', '17019900', '09011100'];
foreach ($ncmCodigos as $cod) {
    $sugerido = App\Models\RegraFiscalNcm::sugerirCsosn($cod);
    $ncm = DB::table('ncm')->where('codigo', $cod)->first();
    echo "NCM {$cod} ({$ncm->descricao}) → CSOSN {$sugerido}";
    if ($sugerido === '500') echo " ← ST";
    echo "\n";
}

echo "\n=== Produtos de teste ===\n";
$skus = ['ARZ001', 'LEI001', 'REF001', 'ACU001', 'CAF001'];
foreach ($skus as $sku) {
    $v = App\Models\ProdutoVariacao::with('ncm')->where('sku', $sku)->first();
    if (!$v) continue;
    $impostos = app(App\Services\CalculoImpostoService::class)->calcular($v, 10);
    echo "{$sku} ({$v->ncm?->codigo}): CSOSN={$impostos['icms']['cst']} ST={$impostos['icms']['substituicao_tributaria']} origem={$impostos['csosn_origem']}\n";
}

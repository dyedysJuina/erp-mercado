<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Limpa CSOSN do Refrigerante para testar a regra automatica
DB::table('produto_variacoes')->where('sku', 'REF001')->update(['cst_icms' => null]);
DB::table('produtos_base')->where('id', function ($q) {
    $q->select('produto_base_id')->from('produto_variacoes')->where('sku', 'REF001')->limit(1);
})->update(['cst_icms' => null]);

echo "CSOSN do REF001 limpo. A regra vai sugerir automaticamente.\n";

$v = App\Models\ProdutoVariacao::with('ncm', 'cfop')->where('sku', 'REF001')->first();
echo "NCM do produto: " . ($v->ncm?->codigo ?? 'NENHUM') . "\n";

$impostos = app(App\Services\CalculoImpostoService::class)->calcular($v, 10);
echo "CSOSN calculado: {$impostos['icms']['cst']}\n";
echo "ST: " . ($impostos['icms']['substituicao_tributaria'] ? 'SIM' : 'NAO') . "\n";
echo "Origem: {$impostos['csosn_origem']}\n";

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$venda = App\Models\PdvVenda::with('itens')->find(5);
if (!$venda) { echo "Venda #5 nao encontrada\n"; exit; }

foreach ($venda->itens as $item) {
    echo "Item: produto_variacao_id={$item->produto_variacao_id}\n";
    $var = $item->variacao;
    if (!$var) { echo "  variacao: NULL\n"; continue; }
    echo "  variacao: id={$var->id} nome={$var->nome_completo}\n";
    echo "  ncm_id={$var->ncm_id}\n";
    $var->load(['ncm','cfop']);
    echo "  ncm loaded: " . ($var->relationLoaded('ncm') ? 'YES' : 'NO') . "\n";
    echo "  ncm: " . ($var->ncm ? $var->ncm->codigo : 'NULL') . "\n";
    echo "  cfop: " . ($var->cfop ? $var->cfop->codigo : 'NULL') . "\n";
    
    // Direct DB query
    $ncmDb = DB::table('ncm')->where('id', $var->ncm_id)->first();
    echo "  ncm direct: " . ($ncmDb ? $ncmDb->codigo : 'NULL') . "\n";
    echo "\n";
}

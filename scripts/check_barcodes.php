<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Códigos de barras cadastrados ===\n";
$codigos = DB::table('produto_codigos_barras')->get();
if ($codigos->isEmpty()) {
    echo "NENHUM código de barras cadastrado.\n";
} else {
    foreach ($codigos as $c) {
        $var = DB::table('produto_variacoes')->where('id', $c->produto_variacao_id)->first();
        $nome = $var ? $var->nome_completo : '?';
        $sku = $var ? $var->sku : 'sem sku';
        echo "#{$c->id} var #{$c->produto_variacao_id} ({$sku} - {$nome}): {$c->codigo} ({$c->tipo})";
        if ($c->principal) echo ' ★principal';
        echo "\n";
    }
}

echo "\n=== Variações sem código de barras ===\n";
$vars = DB::table('produto_variacoes')->where('ativo', true)->get();
foreach ($vars as $v) {
    $tem = DB::table('produto_codigos_barras')->where('produto_variacao_id', $v->id)->exists();
    if (!$tem) echo "#{$v->id} {$v->sku} - {$v->nome_completo} (sem código)\n";
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$c = DB::table('produto_codigos_barras')->first();
if ($c) {
    $v = DB::table('produto_variacoes')->where('id', $c->produto_variacao_id)->first();
    echo "Código: {$c->codigo}\nTipo: {$c->tipo}\nVariacao: {$v->nome_completo} (SKU: {$v->sku})\n";
} else {
    echo "Nenhum código de barras\n";
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ncm = DB::table('ncm')->whereIn('codigo', ['04011010','10063000','22021000','17019900','09011100'])->get(['id','codigo','descricao']);
foreach ($ncm as $n) echo "id:{$n->id} {$n->codigo} {$n->descricao}\n";

echo "\n--- Produtos Variacao NCM ---\n";
$vars = DB::table('produto_variacoes')->whereIn('sku', ['ARZ001','LEI001','REF001','ACU001','CAF001'])->get(['id','sku','ncm_id']);
foreach ($vars as $v) echo "var #{$v->id} {$v->sku} ncm_id:{$v->ncm_id}\n";

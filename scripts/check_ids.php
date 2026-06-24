<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find NCM IDs
$ncms = DB::table('ncm')->whereIn('codigo', ['10063000','04011010','22021000','17019900','09011100'])->get(['id','codigo','descricao']);
echo "=== NCM ===\n";
foreach ($ncms as $n) echo "{$n->id}: {$n->codigo} - {$n->descricao}\n";

// Find CFOP ID for 5102
$cfop = DB::table('cfop')->where('codigo','5102')->first();
echo "\n=== CFOP 5102 ===\n";
echo $cfop ? "{$cfop->id}: {$cfop->codigo} - {$cfop->descricao}\n" : "NOT FOUND\n";

// Find CEST ID for arroz/cereais
$cest = DB::table('cest')->where('codigo','0101200')->first();
echo "\n=== CEST 0101200 (Massas) ===\n";
echo $cest ? "{$cest->id}: {$cest->codigo} - {$cest->descricao}\n" : "NOT FOUND\n";

// Count existing categorias
$cats = DB::table('categorias')->count();
echo "\n=== Categorias existentes: $cats ===\n";

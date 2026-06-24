<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$fps = DB::table('formas_pagamento')->get();
if ($fps->isEmpty()) {
    echo "NENHUMA forma de pagamento encontrada!\n";
    $inserts = [
        ['nome' => 'Dinheiro', 'tipo' => 'dinheiro', 'ativo' => true],
        ['nome' => 'Cartão Débito', 'tipo' => 'debito', 'ativo' => true],
        ['nome' => 'Cartão Crédito', 'tipo' => 'credito', 'ativo' => true],
        ['nome' => 'PIX', 'tipo' => 'pix', 'ativo' => true],
    ];
    foreach ($inserts as $fp) {
        $id = DB::table('formas_pagamento')->insertGetId($fp);
        echo "Criada #$id: {$fp['nome']}\n";
    }
} else {
    foreach ($fps as $fp) echo "#{$fp->id}: {$fp->nome} ({$fp->tipo})\n";
}

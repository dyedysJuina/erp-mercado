<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check if Atributo model table exists
try {
    $count = DB::table('atributos')->count();
    echo "Atributos na tabela: $count\n";
    if ($count > 0) {
        $attrs = DB::table('atributos')->get(['id','nome','slug','tipo','ativo']);
        foreach ($attrs as $a) echo "  #{$a->id} {$a->nome} ({$a->slug}) - {$a->tipo} - ativo:{$a->ativo}\n";
    }
} catch (\Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

// Check if ProdutoVariacaoAtributo table exists
try {
    $count2 = DB::table('produto_variacao_atributos')->count();
    echo "\nProdutoVariacaoAtributos: $count2\n";
} catch (\Exception $e) {
    echo "\nProdutoVariacaoAtributos: Tabela não existe - " . $e->getMessage() . "\n";
}

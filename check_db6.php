<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Test: FULLTEXT search for short word 'cola' ===\n";
    $r = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE)", ['cola*']);
    foreach ($r as $row) { echo "ID {$row->id}: {$row->nome_completo}\n"; }
    if (empty($r)) echo "NOT FOUND - but 'Cola' is 4 chars!\n";

    echo "\n=== Verify: Does the search work for 'uht'? ===\n";
    $r = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE)", ['uht']);
    foreach ($r as $row) { echo "ID {$row->id}: {$row->nome_completo}\n"; }
    if (empty($r)) echo "NOT FOUND\n";
    
    echo "\n=== Check what happens with adicionarProdutoPorCodigo for 'refrigerante' ===\n";
    $codigo = 'refrigerante';
    $barcode = DB::table('produto_codigos_barras')->where('codigo', $codigo)->first();
    echo "Barcode lookup for '$codigo': " . ($barcode ? 'FOUND' : 'NOT FOUND') . "\n";
    
    echo "\n=== Check SKU for 'Refrigerante Cola 2L' ===\n";
    $prod = DB::table('produto_variacoes')->where('id', 5)->first();
    echo "SKU: " . ($prod->sku ?? 'NULL') . "\n";
    echo "nome_completo: {$prod->nome_completo}\n";

    echo "\n=== Simulate the FULL query that would be generated ===\n";
    $q = 'refrigerante';
    $storeId = 2;
    $tableId = DB::table('lojas')->whereKey($storeId)->value('tabela_preco_id');
    echo "For store ID 2, tabela_preco_id = $tableId\n";
    
    echo "\n=== What about searching with LIKE on nome_completo? ===\n";
    $r = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE nome_completo LIKE ?", ['%refrigerante%']);
    echo "LIKE '%refrigerante%' found: " . count($r) . "\n";
    foreach ($r as $row) { echo "ID {$row->id}: {$row->nome_completo}\n"; }

    echo "\n=== Check layout CSS that might clip the dropdown ===\n";
    // Check the main container for overflow
    echo "Layout CSS in venda-manager.blade.php has noSidebar\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

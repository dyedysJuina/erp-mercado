<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Test FULLTEXT search for 'refrigerante' ===\n";
    $results = DB::select("SELECT id, nome_completo, sku FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE)", ['refrigerante*']);
    foreach ($results as $r) {
        echo "ID: {$r->id} | Nome: {$r->nome_completo} | SKU: {$r->sku}\n";
    }
    if (empty($results)) {
        echo "NO MATCHES!\n";
        echo "Trying LIKE instead:\n";
        $results2 = DB::select("SELECT id, nome_completo, sku FROM produto_variacoes WHERE nome_completo LIKE ?", ['%refrigerante%']);
        foreach ($results2 as $r) {
            echo "ID: {$r->id} | Nome: {$r->nome_completo} | SKU: {$r->sku}\n";
        }
    }

    echo "\n=== Simulating productsForCurrentStore with store 2 (tabela_preco_id=1) ===\n";
    $storeId = 2;
    $tableId = DB::table('lojas')->whereKey($storeId)->value('tabela_preco_id');
    echo "Store {$storeId} tabela_preco_id: " . var_export($tableId, true) . "\n";
    $results3 = DB::select("
        SELECT pv.id, pv.nome_completo, pv.ativo, price.preco_venda
        FROM produto_variacoes pv
        JOIN tabela_precos_itens price ON price.produto_variacao_id = pv.id AND price.tabela_preco_id = ?
        WHERE pv.ativo = 1
        AND price.preco_venda > 0
        AND MATCH(pv.nome_completo) AGAINST(? IN BOOLEAN MODE)
        LIMIT 15
    ", [$tableId, 'refrigerante*']);
    foreach ($results3 as $r) {
        echo "ID: {$r->id} | Nome: {$r->nome_completo} | Preco: {$r->preco_venda}\n";
    }
    if (empty($results3)) {
        echo "NO MATCHES with store 2!\n";
    }

    echo "\n=== Simulating productsForCurrentStore with store 1 (tabela_preco_id=NULL) ===\n";
    $storeId = 1;
    $tableId = DB::table('lojas')->whereKey($storeId)->value('tabela_preco_id');
    echo "Store {$storeId} tabela_preco_id: " . var_export($tableId, true) . "\n";
    $castTableId = (int) $tableId; // Simulating what the code does
    echo "Store {$storeId} tabela_preco_id after (int) cast: " . var_export($castTableId, true) . "\n";
    $results4 = DB::select("
        SELECT pv.id, pv.nome_completo, pv.ativo, price.preco_venda
        FROM produto_variacoes pv
        JOIN tabela_precos_itens price ON price.produto_variacao_id = pv.id AND price.tabela_preco_id = ?
        WHERE pv.ativo = 1
        AND price.preco_venda > 0
        AND MATCH(pv.nome_completo) AGAINST(? IN BOOLEAN MODE)
        LIMIT 15
    ", [$castTableId, 'refrigerante*']);
    foreach ($results4 as $r) {
        echo "ID: {$r->id} | Nome: {$r->nome_completo} | Preco: {$r->preco_venda}\n";
    }
    if (empty($results4)) {
        echo "NO MATCHES with store 1 (cast to 0) - as expected!\n";
    }

    echo "\n=== Checking full produtos_variacoes columns ===\n";
    $cols = DB::select('SHOW COLUMNS FROM produto_variacoes');
    foreach ($cols as $c) {
        echo $c->Field . " (" . $c->Type . ") " . ($c->Null === 'YES' ? 'NULL' : 'NOT NULL') . " " . ($c->Default !== null ? 'default: ' . $c->Default : '') . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

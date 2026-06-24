<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== produto_codigos_barras columns ===\n";
    $cols = DB::select('SHOW COLUMNS FROM produto_codigos_barras');
    foreach ($cols as $c) {
        echo $c->Field . " (" . $c->Type . ")\n";
    }
    echo "\n=== FULLTEXT indexes on produto_variacoes ===\n";
    $idxs = DB::select("SHOW INDEX FROM produto_variacoes WHERE Index_type = 'FULLTEXT'");
    foreach ($idxs as $idx) {
        echo $idx->Key_name . " | " . $idx->Column_name . " | " . $idx->Index_type . "\n";
    }
    if (empty($idxs)) {
        echo "NO FULLTEXT INDEX FOUND on produto_variacoes!\n";
    }
    echo "\n=== Indexes on produto_variacoes ===\n";
    $all = DB::select('SHOW INDEX FROM produto_variacoes');
    foreach ($all as $idx) {
        echo $idx->Key_name . " | " . $idx->Column_name . " | " . $idx->Index_type . "\n";
    }
    echo "\n=== Checking if Refrigerante exists ===\n";
    $prods = DB::select("SELECT id, nome_completo, ativo FROM produto_variacoes WHERE nome_completo LIKE ?", ['%Refrigerante%']);
    foreach ($prods as $p) {
        echo "ID: {$p->id} | Nome: {$p->nome_completo} | Ativo: " . ($p->ativo ? 'true' : 'false') . "\n";
    }
    if (empty($prods)) {
        echo "NO products with 'Refrigerante' found.\n";
    }
    echo "\n=== All products in produto_variacoes ===\n";
    $all = DB::select('SELECT id, nome_completo, ativo FROM produto_variacoes LIMIT 20');
    foreach ($all as $p) {
        echo "ID: {$p->id} | Nome: {$p->nome_completo} | Ativo: " . ($p->ativo ? 'true' : 'false') . "\n";
    }
    if (empty($all)) {
        echo "NO products found at all.\n";
    }
    echo "\n=== tabela_precos_itens ===\n";
    $precos = DB::select('SELECT * FROM tabela_precos_itens LIMIT 10');
    foreach ($precos as $p) {
        echo json_encode($p) . "\n";
    }
    if (empty($precos)) {
        echo "NO price items found in tabela_precos_itens.\n";
    }
    echo "\n=== Lojas ===\n";
    $lojas = DB::select('SELECT id, nome, tabela_preco_id FROM lojas');
    foreach ($lojas as $l) {
        echo "ID: {$l->id} | Nome: {$l->nome} | TabelaPrecoID: {$l->tabela_preco_id}\n";
    }
    if (empty($lojas)) {
        echo "NO stores found.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

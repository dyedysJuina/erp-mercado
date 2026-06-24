<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SUMMARY ===\n\n";

echo "1. Product 'Refrigerante Cola 2L' exists? ";
$p = DB::table('produto_variacoes')->where('id', 5)->first(['id','nome_completo','ativo']);
echo ($p ? "YES (ID: {$p->id}, Nome: '{$p->nome_completo}', Ativo: {$p->ativo})" : "NO") . "\n\n";

echo "2. FULLTEXT index exists? ";
$idx = DB::select("SHOW INDEX FROM produto_variacoes WHERE Index_type = 'FULLTEXT'");
echo (!empty($idx) ? "YES, on column: {$idx[0]->Column_name}" : "NO") . "\n\n";

echo "3. MySQL FULLTEXT config:\n";
$configs = DB::select("SHOW VARIABLES WHERE Variable_name IN ('innodb_ft_min_token_size','innodb_ft_max_token_size','innodb_ft_enable_stopword')");
foreach ($configs as $c) echo "   {$c->Variable_name} = {$c->Value}\n";
echo "\n";

echo "4. Does FULLTEXT search for 'refrigerante' work? ";
$r = DB::select("SELECT id FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 1", ['refrigerante*']);
echo (count($r) > 0 ? "YES" : "NO (because 'refrigerante' = 12 chars > innodb_ft_max_token_size=10)") . "\n\n";

echo "5. Does FULLTEXT search for 'cola' work? ";
$r = DB::select("SELECT id FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 1", ['cola*']);
echo (count($r) > 0 ? "YES (finds Refrigerante Cola 2L by the word 'Cola' = 4 chars)" : "NO") . "\n\n";

echo "6. Store 'SUPERMERCADO CANARIO' (ID:2, tabela_preco_id:1) works? ";
$tableId = 1;
$r = DB::select("SELECT pv.id, pv.nome_completo FROM produto_variacoes pv JOIN tabela_precos_itens price ON price.produto_variacao_id = pv.id AND price.tabela_preco_id = ? WHERE pv.ativo=1 AND price.preco_venda>0 AND MATCH(pv.nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 5", [$tableId, 'cola*']);
echo (count($r) > 0 ? "YES, found: " . implode(', ', array_map(fn($x)=>$x->nome_completo, $r)) : "NO") . "\n\n";

echo "7. Primary Root Cause:\n";
echo "   MySQL config 'innodb_ft_max_token_size=10' (line 126 in my.ini)\n";
echo "   Word 'refrigerante' has 12 characters, exceeding this limit.\n";
echo "   Words >10 chars are NOT indexed by FULLTEXT index.\n\n";

echo "8. Secondary Issue (would prevent ALL results):\n";
$lojas = DB::select('SELECT id, nome, tabela_preco_id FROM lojas');
foreach ($lojas as $l) {
    $tableIdVal = $l->tabela_preco_id ?? 'NULL';
    echo "   Store '{$l->nome}' (ID:{$l->id}): tabela_preco_id = " . var_export($l->tabela_preco_id, true) . "\n";
    if ($l->tabela_preco_id === null) {
        $cast = (int) $l->tabela_preco_id;
        echo "   -> When cast to (int) becomes: {$cast} -> JOIN with tabela_precos_itens.tabela_preco_id = {$cast} -> NO MATCHES!\n";
    }
}

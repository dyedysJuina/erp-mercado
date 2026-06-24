<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Test 1: BOOLEAN MODE with exact word (no wildcard) ===\n";
    $r1 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['refrigerante']);
    foreach ($r1 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r1)) echo "NO MATCHES\n";

    echo "\n=== Test 2: BOOLEAN MODE with wildcard ===\n";
    $r2 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['refrigerante*']);
    foreach ($r2 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r2)) echo "NO MATCHES\n";

    echo "\n=== Test 3: NATURAL LANGUAGE MODE ===\n";
    $r3 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN NATURAL LANGUAGE MODE) LIMIT 10", ['refrigerante']);
    foreach ($r3 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r3)) echo "NO MATCHES\n";

    echo "\n=== Test 4: BOOLEAN MODE with + operator ===\n";
    $r4 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['+refrigerante']);
    foreach ($r4 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r4)) echo "NO MATCHES\n";

    echo "\n=== Test 5: Check FT min word length config ===\n";
    $r5 = DB::select("SHOW VARIABLES LIKE '%ft_min%'");
    foreach ($r5 as $r) { echo "{$r->Variable_name}: {$r->Value}\n"; }
    $r5b = DB::select("SHOW VARIABLES LIKE 'innodb_ft_min%'");
    foreach ($r5b as $r) { echo "{$r->Variable_name}: {$r->Value}\n"; }

    echo "\n=== Test 6: Check stopword config ===\n";
    $r6 = DB::select("SHOW VARIABLES LIKE '%stopword%'");
    foreach ($r6 as $r) { echo "{$r->Variable_name}: {$r->Value}\n"; }

    echo "\n=== Test 7: Check if 'refrigerante' is in stopword list ===\n";
    $r7 = DB::select("SELECT * FROM INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD WHERE word = 'refrigerante'");
    if (empty($r7)) echo "'refrigerante' is NOT a default stopword\n";
    else echo "'refrigerante' IS a stopword!\n";

    echo "\n=== Test 8: Search for a shorter word ===\n";
    $r8 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['arroz*']);
    foreach ($r8 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r8)) echo "NO MATCHES for 'arroz*'\n";

    echo "\n=== Test 9: Check all words in nome_completo by tokenizing ===\n";
    $all = DB::select("SELECT id, nome_completo FROM produto_variacoes LIMIT 20");
    foreach ($all as $p) {
        $words = preg_split('/[\s,.;:\-]+/', strtolower($p->nome_completo));
        $short = array_filter($words, fn($w) => strlen($w) >= 3);
        echo "ID {$p->id}: words = " . implode(', ', $short) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

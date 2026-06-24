<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Check table engine ===\n";
    $r = DB::select("SHOW TABLE STATUS WHERE Name = 'produto_variacoes'");
    echo "Engine: " . $r[0]->Engine . "\n";
    echo "Rows: " . $r[0]->Rows . "\n";

    echo "\n=== Try rebuilding FULLTEXT index ===\n";
    // Note: DROP INDEX and ADD INDEX to fix
    // Check if dropping/adding would help
    echo "Will attempt: DROP INDEX and recreate...\n";

    echo "\n=== Test if LIKE query finds 'refrigerante' ===\n";
    $r1 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE nome_completo LIKE ?", ['%refrigerante%']);
    echo "LIKE found: " . count($r1) . " results\n";
    foreach ($r1 as $r) { echo "  ID: {$r->id} | {$r->nome_completo}\n"; }

    echo "\n=== Test 'cafe' FULLTEXT (also should exist) ===\n";
    $r2 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['cafe*']);
    foreach ($r2 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r2)) echo "NO MATCHES for 'cafe*'\n";

    echo "\n=== Test short word 'arroz' (exact word) ===\n";
    $r3 = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 10", ['arroz']);
    foreach ($r3 as $r) { echo "ID: {$r->id} | {$r->nome_completo}\n"; }
    if (empty($r3)) echo "NO MATCHES for 'arroz'\n";

    echo "\n=== Check innodb_ft settings more thoroughly ===\n";
    $r4 = DB::select("SHOW VARIABLES LIKE 'innodb_ft%'");
    foreach ($r4 as $r) { echo "{$r->Variable_name}: {$r->Value}\n"; }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "=== Test words of various lengths ===\n";
    $tests = [
        'leite*',       // 5 chars
        'integral*',    // 8 chars
        'açúcar*',      // 6 chars (with accent)
        'acucar*',      // 6 chars (without accent)
        'koblenz*',     // 7 chars
        'masson*',      // 6 chars
        'extraforte*',  // 10 chars - at the limit
        'extrafort*',   // 9 chars
        'refrigerante*', // 12 chars - OVER limit
        'refrige*',     // 7 chars
        'supermercado*', // 12 chars - OVER limit
    ];
    
    foreach ($tests as $test) {
        $word = rtrim($test, '*');
        $len = strlen($word);
        $r = DB::select("SELECT id, nome_completo FROM produto_variacoes WHERE MATCH(nome_completo) AGAINST(? IN BOOLEAN MODE) LIMIT 5", [$test]);
        $count = count($r);
        $status = $count > 0 ? "✅ FOUND ($count)" : "❌ NOT FOUND";
        echo "$test (len=$len): $status\n";
        foreach ($r as $row) {
            echo "   -> {$row->nome_completo}\n";
        }
    }

    echo "\n=== Verify: max_token_size=10 means words >10 chars not indexed ===\n";
    echo "Word 'refrigerante' = 12 chars -> EXCLUDED from index\n";
    echo "Word 'extraforte' = 10 chars -> at boundary (might be excluded)\n";
    
    echo "\n=== Check if innodb_ft_min_token_size=0 means empty/null analysis ===\n";
    // If min=0, even 0-length tokens... let me check if that breaks anything

    echo "\n=== All nome_completo values and their word lengths ===\n";
    $all = DB::select("SELECT id, nome_completo FROM produto_variacoes");
    foreach ($all as $p) {
        $words = preg_split('/[\s,.;:\-]+/', $p->nome_completo);
        foreach ($words as $w) {
            $clean = preg_replace('/[^a-zA-ZÀ-ÿ0-9]/u', '', $w);
            if (strlen($clean) > 0) {
                $len = strlen($clean);
                $status = $len > 10 ? '❌ NOT INDEXED (>10)' : ($len < 3 ? '⚠️ <3 chars' : '✅');
                echo "  '{$clean}' (len={$len}) $status\n";
            }
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

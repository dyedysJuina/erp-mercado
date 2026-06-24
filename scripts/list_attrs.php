<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $attrs = DB::table('atributos')->get();
    echo "Total atributos: " . count($attrs) . "\n";
    foreach ($attrs as $a) {
        echo "  #{$a->id} {$a->nome} ({$a->slug}) - {$a->tipo}\n";
    }
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

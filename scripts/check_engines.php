<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['ncm','cfop','cest','icms_cst','produtos_base','produtos_variacoes'];
foreach ($tables as $t) {
    $r = DB::select("SHOW TABLE STATUS WHERE Name = ?", [$t]);
    echo "$t: {$r[0]->Engine}\n";
}

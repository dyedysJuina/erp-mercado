<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$tables = DB::select("SHOW TABLES LIKE '%atributo%'");
foreach ($tables as $t) {
    $name = (array)$t;
    echo current($name) . "\n";
}

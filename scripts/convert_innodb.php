<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['ncm','cfop','cest','icms_cst'];
foreach ($tables as $t) {
    DB::statement("ALTER TABLE `$t` ENGINE = InnoDB");
    echo "Converted $t to InnoDB\n";
}

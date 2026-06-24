<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select('SHOW COLUMNS FROM produto_imagens');
foreach ($cols as $c) echo "{$c->Field} ({$c->Type})\n";

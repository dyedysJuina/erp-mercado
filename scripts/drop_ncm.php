<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

DB::statement('ALTER TABLE produtos_base DROP COLUMN ncm_id');
echo "Dropped ncm_id from produtos_base\n";

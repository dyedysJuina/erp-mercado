<?php
require 'C:\wamp64\www\erp_mercado\vendor\autoload.php';
$app = require_once 'C:\wamp64\www\erp_mercado\bootstrap\app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$dbName = DB::getDatabaseName();
$tables = DB::select("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND ENGINE != 'InnoDB'", [$dbName]);
echo "Non-InnoDB tables:\n";
foreach ($tables as $t) {
    echo "  {$t->TABLE_NAME} => {$t->ENGINE}\n";
    // Convert to InnoDB
    DB::statement("ALTER TABLE `{$t->TABLE_NAME}` ENGINE = InnoDB");
    echo "    -> converted to InnoDB\n";
}
echo "DONE\n";

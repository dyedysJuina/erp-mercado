<?php
require 'C:\wamp64\www\erp_mercado\vendor\autoload.php';
$app = require_once 'C:\wamp64\www\erp_mercado\bootstrap\app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fks = [
    ['pedidos', 'separador_id', 'users'],
    ['pedidos', 'entregador_id', 'users'],
];

foreach ($fks as $fk) {
    list($table, $col, $ref) = $fk;
    $name = "fk_{$table}_{$col}";

    // First make nullable
    try {
        Schema::table($table, function ($b) use ($col) {
            $b->unsignedBigInteger($col)->nullable()->change();
        });
    } catch (Exception $e) {
        echo "Change nullable: {$e->getMessage()}\n";
    }

    // Then add FK (without trying to drop first)
    try {
        Schema::table($table, function ($b) use ($col, $ref, $name) {
            $b->foreign($col, $name)->references('id')->on($ref)->onDelete('SET NULL');
        });
        echo "OK: {$name}\n";
    } catch (Exception $e) {
        echo "ERRO: {$name}: {$e->getMessage()}\n";
    }
}
echo "DONE\n";

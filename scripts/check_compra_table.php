<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("DESCRIBE compra_pedidos");
    echo "compra_pedidos columns:\n";
    foreach ($r as $row) echo "  {$row['Field']} ({$row['Type']})\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\nTrying compras_pedidos...\n";
    try {
        $r = $c->query("DESCRIBE compras_pedidos");
        echo "compras_pedidos columns:\n";
        foreach ($r as $row) echo "  {$row['Field']} ({$row['Type']})\n";
    } catch(Exception $e2) {
        echo "Error2: " . $e2->getMessage();
    }
}

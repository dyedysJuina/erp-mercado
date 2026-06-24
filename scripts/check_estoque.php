<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("DESCRIBE estoque_saldos");
    echo "estoque_saldos columns:\n";
    foreach ($r as $row) echo "  {$row['Field']} ({$row['Type']})\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}

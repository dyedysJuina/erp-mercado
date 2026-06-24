<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("DESCRIBE pdv_caixa_movimentos");
    echo "pdv_caixa_movimentos:\n";
    foreach ($r as $row) echo "  {$row['Field']} ({$row['Type']})\n";
    
    $r2 = $c->query("DESCRIBE pdv_caixas_aberturas");
    echo "\npdv_caixas_aberturas:\n";
    foreach ($r2 as $row) echo "  {$row['Field']} ({$row['Type']})\n";

    $r3 = $c->query("DESCRIBE pdv_caixas");
    echo "\npdv_caixas:\n";
    foreach ($r3 as $row) echo "  {$row['Field']} ({$row['Type']})\n";
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

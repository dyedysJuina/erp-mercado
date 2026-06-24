<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("DESCRIBE pdv_devolucoes");
    echo "pdv_devolucoes:\n";
    foreach ($r as $row) echo "  {$row['Field']} ({$row['Type']})\n";
    $r2 = $c->query("DESCRIBE pdv_devolucao_itens");
    echo "\npdv_devolucao_itens:\n";
    foreach ($r2 as $row) echo "  {$row['Field']} ({$row['Type']})\n";
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("SHOW TABLES LIKE '%produto_var%'");
    foreach ($r as $row) echo $row[0] . "\n";
} catch(Exception $e) {
    echo $e->getMessage();
}

<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query('SHOW TABLES LIKE "categorias_atributos"');
    echo $r->rowCount() ? 'EXISTS' : 'NOT_FOUND';
    $r = $c->query('SHOW COLUMNS FROM categorias_atributos');
    echo "\n";
    foreach ($r as $row) echo $row['Field'] . ' ' . $row['Type'] . "\n";
} catch(Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $r = $c->query("SHOW COLUMNS FROM atributos WHERE Field='tipo'");
    foreach ($r as $row) echo $row['Type'];
} catch(Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $c->exec("DROP TABLE IF EXISTS model_has_permissions");
    $c->exec("DROP TABLE IF EXISTS model_has_roles");
    $c->exec("DROP TABLE IF EXISTS role_has_permissions");
    $c->exec("DROP TABLE IF EXISTS permissions");
    $c->exec("DROP TABLE IF EXISTS roles");
    echo "Tables dropped.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}

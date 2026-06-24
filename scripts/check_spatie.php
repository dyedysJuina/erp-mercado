<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $tables = ['roles','permissions','model_has_roles','model_has_permissions','role_has_permissions'];
    foreach ($tables as $t) {
        $r = $c->query("SHOW TABLES LIKE '$t'");
        echo "$t: " . $r->rowCount() . "\n";
        if ($r->rowCount() > 0) {
            $c2 = $c->query("SELECT COUNT(*) FROM $t");
            echo "  records: " . $c2->fetchColumn() . "\n";
        }
    }
} catch(Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $c->exec("ALTER TABLE atributos MODIFY COLUMN tipo ENUM('texto','numero','decimal','booleano','lista','checkbox','radio','data') NOT NULL DEFAULT 'texto'");
    echo '✅ Tipo column updated to include checkbox and radio';
} catch(Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}

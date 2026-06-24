<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    $c->exec("ALTER TABLE produto_variacao_atributos ADD UNIQUE INDEX pv_atributo_unique (produto_variacao_id, atributo_id)");
    echo "Unique index added.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trying to drop existing index first...\n";
    try {
        $c->exec("ALTER TABLE produto_variacao_atributos DROP INDEX pv_atributo_unique");
        $c->exec("ALTER TABLE produto_variacao_atributos ADD UNIQUE INDEX pv_atributo_unique (produto_variacao_id, atributo_id)");
        echo "Unique index added after drop.\n";
    } catch(Exception $e2) {
        echo "Still failed: " . $e2->getMessage() . "\n";
    }
}

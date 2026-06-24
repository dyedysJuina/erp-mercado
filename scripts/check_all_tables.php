<?php
try {
    $c = new PDO('mysql:host=127.0.0.1;dbname=erp_supermercado;charset=utf8mb4','root','');
    
    $tables = [
        'pdv_vendas' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='pdv_vendas'",
        'pdv_venda_pagamentos' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='pdv_venda_pagamentos'",
        'pdv_venda_itens' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='pdv_venda_itens'",
        'financeiro_lancamentos' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='financeiro_lancamentos'",
        'financeiro_categorias' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='financeiro_categorias'",
        'estoque_saldos' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='estoque_saldos'",
        'estoque_lotes' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='estoque_lotes'",
        'produto_variacoes' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='produto_variacoes'",
        'pdv_venda_pagamentos' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='pdv_venda_pagamentos'",
        'formas_pagamento' => "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='erp_supermercado' AND TABLE_NAME='formas_pagamento'",
    ];
    
    foreach ($tables as $name => $sql) {
        echo "\n=== $name ===\n";
        $r = $c->query($sql);
        $cols = [];
        foreach ($r as $row) $cols[] = $row['COLUMN_NAME'];
        echo implode(', ', $cols) . "\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}

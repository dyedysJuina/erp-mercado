<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Produtos Base ===\n";
$bases = DB::table('produtos_base')->get(['id','nome','ncm_id','cfop_id','cst_icms','origem_mercadoria','ativo']);
foreach ($bases as $b) echo "#{$b->id} {$b->nome} | ncm_id:{$b->ncm_id} cfop_id:{$b->cfop_id} cst:{$b->cst_icms} origem:{$b->origem_mercadoria} ativo:{$b->ativo}\n";

echo "\n=== Variações ===\n";
$vars = DB::table('produto_variacoes')->get(['id','produto_base_id','nome_completo','sku','ncm_id','cfop_id','cst_icms','ativo']);
foreach ($vars as $v) echo "#{$v->id} (base #{$v->produto_base_id}) {$v->nome_completo} | sku:{$v->sku} ncm_id:{$v->ncm_id} cfop_id:{$v->cfop_id} ativo:{$v->ativo}\n";

echo "\n=== Lojas ===\n";
$lojas = DB::table('lojas')->get(['id','nome','tabela_preco_id']);
foreach ($lojas as $l) echo "#{$l->id} {$l->nome} tabela_preco_id:{$l->tabela_preco_id}\n";

echo "\n=== Tabelas Preço ===\n";
$tabs = DB::table('tabela_precos')->get(['id','nome']);
foreach ($tabs as $t) echo "#{$t->id} {$t->nome}\n";

echo "\n=== Preços dos produtos ===\n";
$precos = DB::table('tabela_precos_itens')->get(['id','tabela_preco_id','produto_variacao_id','preco_venda']);
foreach ($precos as $p) echo "#{$p->id} tabela:{$p->tabela_preco_id} variacao:{$p->produto_variacao_id} preco:{$p->preco_venda}\n";

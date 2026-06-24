<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "═══════════════════════════════════════════\n";
echo "  TESTE COMPLETO - MÓDULO FISCAL\n";
echo "═══════════════════════════════════════════\n\n";

$pass = 0;
$fail = 0;

function test(string $label, bool $cond, string $detail = ''): void {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  ✅ $label\n"; }
    else { $fail++; echo "  ❌ $label" . ($detail ? " — $detail" : '') . "\n"; }
}

// ── 1. TABELAS FISCAIS ──
echo "─── 1. TABELAS FISCAIS ───\n";

test('Tabela ncm existe', Schema::hasTable('ncm'));
test('Tabela cfop existe', Schema::hasTable('cfop'));
test('Tabela cest existe', Schema::hasTable('cest'));
test('Tabela icms_cst existe', Schema::hasTable('icms_cst'));
test('Tabela regras_fiscais_ncm existe', Schema::hasTable('regras_fiscais_ncm'));

$ncmCount = DB::table('ncm')->count();
test("NCM populados ($ncmCount registros)", $ncmCount > 100);

$cfopCount = DB::table('cfop')->count();
test("CFOP populados ($cfopCount registros)", $cfopCount > 50);

$regrasCount = DB::table('regras_fiscais_ncm')->count();
test("Regras fiscais NCM→CSOSN ($regrasCount regras)", $regrasCount > 10);

// ── 2. PRODUTOS DE TESTE ──
echo "\n─── 2. PRODUTOS DE TESTE ───\n";

$skus = ['ARZ001','LEI001','REF001','ACU001','CAF001'];
foreach ($skus as $sku) {
    $v = DB::table('produto_variacoes')->where('sku', $sku)->first();
    test("Produto $sku existe", (bool)$v, $sku . ' não encontrado');
}

$variacoes = DB::table('produto_variacoes')->whereIn('sku', $skus)->get();
foreach ($variacoes as $v) {
    test("{$v->sku} tem ncm_id", !empty($v->ncm_id), "ncm_id vazio");
    test("{$v->sku} tem cfop_id", !empty($v->cfop_id), "cfop_id vazio");
}

// ── 3. PREÇOS E ESTOQUE ──
echo "\n─── 3. PREÇOS E ESTOQUE ───\n";

foreach ($variacoes as $v) {
    $preco = DB::table('tabela_precos_itens')
        ->where('produto_variacao_id', $v->id)
        ->where('tabela_preco_id', 1)
        ->first();
    test("{$v->sku} tem preço", $preco && $preco->preco_venda > 0, "preco_venda=" . ($preco->preco_venda ?? 'NULL'));

    $estoque = DB::table('estoque_saldos')
        ->where('produto_variacao_id', $v->id)
        ->where('loja_id', 2)
        ->first();
    test("{$v->sku} tem estoque 1000", $estoque && $estoque->quantidade_atual == 1000);
}

// ── 4. CÓDIGOS DE BARRAS ──
echo "\n─── 4. CÓDIGOS DE BARRAS ───\n";

foreach ($variacoes as $v) {
    $cb = DB::table('produto_codigos_barras')
        ->where('produto_variacao_id', $v->id)
        ->where('principal', true)
        ->first();
    test("{$v->sku} tem código de barras", (bool)$cb, $cb ? "codigo={$cb->codigo}" : 'sem código');
}

// ── 5. REGRAS FISCAIS NCM → CSOSN ──
echo "\n─── 5. REGRAS FISCAIS NCM → CSOSN ───\n";

$testCases = [
    '10063000' => ['esperado' => '103', 'label' => 'Arroz (10063000) → CSOSN 103 (isento)'],
    '04011010' => ['esperado' => '102', 'label' => 'Leite (04011010) → CSOSN 102 (tributado)'],
    '22021000' => ['esperado' => '500', 'label' => 'Refrigerante (22021000) → CSOSN 500 (ST)'],
    '17019900' => ['esperado' => '102', 'label' => 'Açúcar (17019900) → CSOSN 102 (tributado)'],
    '09011100' => ['esperado' => '103', 'label' => 'Café (09011100) → CSOSN 103 (isento)'],
    '22030000' => ['esperado' => '500', 'label' => 'Cerveja (22030000) → CSOSN 500 (ST)'],
    '33041000' => ['esperado' => '500', 'label' => 'Batom (33041000) → CSOSN 500 (ST)'],
    '34011100' => ['esperado' => '500', 'label' => 'Sabão (34011100) → CSOSN 500 (ST)'],
];

foreach ($testCases as $ncm => $data) {
    $resultado = App\Models\RegraFiscalNcm::sugerirCsosn($ncm);
    test($data['label'], $resultado === $data['esperado'], "retornou $resultado, esperado {$data['esperado']}");
}

// ── 6. CÁLCULO DE IMPOSTOS ──
echo "\n─── 6. CÁLCULO DE IMPOSTOS ───\n";

foreach ($variacoes as $v) {
    $model = App\Models\ProdutoVariacao::find($v->id);
    $impostos = app(App\Services\CalculoImpostoService::class)->calcular($model, 10.00);
    $csosn = $impostos['icms']['cst'];
    $st = $impostos['icms']['substituicao_tributaria'];
    $origem = $impostos['csosn_origem'];

    echo "  {$v->sku}: CSOSN={$csosn} ST=" . ($st ? 'SIM' : 'NÃO') . " origem={$origem}\n";

    $ncm = DB::table('ncm')->where('id', $v->ncm_id)->first();
    $cod = $ncm->codigo ?? '00000000';
    $regra = App\Models\RegraFiscalNcm::sugerirCsosn($cod);

    if ($regra === '500') {
        test("{$v->sku} → ST detectado", $st === true);
    }
}

// ── 7. NFC-e SERVICE ──
echo "\n─── 7. NFC-e SERVICE ───\n";

test('NfceService class existe', class_exists(\App\Services\NfceService::class));
test('CalculoImpostoService class existe', class_exists(\App\Services\CalculoImpostoService::class));
test('FiscalPerfil model existe', class_exists(\App\Models\FiscalPerfil::class));
test('FiscalDocumento model existe', class_exists(\App\Models\FiscalDocumento::class));

// ── 8. LOJA COM DADOS FISCAIS ──
echo "\n─── 8. DADOS DA LOJA ───\n";

$loja = DB::table('lojas')->where('id', 2)->first();
test('Loja SUPERMERCADO CANARIO existe', (bool)$loja);
if ($loja) {
    test('Loja tem CNPJ', !empty($loja->cnpj));
    test('Loja tem IE', !empty($loja->inscricao_estadual));
    test('Loja tem logradouro', !empty($loja->logradouro));
    test('Loja tem tabela_preco_id', !empty($loja->tabela_preco_id));
    test('Loja tem cidade_id', !empty($loja->cidade_id));
}

// ── 9. FORMAS DE PAGAMENTO ──
echo "\n─── 9. FORMAS DE PAGAMENTO ───\n";

$fps = DB::table('formas_pagamento')->where('ativo', true)->count();
test("Formas de pagamento ativas ($fps)", $fps >= 3);

// ── 10. PERFIL FISCAL ──
echo "\n─── 10. PERFIL FISCAL ───\n";

$perfil = DB::table('fiscal_perfis')->where('ativo', true)->first();
test('Perfil fiscal ativo existe', (bool)$perfil);
if ($perfil) {
    test("Ambiente: " . ($perfil->ambiente === '1' ? 'Produção' : 'Homologação'), true);
    test("Regime: " . ($perfil->regime_tributario === '1' ? 'Simples Nacional' : 'Outro'), true);
}

// ── RESULTADO ──
echo "\n═══════════════════════════════════════════\n";
echo "  RESULTADO: $pass ✅  $fail ❌\n";
echo "═══════════════════════════════════════════\n";

if ($fail === 0) {
    echo "\n  🎉 TUDO OK! O sistema está pronto pra testes no PDV.\n";
    echo "\n  Próximos passos:\n";
    echo "  1. Acesse /vendas e faça uma venda completa\n";
    echo "  2. Finalize e emita a NFC-e\n";
    echo "  3. Visualize o DANFE com os dados fiscais\n";
    echo "  4. Verifique o XML gerado\n";
} else {
    echo "\n  ⚠️  $fail teste(s) falharam. Reveja os erros acima.\n";
}

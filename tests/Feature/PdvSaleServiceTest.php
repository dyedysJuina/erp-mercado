<?php

namespace Tests\Feature;

use App\Livewire\VendaManager;
use App\Services\PdvSaleService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PdvSaleServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('lojas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome');
            $table->string('status_operacional')->default('aberta');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('tabela_preco_id')->nullable();
        });
        Schema::create('pdv_caixas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->string('nome');
            $table->string('numero')->nullable();
            $table->boolean('ativo')->default(true);
        });
        Schema::create('pdv_caixas_aberturas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('caixa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('status');
            $table->decimal('valor_abertura', 12, 2);
            $table->dateTime('aberto_at');
        });
        Schema::create('formas_pagamento', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
            $table->string('tipo');
            $table->boolean('ativo')->default(true);
        });
        Schema::create('produto_variacoes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('marca_id')->nullable();
            $table->unsignedBigInteger('unidade_medida_id')->nullable();
            $table->string('nome_completo');
            $table->string('sku')->nullable();
            $table->boolean('ativo')->default(true);
            $table->decimal('quantidade_minima_venda', 12, 3)->default(1);
            $table->decimal('passo_venda', 12, 3)->default(1);
        });
        Schema::create('marcas', function (Blueprint $table): void {
            $table->id();
            $table->string('nome');
        });
        Schema::create('unidades_medida', function (Blueprint $table): void {
            $table->id();
            $table->string('sigla');
        });
        Schema::create('produto_codigos_barras', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('produto_variacao_id');
            $table->string('codigo')->unique();
            $table->boolean('principal')->default(false);
        });
        Schema::create('tabela_precos_itens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tabela_preco_id');
            $table->unsignedBigInteger('produto_variacao_id');
            $table->decimal('preco_venda', 12, 2);
        });
        Schema::create('estoque_saldos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('produto_variacao_id');
            $table->decimal('quantidade_atual', 12, 3);
            $table->decimal('quantidade_reservada', 12, 3)->default(0);
        });
        Schema::create('pdv_vendas', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('caixa_abertura_id');
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('status');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('desconto', 12, 2);
            $table->decimal('acrescimo', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
            $table->dateTime('finalizada_at');
        });
        Schema::create('pdv_venda_itens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('venda_id');
            $table->unsignedBigInteger('produto_variacao_id');
            $table->decimal('quantidade', 12, 3);
            $table->decimal('preco_unitario', 12, 2);
            $table->decimal('desconto', 12, 2);
            $table->decimal('total_item', 12, 2);
            $table->boolean('cancelado');
        });
        Schema::create('pdv_venda_pagamentos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('venda_id');
            $table->unsignedBigInteger('forma_pagamento_id');
            $table->decimal('valor', 12, 2);
            $table->integer('parcelas');
        });
        Schema::create('estoque_movimentacoes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('produto_variacao_id');
            $table->unsignedBigInteger('usuario_id');
            $table->string('origem_tipo');
            $table->unsignedBigInteger('origem_id');
            $table->string('tipo');
            $table->decimal('quantidade', 12, 3);
            $table->string('justificativa')->nullable();
            $table->timestamps();
        });
        Schema::create('financeiro_lancamentos', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('loja_id');
            $table->unsignedBigInteger('pdv_venda_id');
            $table->string('tipo');
            $table->string('descricao');
            $table->decimal('valor', 12, 2);
            $table->date('data_competencia');
            $table->date('data_vencimento');
            $table->date('data_pagamento')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        DB::table('lojas')->insert(['id' => 1, 'empresa_id' => 7, 'nome' => 'Loja teste', 'status_operacional' => 'aberta', 'ativo' => true, 'tabela_preco_id' => 5]);
        DB::table('formas_pagamento')->insert(['id' => 1, 'nome' => 'Dinheiro', 'tipo' => 'dinheiro', 'ativo' => true]);
        DB::table('produto_variacoes')->insert(['id' => 2, 'nome_completo' => 'Arroz 1 kg', 'ativo' => true, 'quantidade_minima_venda' => 1, 'passo_venda' => 1]);
        DB::table('produto_codigos_barras')->insert(['produto_variacao_id' => 2, 'codigo' => '7891234567890', 'principal' => true]);
        DB::table('tabela_precos_itens')->insert(['tabela_preco_id' => 5, 'produto_variacao_id' => 2, 'preco_venda' => 10.50]);
        DB::table('estoque_saldos')->insert(['id' => 3, 'loja_id' => 1, 'produto_variacao_id' => 2, 'quantidade_atual' => 10, 'quantidade_reservada' => 0]);
    }

    public function test_it_opens_cash_and_finalizes_an_atomic_sale(): void
    {
        $service = app(PdvSaleService::class);
        $openingId = $service->openCash(1, 9, 'R$ 100,00');
        $result = $service->finalize(1, $openingId, 9, null, [
            ['variacao_id' => 2, 'quantidade' => '2,000', 'preco' => '0,01'],
        ], '1,00', '0,50', 1, '30,00');

        $this->assertSame(20.5, $result['total']);
        $this->assertSame(9.5, $result['change']);
        $this->assertEquals(8, DB::table('estoque_saldos')->value('quantidade_atual'));
        $this->assertDatabaseHas('pdv_vendas', ['id' => $result['id'], 'total' => 20.5, 'status' => 'concluida']);
        $this->assertDatabaseHas('pdv_venda_pagamentos', ['venda_id' => $result['id'], 'valor' => 20.5]);
        $this->assertDatabaseHas('estoque_movimentacoes', ['origem_id' => $result['id'], 'tipo' => 'saida_venda_pdv']);
        $this->assertDatabaseHas('financeiro_lancamentos', ['pdv_venda_id' => $result['id'], 'empresa_id' => 7, 'valor' => 20.5]);
    }

    public function test_it_rolls_everything_back_when_stock_is_insufficient(): void
    {
        $service = app(PdvSaleService::class);
        $openingId = $service->openCash(1, 9, '0,00');

        try {
            $service->finalize(1, $openingId, 9, null, [
                ['variacao_id' => 2, 'quantidade' => '11,000'],
            ], '0,00', '0,00', 1, '200,00');
            $this->fail('A venda deveria ter sido recusada.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('pdv_vendas', 0);
            $this->assertEquals(10, DB::table('estoque_saldos')->value('quantidade_atual'));
            $this->assertDatabaseCount('estoque_movimentacoes', 0);
        }
    }

    public function test_the_pdv_adds_a_product_by_barcode_and_increments_repeated_scans(): void
    {
        Livewire::test(VendaManager::class)
            ->set('passo', 'venda')
            ->set('loja_id', '1')
            ->call('adicionarProdutoPorCodigo', " 7891234567890\r\n")
            ->assertSet('codigoBarrasLido', 'ok')
            ->assertSet('carrinho.0.variacao_id', 2)
            ->assertSet('carrinho.0.quantidade', '1.000')
            ->call('adicionarProdutoPorCodigo', '7891234567890')
            ->assertSet('carrinho.0.quantidade', '2.000')
            ->assertHasNoErrors('buscaProduto');
    }

    public function test_the_pdv_reports_an_unknown_barcode_without_changing_the_cart(): void
    {
        Livewire::test(VendaManager::class)
            ->set('passo', 'venda')
            ->set('loja_id', '1')
            ->call('adicionarProdutoPorCodigo', '0000000000000')
            ->assertSet('codigoBarrasLido', 'nao_encontrado')
            ->assertSet('carrinho', [])
            ->assertSet('buscaProduto', '0000000000000');
    }
}

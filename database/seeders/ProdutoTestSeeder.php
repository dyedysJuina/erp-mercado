<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cfop;
use App\Models\Ncm;
use App\Models\ProdutoBase;
use App\Models\ProdutoVariacao;
use App\Models\UnidadeMedida;
use App\Models\Marca;
use App\Models\Embalagem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdutoTestSeeder extends Seeder
{
    public function run(): void
    {
        $cfop = Cfop::where('codigo', '5102')->first();
        if (!$cfop) { $this->command->error('CFOP 5102 não encontrado'); return; }

        $um = UnidadeMedida::first();
        if (!$um) { $um = UnidadeMedida::create(['sigla' => 'UN', 'nome' => 'Unidade']); }

        $marca = Marca::first();
        if (!$marca) { $marca = Marca::create(['nome' => 'Genérica', 'ativo' => true]); }

        $emb = Embalagem::first();
        if (!$emb) { $emb = Embalagem::create(['nome' => 'Unidade', 'sigla' => 'Un', 'ativo' => true]); }

        $ncmMap = [];
        foreach (Ncm::whereIn('codigo', ['10063000','04011010','22021000','17019900','09011100'])->get() as $n) {
            $ncmMap[$n->codigo] = $n->id;
        }

        $categoria = $this->criarCategorias();

        $produtos = [
            [
                'nome' => 'Arroz Tipo 1',
                'ncm' => '10063000',
                'cest' => null,
                'slug_suffix' => 'arroz-tipo-1',
                'qtd' => 5,
                'sku' => 'ARZ001',
            ],
            [
                'nome' => 'Leite UHT Integral',
                'ncm' => '04011010',
                'cest' => null,
                'slug_suffix' => 'leite-uht-integral',
                'qtd' => 1,
                'sku' => 'LEI001',
            ],
            [
                'nome' => 'Refrigerante Cola 2L',
                'ncm' => '22021000',
                'cest' => '12',
                'slug_suffix' => 'refrigerante-cola-2l',
                'qtd' => 2,
                'sku' => 'REF001',
            ],
            [
                'nome' => 'Açúcar Refinado',
                'ncm' => '17019900',
                'cest' => null,
                'slug_suffix' => 'acucar-refinado',
                'qtd' => 1,
                'sku' => 'ACU001',
            ],
            [
                'nome' => 'Café Torrado Moído',
                'ncm' => '09011100',
                'cest' => null,
                'slug_suffix' => 'cafe-torrado-moido',
                'qtd' => 500,
                'sku' => 'CAF001',
            ],
        ];

        foreach ($produtos as $i => $p) {
            $ncmId = $ncmMap[$p['ncm']] ?? null;

            $base = ProdutoBase::create([
                'categoria_id' => $categoria->id,
                'nome' => $p['nome'],
                'slug' => Str::slug($p['nome']),
                'ativo' => true,
                'ncm_id' => $ncmId,
                'cfop_id' => $cfop->id,
                'cest_id' => null,
                'cst_icms' => null,
                'origem_mercadoria' => '0',
            ]);

            $var = ProdutoVariacao::create([
                'produto_base_id' => $base->id,
                'marca_id' => $marca->id,
                'unidade_medida_id' => $um->id,
                'embalagem_id' => $emb->id,
                'nome_completo' => $p['nome'],
                'slug' => $p['slug_suffix'],
                'sku' => $p['sku'],
                'conteudo_quantidade' => $p['qtd'],
                'pesavel' => false,
                'fracionado' => false,
                'quantidade_minima_venda' => 1,
                'passo_venda' => 1,
                'ativo' => true,
                'ncm_id' => $ncmId,
                'cfop_id' => $cfop->id,
                'cest_id' => null,
                'cst_icms' => null,
                'origem_mercadoria' => '0',
            ]);

            $precos = [22.90, 4.50, 7.90, 3.99, 12.90];
            $custos = [18.00, 3.20, 5.50, 2.80, 9.00];
            \DB::table('tabela_precos_itens')->insert([
                'tabela_preco_id' => 1,
                'produto_variacao_id' => $var->id,
                'preco_venda' => $precos[$i],
                'preco_custo' => $custos[$i],
                'margem_percentual' => round(($precos[$i] - $custos[$i]) / $precos[$i] * 100, 1),
            ]);

            $this->command->info("✓ {$p['nome']} criado (R$ {$precos[$i]})");
        }
    }

    private function criarCategorias(): Categoria
    {
        $n1 = Categoria::firstOrCreate(
            ['slug' => 'alimentos'],
            ['nome' => 'Alimentos', 'parent_id' => null, 'ordem' => 1, 'slug' => 'alimentos', 'ativo' => true]
        );
        $n2 = Categoria::firstOrCreate(
            ['slug' => 'alimentos-nao-pereciveis'],
            ['nome' => 'Não Perecíveis', 'parent_id' => $n1->id, 'ordem' => 1, 'slug' => 'alimentos-nao-pereciveis', 'ativo' => true]
        );
        return Categoria::firstOrCreate(
            ['slug' => 'graos-cereais'],
            ['nome' => 'Grãos e Cereais', 'parent_id' => $n2->id, 'ordem' => 1, 'slug' => 'graos-cereais', 'ativo' => true]
        );
    }
}

<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SugestaoCompraManager extends Component
{
    use WithPagination;

    public string $lojaFiltro = '';
    public string $categoriaFiltro = '';
    public string $diasBase = '30';
    public string $diasReposicao = '15';
    public string $filtroUrgencia = '';
    public string $fornecedorFiltro = '';
    public bool $selecionarTodos = false;
    public array $selecionados = [];

    public string $toastMsg = '';
    public bool $toastShow = false;

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function categorias(): array
    {
        return Categoria::where('nivel', 1)->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function fornecedores(): array
    {
        return \App\Models\Fornecedor::where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social'])->toArray();
    }

    public function updatedSelecionarTodos(): void
    {
        $sugestoes = $this->sugestoes();
        $this->selecionados = $this->selecionarTodos
            ? collect($sugestoes)->pluck('variacao_id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    public function gerarPedido(): void
    {
        if (!$this->lojaFiltro || !$this->fornecedorFiltro) {
            $this->toast('Selecione a loja e o fornecedor antes de gerar o pedido.');
            return;
        }

        if (empty($this->selecionados)) {
            $this->toast('Selecione pelo menos um item para gerar o pedido.');
            return;
        }

        $sugestoes = collect($this->sugestoes())->keyBy('variacao_id');
        $selecionadosIds = array_map('intval', $this->selecionados);

        $lojaId = (int)$this->lojaFiltro;
        $fornecedorId = (int)$this->fornecedorFiltro;
        $totalProdutos = 0;
        $itensNoPedido = 0;

        DB::transaction(function () use ($sugestoes, $selecionadosIds, $lojaId, $fornecedorId, &$totalProdutos, &$itensNoPedido) {
            $pedido = \App\Models\CompraPedido::create([
                'loja_id' => $lojaId,
                'fornecedor_id' => $fornecedorId,
                'usuario_id' => auth()->id(),
                'status' => 'rascunho',
                'total_produtos' => 0,
                'total_pedido' => 0,
            ]);

            foreach ($selecionadosIds as $variacaoId) {
                $s = $sugestoes->get($variacaoId);
                if (!$s) continue;

                $qtd = max(1, (float)$s['sugestao']);
                $custo = (float)$s['custo'];
                $totalItem = $qtd * $custo;
                $totalProdutos += $totalItem;
                $itensNoPedido++;

                \App\Models\CompraPedidoItem::create([
                    'compra_pedido_id' => $pedido->id,
                    'produto_variacao_id' => (int)$s['variacao_id'],
                    'quantidade_pedida' => $qtd,
                    'custo_unitario' => $custo,
                    'total_item' => $totalItem,
                ]);
            }

            $pedido->update([
                'total_produtos' => $totalProdutos,
                'total_pedido' => $totalProdutos,
            ]);

            $this->pedidoCriado = $pedido->id;
        });

        $this->toast("Pedido #{$this->pedidoCriado} gerado com {$itensNoPedido} item(ns)!");
        $this->selecionados = [];
        $this->selecionarTodos = false;
    }

    public ?int $pedidoCriado = null;

    public function sugestoes()
    {
        if (!$this->lojaFiltro) return [];

        $dias = max(1, (int)$this->diasBase);
        $leadTime = max(1, (int)$this->diasReposicao);
        $dataLimite = now()->subDays($dias);
        $lojaId = (int)$this->lojaFiltro;

        $vendas = DB::table('pdv_venda_itens')
            ->join('pdv_vendas', 'pdv_vendas.id', '=', 'pdv_venda_itens.venda_id')
            ->where('pdv_vendas.status', 'concluida')
            ->where('pdv_vendas.created_at', '>=', $dataLimite)
            ->where('pdv_vendas.loja_id', $lojaId)
            ->where('pdv_venda_itens.cancelado', false)
            ->select(
                'pdv_venda_itens.produto_variacao_id',
                DB::raw('SUM(pdv_venda_itens.quantidade) as qtd_total')
            )
            ->groupBy('pdv_venda_itens.produto_variacao_id')
            ->pluck('qtd_total', 'produto_variacao_id');

        if ($vendas->isEmpty()) return [];

        $variacaoIds = $vendas->keys();

        $estoques = DB::table('estoque_saldos')
            ->where('loja_id', $lojaId)
            ->whereIn('produto_variacao_id', $variacaoIds)
            ->select('produto_variacao_id', 'quantidade_atual', 'estoque_minimo', 'estoque_maximo')
            ->get()
            ->keyBy('produto_variacao_id');

        $produtos = DB::table('produto_variacoes')
            ->whereIn('produto_variacoes.id', $variacaoIds)
            ->where('produto_variacoes.ativo', true)
            ->join('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->join('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->leftJoin('tabela_precos_itens', function ($j) use ($lojaId) {
                $j->on('tabela_precos_itens.produto_variacao_id', '=', 'produto_variacoes.id')
                  ->whereExists(function ($sub) use ($lojaId) {
                      $sub->select(DB::raw(1))
                          ->from('lojas')
                          ->where('lojas.id', $lojaId)
                          ->whereColumn('lojas.tabela_preco_id', 'tabela_precos_itens.tabela_preco_id');
                  });
            })
            ->select(
                'produto_variacoes.id',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'categorias.id as categoria_id',
                'categorias.nome as categoria',
                'tabela_precos_itens.preco_custo'
            )
            ->get()
            ->keyBy('id');

        $resultado = [];
        foreach ($vendas as $variacaoId => $qtdTotal) {
            $prod = $produtos->get($variacaoId);
            if (!$prod) continue;

            $est = $estoques->get($variacaoId);
            $estoqueAtual = (float)($est->quantidade_atual ?? 0) - (float)($est->quantidade_reservada ?? 0);
            $estoqueMin = (float)($est->estoque_minimo ?? 0);
            $vendaMediaDiaria = $qtdTotal / $dias;

            if ($vendaMediaDiaria <= 0) continue;

            $diasAteZero = $vendaMediaDiaria > 0 ? floor($estoqueAtual / $vendaMediaDiaria) : 999;
            $sugestao = max(0, ceil(($vendaMediaDiaria * ($leadTime + 7)) - $estoqueAtual + $estoqueMin));

            $resultado[] = [
                'variacao_id' => $variacaoId,
                'nome' => $prod->nome_completo,
                'sku' => $prod->sku,
                'categoria_id' => $prod->categoria_id,
                'categoria' => $prod->categoria,
                'custo' => (float)($prod->preco_custo ?? 0),
                'venda_media' => round($vendaMediaDiaria, 3),
                'estoque_atual' => $estoqueAtual,
                'estoque_min' => $estoqueMin,
                'dias_ate_zero' => $diasAteZero,
                'sugestao' => $sugestao,
                'urgencia' => $diasAteZero <= $leadTime ? 'critica' : ($diasAteZero <= $leadTime + 7 ? 'media' : 'ok'),
            ];
        }

        if ($this->categoriaFiltro) {
            $resultado = array_filter($resultado, fn($r) => (int)$r['categoria_id'] === (int)$this->categoriaFiltro);
        }
        if ($this->filtroUrgencia === 'critica') {
            $resultado = array_filter($resultado, fn($r) => $r['urgencia'] === 'critica');
        } elseif ($this->filtroUrgencia === 'media') {
            $resultado = array_filter($resultado, fn($r) => $r['urgencia'] === 'media');
        }

        usort($resultado, fn($a, $b) => $a['dias_ate_zero'] <=> $b['dias_ate_zero']);
        return $resultado;
    }

    #[Computed]
    public function resumo(): array
    {
        $itens = $this->sugestoes();
        $criticos = collect($itens)->where('urgencia', 'critica');
        $media = collect($itens)->where('urgencia', 'media');
        return [
            'total' => count($itens),
            'criticos' => $criticos->count(),
            'criticos_valor' => $criticos->sum(fn($i) => $i['custo'] * $i['sugestao']),
            'media' => $media->count(),
            'sugestao_total' => collect($itens)->sum('sugestao'),
            'custo_total' => collect($itens)->sum(fn($i) => $i['custo'] * $i['sugestao']),
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.sugestao-compra-manager')
            ->layout('components.layouts.app', ['title' => 'Sugestão de Compras · ERP Mercado']);
    }
}

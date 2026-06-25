<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Loja;
use App\Models\CompraPedidoItem;
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
    public array $itensPedido = [];

    public ?int $pedidoCriado = null;
    public string $toastMsg = '';
    public bool $toastShow = false;

    public bool $historicoModalOpen = false;
    public array $historicoDados = [];
    public string $historicoProdutoNome = '';

    public function verHistorico(int $variacaoId, string $nome): void
    {
        $this->historicoProdutoNome = $nome;
        $ultimos = CompraPedidoItem::with('pedido')
            ->where('produto_variacao_id', $variacaoId)
            ->whereHas('pedido', fn($q) => $q->where('status', '!=', 'cancelado'))
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $this->historicoDados = $ultimos->map(fn($i) => [
            'pedido_id' => $i->compra_pedido_id,
            'data' => $i->created_at?->format('d/m/Y') ?? '—',
            'quantidade' => (float)$i->quantidade_pedida,
            'preco' => (float)$i->custo_unitario,
        ])->toArray();

        // Also add from precos_historico
        $histPreco = DB::table('precos_historico')
            ->where('produto_variacao_id', $variacaoId)
            ->where('loja_id', (int)$this->lojaFiltro)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        foreach ($histPreco as $h) {
            $this->historicoDados[] = [
                'pedido_id' => null,
                'data' => $h->created_at ? date('d/m/Y', strtotime($h->created_at)) : '—',
                'quantidade' => 0,
                'preco' => (float)$h->preco_novo,
            ];
        }

        usort($this->historicoDados, fn($a, $b) => strtotime(str_replace('/', '-', $b['data'])) - strtotime(str_replace('/', '-', $a['data'])));
        $this->historicoDados = array_slice($this->historicoDados, 0, 10);
        $this->historicoModalOpen = true;
    }

    public function mount(): void
    {
        $this->lojaFiltro = (string)(auth()->user()->loja_id ?? '');
    }

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

    public function updatedLojaFiltro(): void { $this->itensPedido = []; $this->selecionados = []; }
    public function updatedDiasBase(): void { $this->itensPedido = []; }
    public function updatedDiasReposicao(): void { $this->itensPedido = []; }

    public function updatedSelecionarTodos(): void
    {
        $sugestoes = $this->sugestoes();
        $this->selecionados = $this->selecionarTodos
            ? collect($sugestoes)->pluck('variacao_id')->map(fn($id) => (string)$id)->toArray()
            : [];
    }

    private function syncItensPedido(): void
    {
        $sugestoes = $this->sugestoes();
        foreach ($sugestoes as $s) {
            $vid = $s['variacao_id'];
            if (!isset($this->itensPedido[$vid])) {
                $this->itensPedido[$vid] = [
                    'quantidade' => max(1, (float)$s['sugestao']),
                    'preco' => (float)$s['custo'],
                ];
            }
        }
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

        $this->syncItensPedido();

        $lojaId = (int)$this->lojaFiltro;
        $fornecedorId = (int)$this->fornecedorFiltro;
        $totalProdutos = 0;
        $itensNoPedido = 0;

        DB::transaction(function () use ($lojaId, $fornecedorId, &$totalProdutos, &$itensNoPedido) {
            $pedido = \App\Models\CompraPedido::create([
                'loja_id' => $lojaId,
                'fornecedor_id' => $fornecedorId,
                'usuario_id' => auth()->id(),
                'status' => 'rascunho',
                'total_produtos' => 0,
                'total_pedido' => 0,
            ]);

            foreach ($this->selecionados as $variacaoIdStr) {
                $variacaoId = (int)$variacaoIdStr;
                $item = $this->itensPedido[$variacaoId] ?? null;
                if (!$item) continue;

                $qtd = max(0.001, (float)$item['quantidade']);
                $preco = max(0, (float)$item['preco']);
                $totalItem = $qtd * $preco;
                $totalProdutos += $totalItem;
                $itensNoPedido++;

                \App\Models\CompraPedidoItem::create([
                    'compra_pedido_id' => $pedido->id,
                    'produto_variacao_id' => $variacaoId,
                    'quantidade_pedida' => $qtd,
                    'custo_unitario' => $preco,
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
        $this->itensPedido = [];
    }

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
            ->select('pdv_venda_itens.produto_variacao_id', DB::raw('SUM(pdv_venda_itens.quantidade) as qtd_total'))
            ->groupBy('pdv_venda_itens.produto_variacao_id')
            ->pluck('qtd_total', 'produto_variacao_id');

        if ($vendas->isEmpty()) return [];

        $variacaoIds = $vendas->keys();

        $estoques = DB::table('estoque_saldos')
            ->where('loja_id', $lojaId)->whereIn('produto_variacao_id', $variacaoIds)
            ->select('produto_variacao_id', 'quantidade_atual', 'estoque_minimo')
            ->get()->keyBy('produto_variacao_id');

        $produtos = DB::table('produto_variacoes')
            ->whereIn('produto_variacoes.id', $variacaoIds)->where('produto_variacoes.ativo', true)
            ->join('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->join('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->leftJoin('tabela_precos_itens', function ($j) use ($lojaId) {
                $j->on('tabela_precos_itens.produto_variacao_id', '=', 'produto_variacoes.id')
                  ->whereExists(function ($sub) use ($lojaId) {
                      $sub->select(DB::raw(1))->from('lojas')
                          ->where('lojas.id', $lojaId)
                          ->whereColumn('lojas.tabela_preco_id', 'tabela_precos_itens.tabela_preco_id');
                  });
            })
            ->select(
                'produto_variacoes.id', 'produto_variacoes.nome_completo', 'produto_variacoes.sku',
                'categorias.id as categoria_id', 'categorias.nome as categoria',
                'tabela_precos_itens.preco_custo'
            )->get()->keyBy('id');

        $resultado = [];
        $ultimosPrecos = [];
        if ($this->lojaFiltro) {
            $ultimosPrecos = CompraPedidoItem::select('produto_variacao_id', 'custo_unitario', 'compra_pedido_id')
                ->whereIn('produto_variacao_id', $variacaoIds)
                ->whereHas('pedido', fn($q) => $q->where('status', '!=', 'cancelado'))
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('produto_variacao_id')
                ->map(fn($g) => ['preco' => (float)$g->first()->custo_unitario, 'pedido_id' => $g->first()->compra_pedido_id])
                ->toArray();
        }

        foreach ($vendas as $variacaoId => $qtdTotal) {
            $prod = $produtos->get($variacaoId);
            if (!$prod) continue;

            $est = $estoques->get($variacaoId);
            $estoqueAtual = (float)($est->quantidade_atual ?? 0) - (float)(\App\Models\EstoqueSaldo::where('produto_variacao_id', $variacaoId)->where('loja_id', $lojaId)->value('quantidade_reservada') ?? 0);
            $estoqueMin = (float)($est->estoque_minimo ?? 0);
            $vendaMediaDiaria = $qtdTotal / $dias;
            if ($vendaMediaDiaria <= 0) continue;

            $diasAteZero = $vendaMediaDiaria > 0 ? floor($estoqueAtual / $vendaMediaDiaria) : 999;
            $sugestao = max(0, ceil(($vendaMediaDiaria * ($leadTime + 7)) - $estoqueAtual + $estoqueMin));

            $ultimo = $ultimosPrecos[$variacaoId] ?? null;
            $ultimoPreco = $ultimo['preco'] ?? 0;
            $pedidoUltimo = $ultimo['pedido_id'] ?? null;
            $custoAtual = (float)($prod->preco_custo ?? 0);
            $variacaoPct = $ultimoPreco > 0 && $custoAtual > 0
                ? round(($custoAtual - $ultimoPreco) / $ultimoPreco * 100, 1)
                : 0;

            $resultado[] = [
                'variacao_id' => $variacaoId, 'nome' => $prod->nome_completo, 'sku' => $prod->sku,
                'categoria_id' => $prod->categoria_id, 'categoria' => $prod->categoria,
                'custo' => $custoAtual,
                'ultimo_preco' => $ultimoPreco,
                'ultimo_pedido' => $pedidoUltimo,
                'variacao_pct' => $variacaoPct,
                'venda_media' => round($vendaMediaDiaria, 3),
                'estoque_atual' => $estoqueAtual, 'estoque_min' => $estoqueMin,
                'dias_ate_zero' => $diasAteZero, 'sugestao' => $sugestao,
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
        $subiram = collect($itens)->filter(fn($i) => $i['variacao_pct'] > 0);
        $cairam = collect($itens)->filter(fn($i) => $i['variacao_pct'] < 0);
        return [
            'total' => count($itens),
            'criticos' => $criticos->count(),
            'media' => $media->count(),
            'subiram' => $subiram->count(),
            'cairam' => $cairam->count(),
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
            ->layout('components.layouts.app', ['title' => 'Compras · ERP Mercado']);
    }
}

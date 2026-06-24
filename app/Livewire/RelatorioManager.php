<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;

class RelatorioManager extends Component
{
    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $dataInicio = '';
    public string $dataFim = '';

    public function mount(): void
    {
        $this->dataInicio = now()->subMonth()->format('Y-m-d');
        $this->dataFim = now()->format('Y-m-d');
    }

    // ═══════════════════════════════════════
    //  VENDAS
    // ═══════════════════════════════════════

    #[Computed]
    public function vendasResumo(): array
    {
        $q = \App\Models\PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim));
        return [
            'total' => $q->sum('total'),
            'quantidade' => $q->count(),
            'media' => $q->avg('total'),
        ];
    }

    #[Computed]
    public function vendasPorDia(): array
    {
        return \App\Models\PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->selectRaw('DATE(created_at) as dia, COUNT(*) as qtd, SUM(total) as total')
            ->groupBy('dia')->orderBy('dia')->get()->toArray();
    }

    #[Computed]
    public function vendasPorPagamento(): array
    {
        $ids = \App\Models\PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->pluck('id');
        return \App\Models\PdvVendaPagamento::whereIn('venda_id', $ids)
            ->selectRaw('forma_pagamento_id, SUM(valor) as total')
            ->groupBy('forma_pagamento_id')->get()->toArray();
    }

    #[Computed]
    public function vendasTopProdutos(): array
    {
        $ids = \App\Models\PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->pluck('id');
        return \App\Models\PdvVendaItem::whereIn('venda_id', $ids)
            ->selectRaw('produto_variacao_id, SUM(quantidade) as qtd, SUM(total_item) as total')
            ->groupBy('produto_variacao_id')->orderBy('total', 'desc')->limit(20)->get()->toArray();
    }

    // ═══════════════════════════════════════
    //  COMPRAS
    // ═══════════════════════════════════════

    #[Computed]
    public function comprasResumo(): array
    {
        $q = \App\Models\CompraPedido::whereIn('status', ['enviado', 'parcialmente_recebido', 'recebido'])
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim));
        return [
            'total' => $q->sum('total_pedido'),
            'quantidade' => $q->count(),
        ];
    }

    #[Computed]
    public function comprasPorFornecedor(): array
    {
        return \App\Models\CompraPedido::whereIn('status', ['enviado', 'parcialmente_recebido', 'recebido'])
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->selectRaw('fornecedor_id, COUNT(*) as qtd, SUM(total_pedido) as total')
            ->groupBy('fornecedor_id')->orderBy('total', 'desc')->limit(20)->get()->toArray();
    }

    #[Computed]
    public function comprasPorStatus(): array
    {
        return \App\Models\CompraPedido::when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->selectRaw('status, COUNT(*) as qtd, SUM(total_pedido) as total')
            ->groupBy('status')->get()->toArray();
    }

    // ═══════════════════════════════════════
    //  FINANCEIRO
    // ═══════════════════════════════════════

    #[Computed]
    public function financeiroResumo(): array
    {
        $q = \App\Models\FinanceiroLancamento::query()
            ->when($this->dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $this->dataFim));
        $resultados = $q->selectRaw("
            COALESCE(SUM(CASE WHEN tipo='receita' AND status='pago' THEN valor ELSE 0 END), 0) as receitas,
            COALESCE(SUM(CASE WHEN tipo='despesa' AND status='pago' THEN valor ELSE 0 END), 0) as despesas,
            COALESCE(SUM(CASE WHEN tipo='receita' AND status IN ('pendente','atrasado') THEN valor ELSE 0 END), 0) as a_receber,
            COALESCE(SUM(CASE WHEN tipo='despesa' AND status IN ('pendente','atrasado') THEN valor ELSE 0 END), 0) as a_pagar
        ")->first();
        return [
            'receitas' => (float)($resultados->receitas ?? 0),
            'despesas' => (float)($resultados->despesas ?? 0),
            'a_receber' => (float)($resultados->a_receber ?? 0),
            'a_pagar' => (float)($resultados->a_pagar ?? 0),
        ];
    }

    #[Computed]
    public function financeiroPorCategoria(): array
    {
        return \App\Models\FinanceiroLancamento::query()
            ->when($this->dataInicio, fn($q) => $q->whereDate('data_vencimento', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('data_vencimento', '<=', $this->dataFim))
            ->where('status', 'pago')
            ->selectRaw('categoria_id, tipo, SUM(valor) as total')
            ->groupBy('categoria_id', 'tipo')->orderBy('total', 'desc')->limit(30)->get()->toArray();
    }

    #[Computed]
    public function financeiroAging(): array
    {
        $hoje = now();
        $linhas = \App\Models\FinanceiroLancamento::whereIn('status', ['pendente', 'atrasado'])
            ->selectRaw("
                tipo,
                COALESCE(SUM(CASE WHEN data_vencimento >= ? AND data_vencimento <= ? THEN valor ELSE 0 END), 0) as ate_30,
                COALESCE(SUM(CASE WHEN data_vencimento > ? AND data_vencimento <= ? THEN valor ELSE 0 END), 0) as ate_60,
                COALESCE(SUM(CASE WHEN data_vencimento > ? THEN valor ELSE 0 END), 0) as mais_60,
                COALESCE(SUM(CASE WHEN data_vencimento < ? THEN valor ELSE 0 END), 0) as vencidos
            ", [
                $hoje, $hoje->copy()->addDays(30),
                $hoje->copy()->addDays(30), $hoje->copy()->addDays(60),
                $hoje->copy()->addDays(60),
                $hoje,
            ])
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo');

        $rec = $linhas->get('receita');
        $pag = $linhas->get('despesa');
        return [
            'receber' => [
                'ate_30' => (float)($rec->ate_30 ?? 0),
                '31_60' => (float)($rec->ate_60 ?? 0),
                '60_mais' => (float)($rec->mais_60 ?? 0),
                'vencidos' => (float)($rec->vencidos ?? 0),
            ],
            'pagar' => [
                'ate_30' => (float)($pag->ate_30 ?? 0),
                '31_60' => (float)($pag->ate_60 ?? 0),
                '60_mais' => (float)($pag->mais_60 ?? 0),
                'vencidos' => (float)($pag->vencidos ?? 0),
            ],
        ];
    }

    // ═══════════════════════════════════════
    //  ESTOQUE
    // ═══════════════════════════════════════

    #[Computed]
    public function estoqueResumo(): array
    {
        return [
            'total_produtos' => \App\Models\ProdutoVariacao::count(),
            'com_estoque' => \App\Models\EstoqueSaldo::where('quantidade_atual', '>', 0)->count(),
            'baixo' => \App\Models\EstoqueSaldo::where('quantidade_atual', '>', 0)->where('quantidade_atual', '<=', 5)->count(),
            'zerados' => \App\Models\EstoqueSaldo::where('quantidade_atual', '=', 0)->count(),
        ];
    }

    #[Computed]
    public function estoqueLotesVencendo(): array
    {
        return \App\Models\EstoqueLote::where('quantidade_atual', '>', 0)
            ->where('data_validade', '<=', now()->addDays(30))
            ->where('data_validade', '>=', now())
            ->orderBy('data_validade')->limit(20)->get()->toArray();
    }

    #[Computed]
    public function estoqueLotesVencidos(): array
    {
        return \App\Models\EstoqueLote::where('quantidade_atual', '>', 0)
            ->where('data_validade', '<', now())
            ->orderBy('data_validade')->limit(20)->get()->toArray();
    }

    // ═══════════════════════════════════════
    //  CLIENTES
    // ═══════════════════════════════════════

    #[Computed]
    public function clientesResumo(): array
    {
        return [
            'total' => \App\Models\Cliente::count(),
            'novos' => \App\Models\Cliente::when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
                ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))->count(),
            'ativos' => \App\Models\Cliente::where('ativo', true)->count(),
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.relatorio-manager')
            ->layout('components.layouts.app', ['title' => 'Relatórios · ERP Mercado']);
    }
}

<?php

namespace App\Livewire\Admin;

use App\Models\PdvVenda;
use App\Models\PdvVendaItem;
use App\Models\PdvVendaPagamento;
use App\Models\FormaPagamento;
use App\Models\FinanceiroLancamento;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueLote;
use App\Models\CompraPedido;
use App\Models\ProdutoVariacao;
use App\Models\User;
use App\Models\Loja;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class GerencialManager extends Component
{
    public string $dataInicio = '';
    public string $dataFim = '';
    public string $lojaFiltro = '';

    public function mount(): void
    {
        $this->dataInicio = now()->startOfMonth()->format('Y-m-d');
        $this->dataFim = now()->format('Y-m-d');
    }

    // ─── VENDAS ───

    #[Computed]
    public function vendasResumo(): array
    {
        $q = PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro));
        $total = $q->sum('total');
        $qtd = $q->count();
        return [
            'total' => $total,
            'qtd' => $qtd,
            'media' => $qtd > 0 ? $total / $qtd : 0,
        ];
    }

    #[Computed]
    public function vendasHoje(): array
    {
        $q = PdvVenda::where('status', 'concluida')->whereDate('created_at', today())
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro));
        return [
            'total' => $q->sum('total'),
            'qtd' => $q->count(),
        ];
    }

    #[Computed]
    public function vendasPorOperador(): array
    {
        return PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->selectRaw('usuario_id, COUNT(*) as qtd, SUM(total) as total, AVG(total) as ticket_medio')
            ->groupBy('usuario_id')->orderBy('total', 'desc')
            ->get()->toArray();
    }

    #[Computed]
    public function vendas7dias(): array
    {
        $inicio = now()->subDays(6)->startOfDay();
        $fim = now()->endOfDay();
        $vendas = PdvVenda::where('status', 'concluida')
            ->whereBetween('created_at', [$inicio, $fim])
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->selectRaw('DATE(created_at) as dia, SUM(total) as total, COUNT(*) as qtd')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');

        $dados = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = now()->subDays($i)->startOfDay();
            $chave = $dia->format('Y-m-d');
            $v = $vendas->get($chave);
            $dados[] = ['data' => $dia->format('d/m'), 'total' => (float)($v->total ?? 0), 'qtd' => (int)($v->qtd ?? 0)];
        }
        return $dados;
    }

    #[Computed]
    public function vendasPorPagamento(): array
    {
        $ids = PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->pluck('id');
        return PdvVendaPagamento::whereIn('venda_id', $ids)
            ->selectRaw('forma_pagamento_id, SUM(valor) as total, COUNT(*) as qtd')
            ->groupBy('forma_pagamento_id')->orderBy('total', 'desc')
            ->get()->toArray();
    }

    #[Computed]
    public function vendasPorHora(): array
    {
        $rows = PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->selectRaw('HOUR(created_at) as hora, COUNT(*) as qtd, SUM(total) as total')
            ->groupBy('hora')->orderBy('hora')->get();
        $horas = [];
        for ($h = 6; $h <= 22; $h++) {
            $horas[$h - 6] = ['hora' => $h . 'h', 'qtd' => 0, 'total' => 0];
        }
        foreach ($rows as $r) {
            if ($r->hora >= 6 && $r->hora <= 22) {
                $horas[$r->hora - 6]['qtd'] = (int)$r->qtd;
                $horas[$r->hora - 6]['total'] = (float)$r->total;
            }
        }
        return $horas;
    }

    // ─── PRODUTOS ───

    #[Computed]
    public function topProdutos(): array
    {
        $ids = PdvVenda::where('status', 'concluida')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->pluck('id');
        return PdvVendaItem::whereIn('venda_id', $ids)
            ->selectRaw('produto_variacao_id, SUM(quantidade) as qtd, SUM(total_item) as total')
            ->groupBy('produto_variacao_id')->orderBy('total', 'desc')
            ->limit(10)->get()->toArray();
    }

    // ─── FILTROS ───

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function totaisCancelados(): array
    {
        $q = PdvVenda::where('status', 'cancelada')
            ->when($this->dataInicio, fn($q) => $q->whereDate('created_at', '>=', $this->dataInicio))
            ->when($this->dataFim, fn($q) => $q->whereDate('created_at', '<=', $this->dataFim))
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro));
        return ['qtd' => $q->count(), 'total' => $q->sum('total')];
    }

    public function render()
    {
        return view('livewire.admin.gerencial-manager')
            ->layout('components.layouts.app', ['title' => 'Dashboard Gerencial · ERP Mercado']);
    }
}

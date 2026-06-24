<?php

namespace App\Livewire;

use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class DashboardExecutivoManager extends Component
{
    public string $lojaFiltro = '';
    public string $toastMsg = '';
    public bool $toastShow = false;

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function dados(): array
    {
        $inicioHoje = now()->startOfDay();
        $inicioMes = now()->startOfMonth();

        $lojaWhere = fn($q) => $this->lojaFiltro ? $q->where('loja_id', (int)$this->lojaFiltro) : $q;

        // Faturamento Hoje
        $fatHoje = $lojaWhere(DB::table('pdv_vendas')->where('status', 'concluida')->where('created_at', '>=', $inicioHoje))->sum('total');

        // Faturamento Mês
        $fatMes = $lojaWhere(DB::table('pdv_vendas')->where('status', 'concluida')->where('created_at', '>=', $inicioMes))->sum('total');

        // Vendas Hoje (quantidade)
        $vendasHoje = $lojaWhere(DB::table('pdv_vendas')->where('status', 'concluida')->where('created_at', '>=', $inicioHoje))->count();

        // Ticket Médio
        $ticketMedio = $vendasHoje > 0 ? $fatHoje / $vendasHoje : 0;

        // Top 10 produtos mais vendidos (mês)
        $topProdutos = DB::table('pdv_venda_itens')
            ->join('pdv_vendas', 'pdv_vendas.id', '=', 'pdv_venda_itens.venda_id')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'pdv_venda_itens.produto_variacao_id')
            ->where('pdv_vendas.status', 'concluida')
            ->where('pdv_vendas.created_at', '>=', $inicioMes)
            ->where('pdv_venda_itens.cancelado', false)
            ->when($this->lojaFiltro, fn($q) => $q->where('pdv_vendas.loja_id', (int)$this->lojaFiltro))
            ->select(
                'produto_variacoes.nome_completo',
                DB::raw('SUM(pdv_venda_itens.quantidade) as qtd'),
                DB::raw('SUM(pdv_venda_itens.total_item) as total')
            )
            ->groupBy('produto_variacoes.id', 'produto_variacoes.nome_completo')
            ->orderByDesc('qtd')
            ->limit(10)
            ->get()
            ->toArray();

        // Estoque crítico (itens com estoque baixo)
        $estoqueCritico = DB::table('estoque_saldos')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_saldos.produto_variacao_id')
            ->where('estoque_saldos.quantidade_atual', '>', 0)
            ->whereColumn('estoque_saldos.quantidade_atual', '<=', 'estoque_saldos.estoque_minimo')
            ->when($this->lojaFiltro, fn($q) => $q->where('estoque_saldos.loja_id', (int)$this->lojaFiltro))
            ->select('produto_variacoes.nome_completo', 'estoque_saldos.quantidade_atual', 'estoque_saldos.estoque_minimo')
            ->orderBy('estoque_saldos.quantidade_atual')
            ->limit(10)
            ->get()
            ->toArray();

        // Contas atrasadas (inadimplência)
        $inadimplentes = DB::table('financeiro_lancamentos')
            ->where('tipo', 'receita')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->where('data_vencimento', '<', now())
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->select('descricao', 'valor', 'data_vencimento')
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get()
            ->toArray();

        // Vendas dos últimos 7 dias
        $vendas7dias = DB::table('pdv_vendas')
            ->where('status', 'concluida')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->selectRaw('DATE(created_at) as dia, SUM(total) as total, COUNT(*) as qtd')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('dia')
            ->get()
            ->keyBy('dia');

        $grafico7dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = now()->subDays($i)->startOfDay();
            $chave = $dia->format('Y-m-d');
            $v = $vendas7dias->get($chave);
            $grafico7dias[] = [
                'dia' => $dia->format('d/m'),
                'total' => (float)($v->total ?? 0),
                'qtd' => (int)($v->qtd ?? 0),
            ];
        }

        return [
            'fat_hoje' => $fatHoje,
            'fat_mes' => $fatMes,
            'vendas_hoje' => $vendasHoje,
            'ticket_medio' => $ticketMedio,
            'top_produtos' => $topProdutos,
            'estoque_critico' => $estoqueCritico,
            'inadimplentes' => $inadimplentes,
            'grafico_7dias' => $grafico7dias,
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.dashboard-executivo-manager')
            ->layout('components.layouts.app', ['title' => 'Dashboard Executivo · ERP Mercado']);
    }
}

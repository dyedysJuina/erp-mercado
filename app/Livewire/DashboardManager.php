<?php

namespace App\Livewire;

use App\Models\PdvVenda;
use App\Models\PdvVendaItem;
use App\Models\FinanceiroLancamento;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueLote;
use App\Models\CompraPedido;
use App\Models\ProdutoVariacao;
use App\Models\TabelaPrecoItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class DashboardManager extends Component
{
    public string $toastMsg = '';
    public bool $toastShow = false;

    #[Computed]
    public function vendasHoje(): array
    {
        $hoje = now()->startOfDay();
        $total = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $hoje)->sum('total');
        $qtd = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $hoje)->count();
        return ['total' => $total, 'qtd' => $qtd];
    }

    #[Computed]
    public function vendasMes(): array
    {
        $inicio = now()->startOfMonth();
        $total = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $inicio)->sum('total');
        $qtd = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $inicio)->count();
        return ['total' => $total, 'qtd' => $qtd];
    }

    #[Computed]
    public function ticketMedio(): float
    {
        $vendas = PdvVenda::where('status', 'concluida')->where('created_at', '>=', now()->startOfDay());
        $total = $vendas->sum('total');
        $qtd = $vendas->count();
        return $qtd > 0 ? $total / $qtd : 0;
    }

    #[Computed]
    public function margemBruta(): float
    {
        return (float) TabelaPrecoItem::whereNotNull('margem_percentual')->where('margem_percentual', '>', 0)->avg('margem_percentual');
    }

    #[Computed]
    public function estoqueBaixo(): array
    {
        return EstoqueSaldo::whereColumn('quantidade_atual', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)
            ->with('variacao')
            ->orderBy('quantidade_atual')->limit(10)->get()->toArray();
    }

    #[Computed]
    public function estoqueCritico(): int
    {
        return EstoqueSaldo::whereColumn('quantidade_atual', '<=', 'estoque_minimo')
            ->where('estoque_minimo', '>', 0)->count();
    }

    #[Computed]
    public function contasPendentesTotal(): float
    {
        return FinanceiroLancamento::where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function aReceber(): float
    {
        return FinanceiroLancamento::where('tipo', 'receita')->where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function aPagar(): float
    {
        return FinanceiroLancamento::where('tipo', 'despesa')->where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function resultadoLiquido(): float
    {
        $rec = FinanceiroLancamento::where('tipo', 'receita')->where('status', 'pago')->whereMonth('created_at', now()->month)->sum('valor');
        $des = FinanceiroLancamento::where('tipo', 'despesa')->where('status', 'pago')->whereMonth('created_at', now()->month)->sum('valor');
        return $rec - $des;
    }

    #[Computed]
    public function vendasRecentes(): array
    {
        return PdvVenda::with('pagamentos')->where('status', 'concluida')->latest('created_at')->limit(5)->get()->toArray();
    }

    #[Computed]
    public function lotesVencendo(): array
    {
        return EstoqueLote::whereBetween('data_validade', [now(), now()->addDays(7)])
            ->with('variacao')
            ->orderBy('data_validade')->limit(8)->get()->toArray();
    }

    #[Computed]
    public function vendas7dias(): array
    {
        $inicio = now()->subDays(6)->startOfDay();
        $fim = now()->endOfDay();
        $vendas = PdvVenda::where('status', 'concluida')
            ->whereBetween('created_at', [$inicio, $fim])
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

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.dashboard-manager')
            ->layout('components.layouts.app', ['title' => 'Dashboard · ERP Mercado']);
    }
}

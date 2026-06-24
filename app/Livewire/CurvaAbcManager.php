<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CurvaAbcManager extends Component
{
    use WithPagination;

    public string $lojaFiltro = '';
    public string $categoriaFiltro = '';
    public string $periodo = '30';
    public string $classeFiltro = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    private ?array $cacheAnalise = null;

    public function updatedLojaFiltro(): void { $this->cacheAnalise = null; $this->resetPage(); }
    public function updatedCategoriaFiltro(): void { $this->cacheAnalise = null; $this->resetPage(); }
    public function updatedPeriodo(): void { $this->cacheAnalise = null; $this->resetPage(); }
    public function updatedClasseFiltro(): void { $this->cacheAnalise = null; $this->resetPage(); }

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

    public function analise()
    {
        if ($this->cacheAnalise !== null) {
            return $this->cacheAnalise;
        }

        $dias = max(1, (int)$this->periodo);
        $dataLimite = now()->subDays($dias);

        $q = DB::table('pdv_venda_itens')
            ->join('pdv_vendas', 'pdv_vendas.id', '=', 'pdv_venda_itens.venda_id')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'pdv_venda_itens.produto_variacao_id')
            ->join('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->join('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->where('pdv_vendas.status', 'concluida')
            ->where('pdv_vendas.created_at', '>=', $dataLimite)
            ->where('pdv_venda_itens.cancelado', false)
            ->select(
                'produto_variacoes.id as variacao_id',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'categorias.nome as categoria',
                DB::raw('SUM(pdv_venda_itens.total_item) as valor_total'),
                DB::raw('SUM(pdv_venda_itens.quantidade) as qtd_total')
            );

        if ($this->lojaFiltro) {
            $q->where('pdv_vendas.loja_id', (int)$this->lojaFiltro);
        }
        if ($this->categoriaFiltro) {
            $q->where('categorias.id', (int)$this->categoriaFiltro);
        }

        $itens = $q->groupBy(
                'produto_variacoes.id',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'categorias.nome'
            )
            ->orderByDesc('valor_total')
            ->get();

        $totalGeral = $itens->sum('valor_total');
        $acumulado = 0;
        $resultado = [];

        foreach ($itens as $item) {
            $acumulado += $item->valor_total;
            $pct = $totalGeral > 0 ? ($item->valor_total / $totalGeral) * 100 : 0;
            $pctAcum = $totalGeral > 0 ? ($acumulado / $totalGeral) * 100 : 0;

            if ($pctAcum <= 80) $classe = 'A';
            elseif ($pctAcum <= 95) $classe = 'B';
            else $classe = 'C';

            $resultado[] = [
                'variacao_id' => $item->variacao_id,
                'nome' => $item->nome_completo,
                'sku' => $item->sku,
                'categoria' => $item->categoria,
                'valor' => (float)$item->valor_total,
                'qtd' => (float)$item->qtd_total,
                'pct' => round($pct, 1),
                'pct_acum' => round($pctAcum, 1),
                'classe' => $classe,
            ];
        }

        if ($this->classeFiltro) {
            $resultado = array_filter($resultado, fn($r) => $r['classe'] === $this->classeFiltro);
        }

        $this->cacheAnalise = $resultado;
        return $resultado;
    }

    #[Computed]
    public function resumo(): array
    {
        $itens = $this->analise();
        $total = collect($itens)->sum('valor');
        $a = collect($itens)->where('classe', 'A');
        $b = collect($itens)->where('classe', 'B');
        $c = collect($itens)->where('classe', 'C');
        return [
            'total' => $total,
            'a_count' => $a->count(), 'a_valor' => $a->sum('valor'),
            'b_count' => $b->count(), 'b_valor' => $b->sum('valor'),
            'c_count' => $c->count(), 'c_valor' => $c->sum('valor'),
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.curva-abc-manager')
            ->layout('components.layouts.app', ['title' => 'Curva ABC · ERP Mercado']);
    }
}

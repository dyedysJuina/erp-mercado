<?php

namespace App\Livewire;

use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class MargemDepartamentoManager extends Component
{
    public string $lojaFiltro = '';
    public string $periodo = '30';

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
        $dias = max(1, (int)$this->periodo);
        $dataLimite = now()->subDays($dias);

        $q = DB::table('pdv_venda_itens')
            ->join('pdv_vendas', 'pdv_vendas.id', '=', 'pdv_venda_itens.venda_id')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'pdv_venda_itens.produto_variacao_id')
            ->join('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->join('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->leftJoin('tabela_precos_itens', function ($j) {
                $j->on('tabela_precos_itens.produto_variacao_id', '=', 'pdv_venda_itens.produto_variacao_id')
                  ->whereExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('lojas')
                          ->whereColumn('lojas.id', 'pdv_vendas.loja_id')
                          ->whereColumn('lojas.tabela_preco_id', 'tabela_precos_itens.tabela_preco_id');
                  });
            })
            ->where('pdv_vendas.status', 'concluida')
            ->where('pdv_vendas.created_at', '>=', $dataLimite)
            ->where('pdv_venda_itens.cancelado', false)
            ->select(
                'categorias.id as cat_id',
                'categorias.nome as categoria',
                DB::raw('SUM(pdv_venda_itens.total_item) as receita'),
                DB::raw('SUM(pdv_venda_itens.quantidade * COALESCE(tabela_precos_itens.preco_custo, 0)) as custo'),
                DB::raw('COUNT(DISTINCT pdv_vendas.id) as vendas'),
                DB::raw('SUM(pdv_venda_itens.quantidade) as qtd')
            );

        if ($this->lojaFiltro) {
            $q->where('pdv_vendas.loja_id', (int)$this->lojaFiltro);
        }

        $resultados = $q->groupBy('categorias.id', 'categorias.nome')
            ->orderByDesc('receita')
            ->get();

        $totalReceita = $resultados->sum('receita');
        $totalCusto = $resultados->sum('custo');

        $departamentos = $resultados->map(function ($r) use ($totalReceita) {
            $receita = (float)$r->receita;
            $custo = (float)$r->custo;
            $lucro = $receita - $custo;
            return [
                'cat_id' => $r->cat_id,
                'categoria' => $r->categoria,
                'receita' => $receita,
                'custo' => $custo,
                'lucro' => $lucro,
                'margem' => $receita > 0 ? round($lucro / $receita * 100, 1) : 0,
                'participacao' => $totalReceita > 0 ? round($receita / $totalReceita * 100, 1) : 0,
                'vendas' => $r->vendas,
                'qtd' => (float)$r->qtd,
            ];
        })->toArray();

        return [
            'departamentos' => $departamentos,
            'total_receita' => $totalReceita,
            'total_custo' => $totalCusto,
            'total_lucro' => $totalReceita - $totalCusto,
            'margem_media' => $totalReceita > 0 ? round(($totalReceita - $totalCusto) / $totalReceita * 100, 1) : 0,
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.margem-departamento-manager')
            ->layout('components.layouts.app', ['title' => 'Margem por Departamento · ERP Mercado']);
    }
}

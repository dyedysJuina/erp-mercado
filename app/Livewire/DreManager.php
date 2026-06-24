<?php

namespace App\Livewire;

use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class DreManager extends Component
{
    public string $lojaFiltro = '';
    public string $mes = '';
    public string $ano = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(): void
    {
        $this->mes = now()->format('m');
        $this->ano = now()->format('Y');
    }

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function dre(): array
    {
        $ano = (int)$this->ano;
        $mes = (int)$this->mes;
        $inicio = now()->create($ano, $mes, 1)->startOfDay();
        $fim = now()->create($ano, $mes, 1)->endOfMonth()->endOfDay();

        // Receita Bruta (vendas concluídas)
        $vendasQ = DB::table('pdv_vendas')
            ->where('status', 'concluida')
            ->whereBetween('created_at', [$inicio, $fim]);
        if ($this->lojaFiltro) $vendasQ->where('loja_id', (int)$this->lojaFiltro);
        $receitaBruta = (float)$vendasQ->sum('total');

        // Receitas financeiras (exclui PDV que já está em receitaBruta)
        $recFinQ = DB::table('financeiro_lancamentos')
            ->where('tipo', 'receita')
            ->where('status', 'pago')
            ->whereNull('pdv_venda_id')
            ->whereBetween('data_pagamento', [$inicio, $fim]);
        if ($this->lojaFiltro) $recFinQ->where('loja_id', (int)$this->lojaFiltro);
        $receitasFinanceiras = (float)$recFinQ->sum('valor');

        // CMV (Custo das Mercadorias Vendidas)
        $cmv = DB::table('pdv_venda_itens')
            ->join('pdv_vendas', 'pdv_vendas.id', '=', 'pdv_venda_itens.venda_id')
            ->join('tabela_precos_itens', function ($j) {
                $j->on('tabela_precos_itens.produto_variacao_id', '=', 'pdv_venda_itens.produto_variacao_id')
                  ->whereExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('lojas')
                          ->whereColumn('lojas.id', 'pdv_vendas.loja_id')
                          ->whereColumn('lojas.tabela_preco_id', 'tabela_precos_itens.tabela_preco_id');
                  });
            })
            ->where('pdv_vendas.status', 'concluida')
            ->where('pdv_venda_itens.cancelado', false)
            ->whereBetween('pdv_vendas.created_at', [$inicio, $fim]);
        if ($this->lojaFiltro) $cmv->where('pdv_vendas.loja_id', (int)$this->lojaFiltro);
        $cmvValor = (float)$cmv->select(DB::raw('SUM(pdv_venda_itens.quantidade * COALESCE(tabela_precos_itens.preco_custo, 0)) as cmv'))->value('cmv');

        // Despesas operacionais
        $despQ = DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')
            ->where('status', 'pago')
            ->whereBetween('data_pagamento', [$inicio, $fim]);
        if ($this->lojaFiltro) $despQ->where('loja_id', (int)$this->lojaFiltro);
        $despesas = (float)$despQ->sum('valor');

        // Contas a pagar (pendentes)
        $aPagarQ = DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')
            ->where('status', 'pendente')
            ->whereBetween('data_vencimento', [$inicio, $fim]);
        if ($this->lojaFiltro) $aPagarQ->where('loja_id', (int)$this->lojaFiltro);
        $aPagar = (float)$aPagarQ->sum('valor');

        // Totais
        $receitaTotal = $receitaBruta + $receitasFinanceiras;
        $lucroBruto = $receitaTotal - $cmvValor;
        $resultadoLiquido = $lucroBruto - $despesas;

        return [
            'mes' => $mes,
            'ano' => $ano,
            'receita_bruta' => $receitaBruta,
            'receitas_financeiras' => $receitasFinanceiras,
            'receita_total' => $receitaTotal,
            'cmv' => $cmvValor,
            'lucro_bruto' => $lucroBruto,
            'margem_bruta' => $receitaTotal > 0 ? round($lucroBruto / $receitaTotal * 100, 1) : 0,
            'despesas' => $despesas,
            'resultado_liquido' => $resultadoLiquido,
            'margem_liquida' => $receitaTotal > 0 ? round($resultadoLiquido / $receitaTotal * 100, 1) : 0,
            'a_pagar' => $aPagar,
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.dre-manager')
            ->layout('components.layouts.app', ['title' => 'DRE — Resultado · ERP Mercado']);
    }
}

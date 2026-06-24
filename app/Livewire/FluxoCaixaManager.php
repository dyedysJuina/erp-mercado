<?php

namespace App\Livewire;

use App\Models\Loja;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class FluxoCaixaManager extends Component
{
    public string $lojaFiltro = '';
    public string $diasProjecao = '30';

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
        $dias = (int)$this->diasProjecao;
        $hoje = now()->startOfDay();
        $fim = now()->addDays($dias)->endOfDay();

        // Saldo atual (receitas pagas - despesas pagas)
        $receitasPagas = (float) DB::table('financeiro_lancamentos')
            ->where('tipo', 'receita')->where('status', 'pago')
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->sum('valor');
        $despesasPagas = (float) DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')->where('status', 'pago')
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->sum('valor');
        $saldoAtual = $receitasPagas - $despesasPagas;

        // Vendas PDV concluídas (todas são receitas já realizadas)
        $vendasPDV = (float) DB::table('pdv_vendas')->where('status', 'concluida')
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->sum('total');
        $saldoReal = $saldoAtual + $vendasPDV;

        // A receber (pendentes)
        $aReceber = (float) DB::table('financeiro_lancamentos')
            ->where('tipo', 'receita')->whereIn('status', ['pendente', 'atrasado'])
            ->whereDate('data_vencimento', '<=', $fim)
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->sum('valor');

        // A pagar (pendentes)
        $aPagar = (float) DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')->whereIn('status', ['pendente', 'atrasado'])
            ->whereDate('data_vencimento', '<=', $fim)
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->sum('valor');

        // Projeção diária
        $projecao = [];
        $saldoProjetado = $saldoReal;
        for ($i = 0; $i <= $dias; $i++) {
            $dia = $hoje->copy()->addDays($i);
            $entradas = (float) DB::table('financeiro_lancamentos')
                ->where('tipo', 'receita')->whereIn('status', ['pendente', 'atrasado'])
                ->whereDate('data_vencimento', $dia)
                ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
                ->sum('valor');
            $saidas = (float) DB::table('financeiro_lancamentos')
                ->where('tipo', 'despesa')->whereIn('status', ['pendente', 'atrasado'])
                ->whereDate('data_vencimento', $dia)
                ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
                ->sum('valor');
            $saldoProjetado += $entradas - $saidas;
            $projecao[] = [
                'dia' => $dia->format('d/m'),
                'dia_semana' => $dia->isoWeekday(),
                'entradas' => $entradas,
                'saidas' => $saidas,
                'saldo' => $saldoProjetado,
            ];
        }

        // Contas a pagar agrupadas por vencimento (próximos 7 dias)
        $proximosVencimentos = DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')->whereIn('status', ['pendente', 'atrasado'])
            ->whereBetween('data_vencimento', [$hoje, $hoje->copy()->addDays(7)])
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->select('descricao', 'valor', 'data_vencimento')
            ->orderBy('data_vencimento')
            ->get()
            ->toArray();

        return [
            'saldo_atual' => $saldoAtual,
            'vendas_pdv' => $vendasPDV,
            'saldo_real' => $saldoReal,
            'a_receber' => $aReceber,
            'a_pagar' => $aPagar,
            'saldo_projetado_final' => $saldoProjetado,
            'projecao' => $projecao,
            'proximos_vencimentos' => $proximosVencimentos,
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.fluxo-caixa-manager')
            ->layout('components.layouts.app', ['title' => 'Fluxo de Caixa · ERP Mercado']);
    }
}

<?php

namespace App\Livewire;

use App\Models\FinanceiroLancamento;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ConciliacaoManager extends Component
{
    use WithPagination;

    public string $aba = 'pendentes';
    public string $busca = '';
    public string $filtroTipo = '';
    public string $filtroLoja = '';
    public string $dataInicio = '';
    public string $dataFim = '';
    public string $dataConcilia = '';

    public array $loteIds = [];
    public bool $selectAll = false;

    public string $toastMsg = '';
    public bool $toastShow = false;

    private function lojaScope($q)
    {
        $lojaId = auth()->user()->loja_id;
        if ($this->filtroLoja) $lojaId = (int)$this->filtroLoja;
        if ($lojaId) $q->where('loja_id', $lojaId);
        return $q;
    }

    public function pendentes()
    {
        $q = FinanceiroLancamento::where('status', 'pago')->whereNull('reconcilied_at')->orderBy('data_pagamento', 'desc');
        $this->lojaScope($q);
        if ($this->filtroTipo) $q->where('tipo', $this->filtroTipo);
        if ($this->dataInicio) $q->whereDate('data_pagamento', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('data_pagamento', '<=', $this->dataFim);
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('descricao', 'like', '%' . $this->busca . '%');
        }
        return $q->paginate(25);
    }

    public function reconciliados()
    {
        $q = FinanceiroLancamento::whereNotNull('reconcilied_at')->with('reconciliedBy')->orderBy('reconcilied_at', 'desc');
        $this->lojaScope($q);
        if ($this->filtroTipo) $q->where('tipo', $this->filtroTipo);
        if ($this->dataInicio) $q->whereDate('reconcilied_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('reconcilied_at', '<=', $this->dataFim);
        return $q->paginate(25);
    }

    #[Computed]
    public function totais(): array
    {
        $pendentes = FinanceiroLancamento::where('status', 'pago')->whereNull('reconcilied_at');
        $this->lojaScope($pendentes);
        $recon = FinanceiroLancamento::whereNotNull('reconcilied_at');
        $this->lojaScope($recon);
        return [
            'pendente_total' => (float)$pendentes->sum('valor'),
            'pendente_count' => $pendentes->count(),
            'recon_total' => (float)$recon->sum('valor'),
            'recon_count' => $recon->count(),
        ];
    }

    public function conciliar(int $id): void
    {
        FinanceiroLancamento::where('id', $id)->whereNull('reconcilied_at')->update([
            'reconcilied_at' => $this->dataConcilia ?: now(),
            'reconcilied_by' => Auth::id(),
        ]);
        $this->toast('Lançamento conciliado!');
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $ids = FinanceiroLancamento::where('status', 'pago')->whereNull('reconcilied_at')
                ->when(true, fn($q) => $this->lojaScope($q))
                ->when($this->filtroTipo, fn($q) => $q->where('tipo', $this->filtroTipo))
                ->when($this->dataInicio, fn($q) => $q->whereDate('data_pagamento', '>=', $this->dataInicio))
                ->when($this->dataFim, fn($q) => $q->whereDate('data_pagamento', '<=', $this->dataFim))
                ->when(strlen(trim($this->busca)) >= 2, fn($q) => $q->where('descricao', 'like', '%' . $this->busca . '%'))
                ->pluck('id')->toArray();
            $this->loteIds = $ids;
        } else {
            $this->loteIds = [];
        }
    }

    public function conciliarLote(): void
    {
        if (empty($this->loteIds)) return;
        FinanceiroLancamento::whereIn('id', $this->loteIds)->whereNull('reconcilied_at')->update([
            'reconcilied_at' => $this->dataConcilia ?: now(),
            'reconcilied_by' => Auth::id(),
        ]);
        $count = count($this->loteIds);
        $this->loteIds = [];
        $this->selectAll = false;
        $this->toast("{$count} lançamento(s) conciliado(s)!");
    }

    public function estornarConciliacao(int $id): void
    {
        FinanceiroLancamento::where('id', $id)->update([
            'reconcilied_at' => null,
            'reconcilied_by' => null,
        ]);
        $this->toast('Conciliação estornada!');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.conciliacao-manager')
            ->layout('components.layouts.app', ['title' => 'Conciliação Bancária · ERP Mercado']);
    }
}

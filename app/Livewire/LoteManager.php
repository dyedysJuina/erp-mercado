<?php

namespace App\Livewire;

use App\Models\EstoqueLote;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Carbon\Carbon;

class LoteManager extends Component
{
    use WithPagination;
    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $busca = '';
    public string $filtroStatus = 'todos';
    public string $dataInicio = '';
    public string $dataFim = '';

    #[Computed]
    public function totalLotes(): int { return EstoqueLote::count(); }

    #[Computed]
    public function expirando30dias(): int
    {
        return EstoqueLote::whereBetween('data_validade', [now(), now()->addDays(30)])->count();
    }

    #[Computed]
    public function vencidos(): int
    {
        return EstoqueLote::where('data_validade', '<', now())->count();
    }

    public function lotes()
    {
        $q = EstoqueLote::with('variacao.unidadeMedida')
            ->orderBy('data_validade');

        if (strlen(trim($this->busca)) >= 2) {
            $q->whereHas('variacao', fn($w) => $w->where('nome_completo', 'like', "%{$this->busca}%")
                ->orWhere('sku', 'like', "%{$this->busca}%"));
        }

        if ($this->filtroStatus === 'vencido') {
            $q->where('data_validade', '<', now());
        } elseif ($this->filtroStatus === 'expirando') {
            $q->whereBetween('data_validade', [now(), now()->addDays(30)]);
        } elseif ($this->filtroStatus === 'valido') {
            $q->where('data_validade', '>=', now()->addDays(30));
        }

        if ($this->dataInicio) $q->whereDate('data_validade', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('data_validade', '<=', $this->dataFim);

        return $q->paginate(25);
    }

    public function limparFiltros(): void
    {
        $this->busca = '';
        $this->filtroStatus = 'todos';
        $this->dataInicio = '';
        $this->dataFim = '';
        $this->toast('Filtros limpos.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.lote-manager')
            ->layout('components.layouts.app', ['title' => 'Lotes e Validades · ERP Mercado']);
    }
}

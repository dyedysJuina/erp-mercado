<?php

namespace App\Livewire;

use App\Models\CompraPedido;
use App\Models\CompraPedidoItem;
use App\Models\Fornecedor;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PedidoListaManager extends Component
{
    use WithPagination;

    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $busca = '';
    public string $filtroStatus = '';
    public string $filtroFornecedor = '';
    public string $dataInicio = '';
    public string $dataFim = '';

    #[Computed]
    public function fornecedores(): array
    {
        return Fornecedor::orderBy('razao_social')->get(['id', 'razao_social'])->toArray();
    }

    public function pedidos()
    {
        $q = CompraPedido::with('fornecedor')
            ->withCount(['itens as total_pedido_qtd' => fn($q) => $q->select(DB::raw('COALESCE(SUM(quantidade_pedida),0)'))])
            ->withCount(['itens as total_recebido_qtd' => fn($q) => $q->select(DB::raw('COALESCE(SUM(quantidade_recebida),0)'))])
            ->orderBy('created_at', 'desc');

        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('id', 'like', '%' . $this->busca . '%')
                  ->orWhereHas('fornecedor', fn($f) => $f->where('razao_social', 'like', '%' . $this->busca . '%'));
            });
        }
        if ($this->filtroStatus) $q->where('status', $this->filtroStatus);
        if ($this->filtroFornecedor) $q->where('fornecedor_id', (int)$this->filtroFornecedor);
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);

        return $q->paginate(25);
    }

    #[Computed]
    public function totalPedidos(): int { return CompraPedido::count(); }
    #[Computed]
    public function totalGasto(): float { return (float) CompraPedido::sum('total_pedido'); }
    #[Computed]
    public function pedidosPendentes(): int { return CompraPedido::whereIn('status', ['rascunho', 'enviado'])->count(); }
    #[Computed]
    public function pedidosMes(): int { return CompraPedido::whereMonth('created_at', now()->month)->count(); }
    #[Computed]
    public function gastoMes(): float { return (float) CompraPedido::whereMonth('created_at', now()->month)->sum('total_pedido'); }

    public function limparFiltros(): void
    {
        $this->busca = '';
        $this->filtroStatus = '';
        $this->filtroFornecedor = '';
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
        return view('livewire.pedido-lista-manager')
            ->layout('components.layouts.app', ['title' => 'Pedidos · ERP Mercado']);
    }
}

<?php

namespace App\Livewire;

use App\Models\Pedido;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class PedidoOnlineManager extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $busca = '';
    public string $filtroStatus = '';
    public string $filtroEntrega = '';
    public string $dataInicio = '';
    public string $dataFim = '';
    public string $periodo = 'hoje';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(): void
    {
        $this->dataInicio = now()->startOfDay()->format('Y-m-d');
        $this->dataFim = now()->endOfDay()->format('Y-m-d');
    }

    #[Computed]
    public function totais(): array
    {
        $q = Pedido::where('origem', 'site');
        if ($lojaId = auth()->user()->loja_id) {
            $q->where('loja_id', $lojaId);
        }
        return [
            'hoje' => (clone $q)->whereDate('created_at', today())->count(),
            'novos' => (clone $q)->where('status', 'recebido')->count(),
            'separacao' => (clone $q)->where('status', 'em_separacao')->count(),
            'prontos' => (clone $q)->whereIn('status', ['pronto_retirada', 'pronto_entrega'])->count(),
            'atrasados' => (clone $q)->whereNotIn('status', ['entregue', 'cancelado'])
                ->whereRaw('(slot_inicio IS NOT NULL AND slot_inicio < NOW())')->count(),
            'faturamento' => (clone $q)->whereDate('created_at', today())->sum('total'),
        ];
    }

    public function listagem()
    {
        $q = Pedido::where('origem', 'site');
        if ($lojaId = auth()->user()->loja_id) {
            $q->where('loja_id', $lojaId);
        }
        $q = $q->with(['cliente', 'itens.variacao', 'separador', 'entregador'])
            ->orderBy('created_at', 'desc');

        if ($this->periodo === 'hoje') {
            $q->whereDate('created_at', today());
        } elseif ($this->periodo === 'semana') {
            $q->whereDate('created_at', '>=', now()->startOfWeek(Carbon::MONDAY));
        }
        if ($this->filtroStatus) $q->where('status', $this->filtroStatus);
        if ($this->filtroEntrega) $q->where('tipo_entrega', $this->filtroEntrega);
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);
        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('id', (int)$this->busca)
                  ->orWhere('codigo', 'like', "%{$this->busca}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$this->busca}%"));
            });
        }

        return $q->paginate(30);
    }

    public function statusDoPedido(Pedido $p): array
    {
        $agora = now();
        $atrasado = $p->slot_inicio && $p->slot_inicio < $agora && !in_array($p->status, ['entregue', 'cancelado']);
        $minutos = $p->slot_inicio ? $agora->diffInMinutes($p->slot_inicio, false) : 0;
        return [
            'atrasado' => $atrasado,
            'minutos_atraso' => $atrasado ? abs((int)$minutos) : 0,
        ];
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.pedido-online-manager')
            ->layout('components.layouts.app', ['title' => 'Central de Pedidos · ERP Mercado']);
    }
}

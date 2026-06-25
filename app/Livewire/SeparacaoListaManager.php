<?php

namespace App\Livewire;

use App\Models\Pedido;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SeparacaoListaManager extends Component
{
    use WithPagination;

    public string $busca = '';
    public string $toastMsg = '';
    public bool $toastShow = false;

    protected $queryString = ['busca'];

    #[Computed]
    #[Computed]
    public function pendentes()
    {
        $q = Pedido::where('origem', 'site')
            ->whereIn('status', ['recebido', 'confirmado', 'em_separacao'])
            ->with(['cliente', 'itens'])
            ->orderBy('created_at', 'asc');

        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('id', (int)$this->busca)
                  ->orWhere('codigo', 'like', "%{$this->busca}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$this->busca}%"));
            });
        }

        $pedidos = $q->get();

        $agora = now();
        $atrasados = [];
        $normais = [];

        foreach ($pedidos as $p) {
            $totalItens = $p->itens->count();
            $separados = $p->itens->whereIn('status_item', ['separado'])->count();
            $pct = $totalItens > 0 ? round(($separados / $totalItens) * 100) : 0;
            $minutos = $p->created_at->diffInMinutes($agora);
            $atrasado = $minutos > 30 && !in_array($p->status, ['pronto_retirada', 'pronto_entrega', 'entregue', 'cancelado']);

            $data = [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'cliente_nome' => $p->cliente?->nome ?? '—',
                'cliente_whatsapp' => $p->cliente?->whatsapp ?? '',
                'total_itens' => $totalItens,
                'total_separados' => $separados,
                'progresso' => $pct,
                'total' => (float)$p->total,
                'minutos_atraso' => $minutos,
                'status' => $p->status,
                'ja_iniciou' => $p->status === 'em_separacao',
            ];

            if ($atrasado) {
                $atrasados[] = $data;
            } else {
                $normais[] = $data;
            }
        }

        return [
            'atrasados' => $atrasados,
            'normais' => $normais,
            'total' => count($pedidos),
        ];
    }

    #[Computed]
    public function totalPendentes(): int
    {
        return Pedido::where('origem', 'site')
            ->whereIn('status', ['recebido', 'confirmado', 'em_separacao'])
            ->count();
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.separacao-lista-manager')
            ->layout('components.layouts.app', ['title' => 'Separação · ERP Mercado', 'noSidebar' => true]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoSeparacao;
use App\Models\PedidoStatusHistorico;
use Livewire\Component;
use Livewire\WithPagination;

class SeparacaoListaManager extends Component
{
    use WithPagination;

    public string $busca = '';
    public string $toastMsg = '';
    public bool $toastShow = false;

    protected $queryString = ['busca'];

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

        $totalAll = (clone $q)->count();
        $pedidos = $q->paginate(20);
        $agora = now();
        $atrasados = [];
        $normais = [];

        foreach ($pedidos as $p) {
            $totalItens = $p->itens->count();
            $separados = $p->itens->whereIn('status_item', ['separado'])->count();
            $faltou = $p->itens->whereIn('status_item', ['faltou'])->count();
            $pendentes = $p->itens->whereIn('status_item', ['pendente'])->count();
            $substituidos = $p->itens->whereIn('status_item', ['substituido'])->count();
            $pct = $totalItens > 0 ? round((($separados + $substituidos) / $totalItens) * 100) : 0;

            $segundos = $p->created_at->diffInSeconds($agora);
            $atrasado = $segundos > 1800 && !in_array($p->status, ['pronto_retirada', 'pronto_entrega', 'entregue', 'cancelado']);

            if ($segundos < 60) {
                $tempo = $segundos . 's';
            } elseif ($segundos < 3600) {
                $m = floor($segundos / 60);
                $s = $segundos % 60;
                $tempo = $m . 'min ' . ($s > 0 ? $s . 's' : '');
            } elseif ($segundos < 86400) {
                $h = floor($segundos / 3600);
                $m = floor(($segundos % 3600) / 60);
                $tempo = $h . 'h ' . ($m > 0 ? $m . 'min' : '');
            } else {
                $d = floor($segundos / 86400);
                $h = floor(($segundos % 86400) / 3600);
                $tempo = $d . 'd ' . ($h > 0 ? $h . 'h' : '');
            }

            $data = [
                'id' => $p->id, 'codigo' => $p->codigo,
                'cliente_nome' => $p->cliente?->nome ?? '—',
                'cliente_whatsapp' => $p->cliente?->whatsapp ?? '',
                'total_itens' => $totalItens, 'total_separados' => $separados,
                'total_faltou' => $faltou, 'total_pendentes' => $pendentes,
                'total_substituidos' => $substituidos,
                'progresso' => $pct, 'total' => (float)$p->total,
                'segundos_atraso' => $segundos, 'minutos_atraso' => round($segundos / 60), 'tempo_atraso' => $tempo,
                'status' => $p->status,
                'ja_iniciou' => $p->status === 'em_separacao',
                'created_at' => $p->created_at->format('d/m/Y H:i'),
            ];

            if ($atrasado) { $atrasados[] = $data; }
            else { $normais[] = $data; }
        }

        return [
            'atrasados' => $atrasados, 'normais' => $normais,
            'total' => $totalAll, 'paginator' => $pedidos,
        ];
    }

    public function totalPendentes(): int
    {
        return Pedido::where('origem', 'site')
            ->whereIn('status', ['recebido', 'confirmado', 'em_separacao'])
            ->count();
    }

    public function cancelarPedido(int $id): void
    {
        $pedido = Pedido::find($id);
        if (!$pedido) return;

        if (in_array($pedido->status, ['pronto_retirada', 'pronto_entrega', 'entregue', 'cancelado'])) {
            $this->toast('Pedido já finalizado ou cancelado.');
            return;
        }

        $statusAntigo = $pedido->status;

        $pedido->update(['status' => 'cancelado']);
        $pedido->itens()->update(['status_item' => 'cancelado']);

        try {
            PedidoSeparacao::where('pedido_id', $id)
                ->whereIn('status', ['em_andamento', 'pausada'])
                ->update(['status' => 'cancelada', 'fim_at' => now()]);
        } catch (\Exception $e) {
            // Tabela pode não existir — segue
        }

        try {
            PedidoStatusHistorico::create([
                'pedido_id' => $pedido->id,
                'usuario_id' => auth()->id(),
                'status_anterior' => $statusAntigo,
                'status_novo' => 'cancelado',
                'observacao' => 'Cancelado via separação',
            ]);
        } catch (\Exception $e) {
            // Tabela pode não existir — segue
        }

        $this->toast('Pedido #' . $id . ' cancelado.');
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

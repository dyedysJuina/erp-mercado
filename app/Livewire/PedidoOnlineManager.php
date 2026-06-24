<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\User;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PedidoOnlineManager extends Component
{
    use WithPagination;

    public string $busca = '';
    public string $filtroStatus = '';
    public string $filtroEntrega = '';
    public string $dataInicio = '';
    public string $dataFim = '';
    public string $periodo = 'hoje';

    public ?int $detalheId = null;
    public ?int $pedidoEditando = null;

    // Item management
    public ?int $itemMudarStatus = null;
    public string $itemNovoStatus = '';
    public string $itemObs = '';

    // Substitution
    public ?int $itemSubstituir = null;
    public string $buscaSubstituto = '';
    public array $resultadosSubstituto = [];
    public ?int $substitutoId = null;

    // Assignment
    public string $separadorId = '';
    public string $entregadorId = '';

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

    #[Computed]
    public function operadores(): array
    {
        return User::where('ativo', true)->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function listagem()
    {
        $q = Pedido::where('origem', 'site')
            ->with(['cliente', 'itens.variacao', 'separador', 'entregador'])
            ->orderByRaw("FIELD(status, 'recebido','confirmado','em_separacao','pronto_retirada','pronto_entrega')")
            ->orderBy('created_at', 'desc');

        if ($this->periodo === 'hoje') {
            $q->whereDate('created_at', today());
        } elseif ($this->periodo === 'semana') {
            $q->whereDate('created_at', '>=', now()->startOfWeek());
        }
        if ($this->filtroStatus) $q->where('status', $this->filtroStatus);
        if ($this->filtroEntrega) $q->where('tipo_entrega', $this->filtroEntrega);
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);
        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('codigo', 'like', "%{$this->busca}%")
                  ->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$this->busca}%"));
            });
        }

        return $q->paginate(30);
    }

    public function verDetalhe(int $id): void
    {
        $this->detalheId = $id;
        $this->pedidoEditando = $id;
        $p = Pedido::with('separador', 'entregador')->find($id);
        if ($p) {
            $this->separadorId = (string)$p->separador_id;
            $this->entregadorId = (string)$p->entregador_id;
        }
    }

    public function fecharDetalhe(): void
    {
        $this->detalheId = null;
        $this->pedidoEditando = null;
    }

    #[Computed]
    public function detalhe(): ?array
    {
        if (!$this->detalheId) return null;
        return Pedido::with([
            'cliente', 'separador', 'entregador',
            'itens' => fn($q) => $q->with('variacao', 'substituto')
        ])->find($this->detalheId)?->toArray();
    }

    public function alterarStatus(int $id, string $status): void
    {
        Pedido::findOrFail($id)->update(['status' => $status]);
        $this->toast("Pedido #{$id} alterado para '{$status}'.");
    }

    public function salvarOperadores(): void
    {
        if (!$this->pedidoEditando) return;
        Pedido::findOrFail($this->pedidoEditando)->update([
            'separador_id' => $this->separador_id ? (int)$this->separador_id : null,
            'entregador_id' => $this->entregador_id ? (int)$this->entregador_id : null,
        ]);
        $this->toast('Operadores atribuidos.');
    }

    public function alterarStatusItem(int $itemId, string $status): void
    {
        DB::table('pedidos_itens')->where('id', $itemId)->update([
            'status_item' => $status,
            'separado_por' => $status === 'separado' ? auth()->id() : DB::raw('separado_por'),
        ]);
        $this->toast('Item atualizado.');
    }

    public function sugerirSubstituto(int $itemId): void
    {
        $this->itemSubstituir = $itemId;
        $this->buscaSubstituto = '';
        $this->resultadosSubstituto = [];
        $this->substitutoId = null;
    }

    public function buscarSubstituto(): void
    {
        $q = trim($this->buscaSubstituto);
        if (strlen($q) < 2) { $this->resultadosSubstituto = []; return; }
        $this->resultadosSubstituto = ProdutoVariacao::where('ativo', true)
            ->where(function ($w) use ($q) {
                $w->where('nome_completo', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            })
            ->with('marca')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function confirmarSubstituicao(): void
    {
        if (!$this->itemSubstituir || !$this->substitutoId) return;
        DB::table('pedidos_itens')->where('id', $this->itemSubstituir)->update([
            'status_item' => 'substituido',
            'substituto_produto_variacao_id' => $this->substitutoId,
        ]);
        $this->itemSubstituir = null;
        $this->buscaSubstituto = '';
        $this->resultadosSubstituto = [];
        $this->substitutoId = null;
        $this->toast('Substituicao registrada.');
    }

    public function cancelarSubstituicao(): void
    {
        $this->itemSubstituir = null;
        $this->buscaSubstituto = '';
        $this->resultadosSubstituto = [];
        $this->substitutoId = null;
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

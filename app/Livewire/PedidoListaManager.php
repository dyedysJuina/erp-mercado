<?php

namespace App\Livewire;

use App\Models\CompraPedido;
use App\Models\CompraPedidoItem;
use App\Models\CompraRecebimento;
use App\Models\CompraRecebimentoItem;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueLote;
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

    public ?int $detalhePedidoId = null;

    public bool $receberModalOpen = false;
    public ?int $receberPedidoId = null;
    public array $receberItens = [];
    public string $receberNota = '';
    public string $receberChave = '';

    #[Computed]
    public function fornecedores(): array
    {
        return Fornecedor::orderBy('razao_social')->get(['id', 'razao_social'])->toArray();
    }

    public function pedidos()
    {
        $q = CompraPedido::with('fornecedor')
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

    public function verDetalhe(int $id): void
    {
        $this->detalhePedidoId = $this->detalhePedidoId === $id ? null : $id;
    }

    public function abrirReceber(int $id): void
    {
        $pedido = CompraPedido::with('itens.variacao')->findOrFail($id);
        $this->receberPedidoId = $id;
        $this->receberNota = '';
        $this->receberChave = '';
        $this->receberItens = $pedido->itens->map(fn($i) => [
            'item_id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'pedido' => (float)$i->quantidade_pedida,
            'recebido' => (float)$i->quantidade_recebida,
            'pendente' => max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida),
            'receber' => max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida),
            'lote' => '',
            'validade' => '',
        ])->toArray();
        $this->receberModalOpen = true;
    }

    public function confirmarRecebimento(): void
    {
        $pedido = CompraPedido::findOrFail($this->receberPedidoId);

        DB::transaction(function () use ($pedido) {
            $rec = CompraRecebimento::create([
                'compra_pedido_id' => $pedido->id,
                'loja_id' => $pedido->loja_id,
                'fornecedor_id' => $pedido->fornecedor_id,
                'usuario_id' => auth()->id(),
                'numero_nota' => $this->receberNota ?: null,
                'chave_nfe' => $this->receberChave ?: null,
                'status' => 'conferido',
            ]);

            foreach ($this->receberItens as $r) {
                $qtd = (float)$r['receber'];
                if ($qtd <= 0) continue;

                CompraRecebimentoItem::create([
                    'recebimento_id' => $rec->id,
                    'produto_variacao_id' => (int)$r['variacao_id'],
                    'quantidade_recebida' => $qtd,
                ]);

                CompraPedidoItem::find($r['item_id'])?->increment('quantidade_recebida', $qtd);

                EstoqueSaldo::firstOrCreate(
                    ['loja_id' => $pedido->loja_id, 'produto_variacao_id' => (int)$r['variacao_id']],
                    ['quantidade_atual' => 0]
                )->increment('quantidade_atual', $qtd);

                if (!empty($r['lote'])) {
                    EstoqueLote::create([
                        'loja_id' => $pedido->loja_id,
                        'produto_variacao_id' => (int)$r['variacao_id'],
                        'numero_lote' => $r['lote'],
                        'data_validade' => $r['validade'] ?: null,
                        'quantidade_atual' => $qtd,
                    ]);
                }
            }

            $pendentes = CompraPedidoItem::where('compra_pedido_id', $pedido->id)
                ->whereRaw('quantidade_recebida < quantidade_pedida')->count();
            $pedido->update(['status' => $pendentes > 0 ? 'parcialmente_recebido' : 'recebido']);
        });

        $this->receberModalOpen = false;
        $this->receberPedidoId = null;
        $this->detalhePedidoId = null;
        $this->toast('Recebimento registrado com sucesso!');
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

    public function enviar(int $id): void
    {
        CompraPedido::findOrFail($id)->update(['status' => 'enviado']);
        $this->toast("Pedido #{$id} marcado como enviado!");
    }

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

<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class SeparacaoManager extends Component
{
    public int $pedidoId;
    public int $itemAtual = 0;
    public array $itens = [];
    public array $processados = [];
    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(int $id): void
    {
        $this->pedidoId = $id;
        $this->carregarItens();
    }

    public function carregarItens(): void
    {
        $pedido = Pedido::with('itens.variacao')->findOrFail($this->pedidoId);
        $this->itens = $pedido->itens->filter(fn($i) => $i->status_item !== 'cancelado')
            ->values()
            ->map(fn($i) => [
                'id' => $i->id,
                'variacao_id' => $i->produto_variacao_id,
                'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
                'qtd_pedido' => (float)$i->quantidade_solicitada,
                'qtd_separada' => (float)($i->quantidade_separada ?: 0),
                'status' => $i->status_item,
                'observacao' => $i->observacao_separacao ?? '',
                'sku' => $i->variacao?->sku ?? '',
            ])->toArray();

        $this->processados = [];
        $this->itemAtual = 0;
    }

    public function getItemProperty(): ?array
    {
        return $this->itens[$this->itemAtual] ?? null;
    }

    #[Computed]
    public function pedido(): ?Pedido
    {
        return Pedido::with('cliente')->find($this->pedidoId);
    }

    #[Computed]
    public function itensRestantes(): int
    {
        return count($this->itens) - $this->itemAtual;
    }

    #[Computed]
    public function progresso(): array
    {
        $total = count($this->itens);
        $feitos = count($this->processados);
        return [
            'total' => $total,
            'feitos' => $feitos,
            'pct' => $total > 0 ? round(($feitos / $total) * 100) : 0,
        ];
    }

    public function definirQuantidade(float $qtd): void
    {
        if (isset($this->itens[$this->itemAtual])) {
            $this->itens[$this->itemAtual]['qtd_separada'] = max(0, min($this->itens[$this->itemAtual]['qtd_pedido'], $qtd));
        }
    }

    public function incrementar(): void
    {
        $this->definirQuantidade(($this->itens[$this->itemAtual]['qtd_separada'] ?? 0) + 1);
    }

    public function decrementar(): void
    {
        $this->definirQuantidade(($this->itens[$this->itemAtual]['qtd_separada'] ?? 0) - 1);
    }

    public function definirStatus(string $status): void
    {
        if (!isset($this->itens[$this->itemAtual])) return;
        $item = &$this->itens[$this->itemAtual];

        if ($status === 'ok') {
            $item['qtd_separada'] = $item['qtd_pedido'];
            $item['status'] = 'separado';
            $item['observacao'] = 'OK';
        } elseif ($status === 'parcial') {
            $item['qtd_separada'] = max(1, $item['qtd_pedido'] - 1);
            $item['status'] = 'separado';
            $item['observacao'] = 'Falta ' . ($item['qtd_pedido'] - $item['qtd_separada']) . ' un';
        } elseif ($status === 'faltou') {
            $item['qtd_separada'] = 0;
            $item['status'] = 'faltou';
            $item['observacao'] = 'Sem estoque na gôndola';
        }
    }

    public function confirmarProximo(): void
    {
        $item = $this->itens[$this->itemAtual] ?? null;
        if (!$item) return;

        // Salva no banco
        PedidoItem::where('id', $item['id'])->update([
            'status_item' => $item['status'] ?: ($item['qtd_separada'] > 0 ? 'separado' : 'faltou'),
            'quantidade_separada' => $item['qtd_separada'],
            'observacao_separacao' => $item['observacao'] ?: null,
            'separado_por' => auth()->id(),
        ]);

        $this->processados[] = $item;
        $this->itemAtual++;

        // Se acabaram os itens, atualiza o status do pedido
        if ($this->itemAtual >= count($this->itens)) {
            $pedido = Pedido::find($this->pedidoId);
            if ($pedido && $pedido->status === 'recebido') {
                $pedido->update(['status' => 'em_separacao']);
            }
        }
    }

    public function pularItem(): void
    {
        $this->itemAtual++;
    }

    public function finalizarSeparacao()
    {
        // Processa itens restantes
        for ($i = $this->itemAtual; $i < count($this->itens); $i++) {
            $item = $this->itens[$i];
            PedidoItem::where('id', $item['id'])->update([
                'status_item' => $item['status'] ?: ($item['qtd_separada'] > 0 ? 'separado' : 'faltou'),
                'quantidade_separada' => $item['qtd_separada'],
                'observacao_separacao' => $item['observacao'] ?: null,
            ]);
        }

        $pedido = Pedido::find($this->pedidoId);
        if ($pedido) {
            $pendentes = PedidoItem::where('pedido_id', $pedido->id)
                ->whereIn('status_item', ['pendente', 'faltou', 'cancelado'])->count();
            $pedido->update(['status' => $pendentes === 0 ? 'pronto_retirada' : 'em_separacao']);
        }

        $this->toast('Separação finalizada!');
        return $this->redirect('/separacao');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.separacao-manager')
            ->layout('components.layouts.app', ['title' => 'Separando Pedido #' . $this->pedidoId . ' · ERP Mercado', 'noSidebar' => true]);
    }
}

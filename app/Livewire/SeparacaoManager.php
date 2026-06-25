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
    public array $cancelados = [];
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

        $todos = $pedido->itens->values();

        $this->cancelados = $todos->filter(fn($i) => $i->status_item === 'cancelado')
            ->map(fn($i) => $this->mapearItem($i))->toArray();

        $restantes = $todos->filter(fn($i) => $i->status_item !== 'cancelado');
        $jaProcessados = $restantes->filter(fn($i) => in_array($i->status_item, ['separado', 'faltou', 'substituido']));
        $pendentes = $restantes->filter(fn($i) => $i->status_item === 'pendente');

        $this->processados = $jaProcessados->map(fn($i) => $this->mapearItem($i))->toArray();
        $this->itens = $pendentes->map(fn($i) => $this->mapearItem($i))->toArray();
        $this->itemAtual = 0;
    }

    private function mapearItem($i): array
    {
        return [
            'id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'qtd_pedido' => (float)$i->quantidade_solicitada,
            'qtd_separada' => (float)($i->quantidade_separada ?: 0),
            'status' => $i->status_item,
            'observacao' => $i->observacao_separacao ?? '',
            'sku' => $i->variacao?->sku ?? '',
        ];
    }

    public function getItemProperty(): ?array
    {
        return $this->itens[$this->itemAtual] ?? null;
    }

    public function pedido(): ?Pedido
    {
        return Pedido::with('cliente')->find($this->pedidoId);
    }

    public function itensRestantes(): int
    {
        return count($this->itens) - $this->itemAtual;
    }

    public function progresso(): array
    {
        $total = count($this->itens) + count($this->processados);
        $feitos = count($this->processados);
        $separados = count(array_filter($this->processados, fn($i) => $i['status'] === 'separado'));
        $faltou = count(array_filter($this->processados, fn($i) => $i['status'] === 'faltou'));
        $substituidos = count(array_filter($this->processados, fn($i) => $i['status'] === 'substituido'));

        $pctTotal = $total > 0 ? round(($feitos / $total) * 100) : 0;
        $pctOk = $total > 0 ? round(($separados / $total) * 100) : 0;
        $pctFaltou = $total > 0 ? round(($faltou / $total) * 100) : 0;
        $pctSubst = $total > 0 ? round(($substituidos / $total) * 100) : 0;

        return [
            'total' => $total, 'feitos' => $feitos, 'pct' => $pctTotal,
            'separados' => $separados, 'faltou' => $faltou, 'substituidos' => $substituidos,
            'pctOk' => $pctOk, 'pctFaltou' => $pctFaltou, 'pctSubst' => $pctSubst,
            'cancelados' => count($this->cancelados),
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

        PedidoItem::where('id', $item['id'])->update([
            'status_item' => $item['status'] ?: ($item['qtd_separada'] > 0 ? 'separado' : 'faltou'),
            'quantidade_separada' => $item['qtd_separada'],
            'observacao_separacao' => $item['observacao'] ?: null,
            'separado_por' => auth()->id(),
        ]);

        $this->processados[] = $item;
        array_splice($this->itens, $this->itemAtual, 1);

        if (count($this->itens) === 0) {
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
        for ($i = 0; $i < count($this->itens); $i++) {
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

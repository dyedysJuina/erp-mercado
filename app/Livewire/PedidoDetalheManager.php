<?php

namespace App\Livewire;

use App\Models\CompraPedido;
use App\Models\CompraPedidoItem;
use App\Models\CompraRecebimento;
use App\Models\CompraRecebimentoItem;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueMovimentacao;
use App\Models\EstoqueLote;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class PedidoDetalheManager extends Component
{
    public int $pedidoId;
    public string $toastMsg = '';
    public bool $toastShow = false;

    public array $itens = [];

    public function mount(int $id): void
    {
        $this->pedidoId = $id;
        $this->carregarItens();
    }

    public function carregarItens(): void
    {
        $pedido = CompraPedido::with('itens.variacao')->findOrFail($this->pedidoId);
        $this->itens = $pedido->itens->map(fn($i) => [
            'item_id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'quantidade_pedida' => (float)$i->quantidade_pedida,
            'quantidade_recebida' => (float)$i->quantidade_recebida,
            'custo' => (float)$i->custo_unitario,
            'conferido' => true,
            'receber' => max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida),
            'avaria' => 0,
            'nao_veio' => false,
            'motivo' => '',
            'lote' => '',
            'validade' => '',
        ])->toArray();
    }

    public function incrementar(string $campo, int $idx): void
    {
        if (!in_array($campo, ['receber', 'avaria'])) return;
        if (isset($this->itens[$idx])) {
            $pendente = max(0, $this->itens[$idx]['quantidade_pedida'] - $this->itens[$idx]['quantidade_recebida']);
            if ($this->itens[$idx][$campo] < $pendente) {
                $this->itens[$idx][$campo] = min($pendente, $this->itens[$idx][$campo] + 1);
            }
        }
    }

    public function decrementar(string $campo, int $idx): void
    {
        if (!in_array($campo, ['receber', 'avaria'])) return;
        if (isset($this->itens[$idx]) && $this->itens[$idx][$campo] > 0) {
            $this->itens[$idx][$campo] = max(0, $this->itens[$idx][$campo] - 1);
        }
    }

    public function naoVeioToggle(int $idx): void
    {
        if (isset($this->itens[$idx])) {
            $this->itens[$idx]['nao_veio'] = !$this->itens[$idx]['nao_veio'];
            if ($this->itens[$idx]['nao_veio']) {
                $this->itens[$idx]['receber'] = 0;
                $this->itens[$idx]['conferido'] = true;
            }
        }
    }

    #[Computed]
    public function pedido(): ?CompraPedido
    {
        return CompraPedido::with('fornecedor')->find($this->pedidoId);
    }

    #[Computed]
    public function totalItens(): int { return count($this->itens); }

    #[Computed]
    public function itensConferidos(): int
    {
        return count(array_filter($this->itens, fn($i) => $i['conferido']));
    }

    #[Computed]
    public function itensPendentes(): int
    {
        return count(array_filter($this->itens, fn($i) => !$i['conferido']));
    }

    public function receberTodos(): void
    {
        foreach ($this->itens as &$i) {
            $i['receber'] = max(0, $i['quantidade_pedida'] - $i['quantidade_recebida']);
            $i['avaria'] = 0;
            $i['conferido'] = true;
        }
        unset($i);
        $this->toast('Quantidades preenchidas conforme pedido.');
    }

    public function zerarNaoConferidos(): void
    {
        foreach ($this->itens as &$i) {
            if (!$i['conferido']) {
                $i['receber'] = 0;
            }
        }
        unset($i);
    }

    public function confirmarRecebimento(): void
    {
        $pedido = $this->pedido;
        if (!$pedido) return;

        $itensParaReceber = array_filter($this->itens, fn($i) => $i['conferido'] && (float)$i['receber'] > 0);

        if (empty($itensParaReceber)) {
            $this->toast('Selecione ao menos um item com quantidade para receber.');
            return;
        }

        DB::transaction(function () use ($pedido, $itensParaReceber) {
            $rec = CompraRecebimento::create([
                'compra_pedido_id' => $pedido->id,
                'loja_id' => $pedido->loja_id,
                'fornecedor_id' => $pedido->fornecedor_id,
                'usuario_id' => auth()->id(),
                'status' => 'conferido',
            ]);

            foreach ($itensParaReceber as $r) {
                $qtdReceber = (float)$r['receber'];
                $qtdAvaria = (float)$r['avaria'];
                $qtdEfetiva = $qtdReceber - $qtdAvaria;
                if ($qtdEfetiva <= 0 && $qtdAvaria <= 0) continue;

                if ($qtdEfetiva > 0) {
                    CompraRecebimentoItem::create([
                        'recebimento_id' => $rec->id,
                        'produto_variacao_id' => (int)$r['variacao_id'],
                        'quantidade_recebida' => $qtdEfetiva,
                    ]);

                    CompraPedidoItem::find($r['item_id'])?->increment('quantidade_recebida', $qtdEfetiva);

                    EstoqueSaldo::firstOrCreate(
                        ['loja_id' => $pedido->loja_id, 'produto_variacao_id' => (int)$r['variacao_id']],
                        ['quantidade_atual' => 0]
                    )->increment('quantidade_atual', $qtdEfetiva);

                    EstoqueMovimentacao::create([
                        'loja_id' => $pedido->loja_id,
                        'produto_variacao_id' => (int)$r['variacao_id'],
                        'usuario_id' => auth()->id(),
                        'origem_tipo' => 'compra_pedido',
                        'origem_id' => $pedido->id,
                        'tipo' => 'entrada_compra',
                        'quantidade' => $qtdEfetiva,
                        'justificativa' => 'Recebimento pedido #' . $pedido->id,
                    ]);
                }

                if ($qtdAvaria > 0) {
                    EstoqueMovimentacao::create([
                        'loja_id' => $pedido->loja_id,
                        'produto_variacao_id' => (int)$r['variacao_id'],
                        'usuario_id' => auth()->id(),
                        'origem_tipo' => 'compra_pedido',
                        'origem_id' => $pedido->id,
                        'tipo' => 'avaria',
                        'quantidade' => $qtdAvaria,
                        'justificativa' => 'Avaria no recebimento pedido #' . $pedido->id,
                    ]);
                }

                if (!empty(trim($r['lote'] ?? '')) && $qtdEfetiva > 0) {
                    EstoqueLote::create([
                        'loja_id' => $pedido->loja_id,
                        'produto_variacao_id' => (int)$r['variacao_id'],
                        'numero_lote' => trim($r['lote']),
                        'data_validade' => $r['validade'] ?: null,
                        'quantidade_atual' => $qtdEfetiva,
                        'custo_unitario' => (float)$r['custo'],
                    ]);
                }
            }

            $pendentes = CompraPedidoItem::where('compra_pedido_id', $pedido->id)
                ->whereRaw('quantidade_recebida < quantidade_pedida')->count();
            $pedido->update(['status' => $pendentes > 0 ? 'parcialmente_recebido' : 'recebido']);
        });

        $this->carregarItens();
        $this->toast('Recebimento confirmado com sucesso!');
    }

    public function enviar(): void
    {
        $p = $this->pedido;
        if ($p && $p->status === 'rascunho') {
            $p->update(['status' => 'enviado']);
            $this->toast('Pedido marcado como enviado!');
        }
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.pedido-detalhe-manager')
            ->layout('components.layouts.app', ['title' => 'Pedido #' . $this->pedidoId . ' · ERP Mercado']);
    }
}

<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoItem;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class PedidoOnlineDetalheManager extends Component
{
    public int $pedidoId;
    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(int $id): void
    {
        $this->pedidoId = $id;
    }

    #[Computed]
    public function pedido(): ?Pedido
    {
        return Pedido::with(['cliente', 'itens.variacao.unidadeMedida', 'pagamentos.formaPagamento', 'separador', 'entregador'])
            ->find($this->pedidoId);
    }

    public function alterarStatus(string $status): void
    {
        $allowed = ['recebido', 'confirmado', 'em_separacao', 'pronto_retirada', 'pronto_entrega', 'entregue', 'cancelado'];
        if (!in_array($status, $allowed)) return;

        $pedido = Pedido::with('itens')->findOrFail($this->pedidoId);
        $oldStatus = $pedido->status;

        DB::transaction(function () use ($pedido, $status, $oldStatus) {
            $pedido->update(['status' => $status]);

            DB::table('pedidos_status_historico')->insert([
                'pedido_id' => $pedido->id,
                'usuario_id' => auth()->id(),
                'status_anterior' => $oldStatus,
                'status_novo' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Atualiza status dos itens conforme o status do pedido
            if ($status === 'entregue') {
                PedidoItem::where('pedido_id', $pedido->id)
                    ->where('status_item', '!=', 'cancelado')
                    ->update(['status_item' => 'separado']);
            } elseif (in_array($status, ['confirmado', 'em_separacao', 'pronto_retirada', 'pronto_entrega'])) {
                PedidoItem::where('pedido_id', $pedido->id)
                    ->where('status_item', 'pendente')
                    ->update(['status_item' => 'separado']);
            }

            if ($status === 'cancelado') {
                foreach ($pedido->itens as $item) {
                    if (in_array($item->status_item, ['pendente', 'separado', 'substituido'])) {
                        $qtd = (float)$item->quantidade_solicitada;
                        \App\Models\EstoqueSaldo::where('loja_id', $pedido->loja_id)
                            ->where('produto_variacao_id', $item->produto_variacao_id)
                            ->increment('quantidade_atual', $qtd);

                        DB::table('estoque_movimentacoes')->insert([
                            'loja_id' => $pedido->loja_id,
                            'produto_variacao_id' => $item->produto_variacao_id,
                            'usuario_id' => auth()->id(),
                            'origem_tipo' => 'pedido',
                            'origem_id' => $pedido->id,
                            'tipo' => 'entrada_compra',
                            'quantidade' => $qtd,
                            'justificativa' => 'Cancelamento pedido #' . $pedido->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            if ($pedido->cliente_id) {
                \App\Models\Notificacao::create([
                    'usuario_id' => null,
                    'canal' => 'sistema',
                    'titulo' => 'Pedido #' . $pedido->id,
                    'mensagem' => 'Seu pedido foi atualizado para: ' . $status,
                    'link' => '/vitrine/pedidos',
                    'status' => 'pendente',
                    'cliente_id' => $pedido->cliente_id,
                ]);
            }
        });

        $this->forgetComputed('pedido');
        $this->toast("Status alterado para '{$status}'!");
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.pedido-online-detalhe-manager')
            ->layout('components.layouts.app', ['title' => 'Pedido Online #' . $this->pedidoId . ' · ERP Mercado']);
    }
}

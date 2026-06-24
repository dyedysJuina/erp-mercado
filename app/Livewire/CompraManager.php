<?php

namespace App\Livewire;

use App\Models\CompraPedido;
use App\Models\CompraPedidoItem;
use App\Models\CompraRecebimento;
use App\Models\CompraRecebimentoItem;
use App\Models\Fornecedor;
use App\Models\Loja;
use App\Models\ProdutoVariacao;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueLote;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CompraManager extends Component
{
    public ?int $pedidoId = null;
    public string $modo = 'create';
    public string $busca = '';
    public string $filtroStatus = '';
    public string $buscaPedidos = '';

    protected $queryString = ['pedidoId'];

    public function mount(): void
    {
        if ($this->pedidoId) {
            $this->selecionarPedido($this->pedidoId);
        }
    }

    public string $toastMsg = '';
    public bool $toastShow = false;

    // Dados da Compra
    public string $fornecedor_id = '';
    public string $loja_id = '';
    public string $condicao_pagamento = '30 dias';
    public string $tipo_frete = 'CIF';
    public string $previsao_entrega = '';
    public string $valor_frete = '0';
    public string $valor_desconto = '0';
    public string $observacoes = '';
    public string $data_pedido = '';
    public string $tipo_pedido = 'normal';

    // Itens
    public array $itens = [];
    public string $buscaVariacao = '';

    // Recebimento
    public bool $mostrarRecebimento = false;
    public string $numero_nota = '';
    public string $chave_nfe = '';
    public array $recebimento = [];

    protected function rules(): array
    {
        return [
            'loja_id' => ['required', 'exists:lojas,id'],
            'fornecedor_id' => ['required', 'exists:fornecedores,id'],
            'previsao_entrega' => ['nullable', 'date'],
            'valor_frete' => ['nullable', 'numeric', 'min:0'],
            'valor_desconto' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    #[Computed]
    public function pedidos(): array
    {
        $q = CompraPedido::with('fornecedor');

        if (strlen(trim($this->buscaPedidos)) >= 2) {
            $q->where(function ($w) {
                $w->where('id', 'like', '%' . $this->buscaPedidos . '%')
                  ->orWhereHas('fornecedor', fn($f) => $f->where('razao_social', 'like', '%' . $this->buscaPedidos . '%'));
            });
        }
        if ($this->filtroStatus) {
            $q->where('status', $this->filtroStatus);
        }

        return $q->orderBy('created_at', 'desc')->get()->toArray();
    }

    #[Computed]
    public function pedidoAtual(): ?array
    {
        if (!$this->pedidoId) return null;
        return CompraPedido::with(['fornecedor:id,razao_social', 'loja:id,nome', 'itens.variacao.marca', 'itens.variacao.unidadeMedida'])
            ->find($this->pedidoId)?->toArray();
    }

    #[Computed]
    public function totalPedido(): float
    {
        $total = 0;
        foreach ($this->itens as $item) {
            $total += ((float)($item['quantidade'] ?? 0)) * ((float)($item['custo'] ?? 0));
        }
        return (float)$total;
    }

    #[Computed]
    public function totalPedidos(): int { return CompraPedido::count(); }

    #[Computed]
    public function valorTotalMes(): float
    {
        return (float) CompraPedido::whereMonth('created_at', now()->month)
            ->sum('total_pedido');
    }

    #[Computed]
    public function pedidosPendentes(): int
    {
        return CompraPedido::whereIn('status', ['rascunho', 'enviado', 'parcialmente_recebido'])->count();
    }

    #[Computed]
    public function pedidosRecebidosMes(): int
    {
        return CompraPedido::where('status', 'recebido')
            ->whereMonth('updated_at', now()->month)->count();
    }

    #[Computed]
    public function resultadosVariacao(): array
    {
        if (strlen(trim($this->buscaVariacao)) < 2) return [];
        return ProdutoVariacao::where('nome_completo', 'like', '%' . $this->buscaVariacao . '%')
            ->with('marca:id,nome')
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function adicionarItem(int $variacaoId): void
    {
        $v = ProdutoVariacao::with('marca:id,nome')->find($variacaoId);
        if (!$v) return;
        $this->itens[] = [
            'variacao_id' => $v->id,
            'nome' => $v->nome_completo,
            'sku' => $v->sku ?? '',
            'marca' => $v->marca->nome ?? '',
            'unidade' => optional($v->unidadeMedida)->sigla ?? 'UN',
            'quantidade' => 1,
            'custo' => 0,
            'desconto' => 0,
        ];
        $this->buscaVariacao = '';
    }

    public function removerItem(int $idx): void
    {
        if (isset($this->itens[$idx])) {
            unset($this->itens[$idx]);
            $this->itens = array_values($this->itens);
        }
    }

    public function selecionarPedido(int $id): void
    {
        $this->pedidoId = $id;
        $this->modo = 'edit';
        $this->mostrarRecebimento = false;

        $p = CompraPedido::with('itens')->findOrFail($id);
        $this->loja_id = (string)$p->loja_id;
        $this->fornecedor_id = (string)$p->fornecedor_id;
        $this->previsao_entrega = $p->previsao_entrega ?? '';
        $this->valor_frete = (string)$p->valor_frete;
        $this->valor_desconto = (string)$p->valor_desconto;
        $this->observacoes = $p->observacoes ?? '';
        $this->condicao_pagamento = $p->condicao_pagamento ?? '30 dias';
        $this->tipo_frete = $p->tipo_frete ?? 'CIF';
        $this->tipo_pedido = $p->tipo_pedido ?? 'normal';
        $this->data_pedido = $p->data_pedido ?? '';

        $this->itens = $p->itens->map(fn($i) => [
            'id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'sku' => $i->variacao?->sku ?? '',
            'marca' => $i->variacao?->marca?->nome ?? '',
            'unidade' => optional($i->variacao?->unidadeMedida)->sigla ?? 'UN',
            'quantidade' => (float)$i->quantidade_pedida,
            'custo' => (float)$i->custo_unitario,
            'desconto' => 0,
            'recebido' => (float)$i->quantidade_recebida,
        ])->toArray();
    }

    public function novoPedido(): void
    {
        $this->pedidoId = null;
        $this->modo = 'create';
        $this->loja_id = '';
        $this->fornecedor_id = '';
        $this->previsao_entrega = '';
        $this->valor_frete = '0';
        $this->valor_desconto = '0';
        $this->observacoes = '';
        $this->condicao_pagamento = '30 dias';
        $this->tipo_frete = 'CIF';
        $this->tipo_pedido = 'normal';
        $this->data_pedido = '';
        $this->itens = [];
        $this->mostrarRecebimento = false;
        $this->resetErrorBag();
    }

    public function salvar(): void
    {
        $this->validate();

        if (empty($this->itens)) {
            $this->addError('itens', 'Adicione pelo menos um item ao pedido.');
            return;
        }

        $totalProdutos = 0;
        foreach ($this->itens as $item) {
            $totalProdutos += ((float)($item['quantidade'] ?? 0)) * ((float)($item['custo'] ?? 0));
        }
        $frete = (float)$this->valor_frete;
        $desconto = (float)$this->valor_desconto;
        $totalPedido = $totalProdutos + $frete - $desconto;

        $data = [
            'loja_id' => (int)$this->loja_id,
            'fornecedor_id' => (int)$this->fornecedor_id,
            'usuario_id' => auth()->id(),
            'status' => 'rascunho',
            'total_produtos' => $totalProdutos,
            'valor_frete' => $frete,
            'valor_desconto' => $desconto,
            'total_pedido' => $totalPedido,
            'previsao_entrega' => $this->previsao_entrega ?: null,
            'observacoes' => $this->observacoes ?: null,
            'condicao_pagamento' => $this->condicao_pagamento ?: null,
            'tipo_frete' => $this->tipo_frete ?: null,
            'tipo_pedido' => $this->tipo_pedido ?: null,
            'data_pedido' => $this->data_pedido ?: null,
        ];

        DB::transaction(function () use ($data) {
            if ($this->modo === 'create') {
                $pedido = CompraPedido::create($data);
                $msg = 'criado';
            } else {
                $pedido = CompraPedido::findOrFail($this->pedidoId);
                $pedido->update($data);
                CompraPedidoItem::where('compra_pedido_id', $pedido->id)->delete();
                $msg = 'atualizado';
            }

            foreach ($this->itens as $item) {
                $qtd = (float)$item['quantidade'];
                $custo = (float)$item['custo'];
                CompraPedidoItem::create([
                    'compra_pedido_id' => $pedido->id,
                    'produto_variacao_id' => (int)$item['variacao_id'],
                    'quantidade_pedida' => $qtd,
                    'quantidade_recebida' => 0,
                    'custo_unitario' => $custo,
                    'total_item' => $qtd * $custo,
                ]);
            }

            $this->pedidoId = $pedido->id;
            $this->modo = 'edit';
            $this->toast("Pedido #{$pedido->id} {$msg} com sucesso!");
        });
    }

    public function enviarPedido(int $id): void
    {
        CompraPedido::findOrFail($id)->update(['status' => 'enviado']);
        $this->selecionarPedido($id);
        $this->toast('Pedido enviado para o fornecedor!');
    }

    public function cancelarPedido(int $id): void
    {
        CompraPedido::findOrFail($id)->update(['status' => 'cancelado']);
        $this->selecionarPedido($id);
        $this->toast('Pedido cancelado.');
    }

    public function prepararRecebimento(): void
    {
        $this->mostrarRecebimento = true;
        $this->numero_nota = '';
        $this->chave_nfe = '';
        $pedido = CompraPedido::with('itens.variacao.marca')->findOrFail($this->pedidoId);
        $this->recebimento = $pedido->itens->map(fn($i) => [
            'item_id' => $i->id, 'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'pedido' => (float)$i->quantidade_pedida, 'recebido' => (float)$i->quantidade_recebida,
            'pendente' => max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida),
            'receber' => max(0, (float)$i->quantidade_pedida - (float)$i->quantidade_recebida),
            'custo' => (float)$i->custo_unitario, 'lote' => '', 'validade' => '',
        ])->toArray();
    }

    public function confirmarRecebimento(): void
    {
        if (empty($this->recebimento) || !$this->pedidoId) return;
        $pedido = CompraPedido::findOrFail($this->pedidoId);

        DB::transaction(function () use ($pedido) {
            $rec = CompraRecebimento::create([
                'compra_pedido_id' => $pedido->id, 'loja_id' => $pedido->loja_id,
                'fornecedor_id' => $pedido->fornecedor_id, 'usuario_id' => auth()->id(),
                'numero_nota' => $this->numero_nota ?: null, 'chave_nfe' => $this->chave_nfe ?: null,
                'status' => 'conferido',
            ]);
            foreach ($this->recebimento as $r) {
                $qtd = (float)$r['receber'];
                if ($qtd <= 0) continue;
                $custo = (float)$r['custo'];
                CompraRecebimentoItem::create([
                    'recebimento_id' => $rec->id, 'produto_variacao_id' => (int)$r['variacao_id'],
                    'quantidade_recebida' => $qtd, 'custo_unitario' => $custo,
                ]);
                CompraPedidoItem::find($r['item_id'])?->increment('quantidade_recebida', $qtd);
                $saldo = EstoqueSaldo::firstOrCreate(
                    ['loja_id' => $pedido->loja_id, 'produto_variacao_id' => (int)$r['variacao_id']],
                    ['quantidade_atual' => 0]
                );
                $saldo->increment('quantidade_atual', $qtd);
                if (!empty($r['lote'])) {
                    EstoqueLote::create([
                        'loja_id' => $pedido->loja_id, 'produto_variacao_id' => (int)$r['variacao_id'],
                        'numero_lote' => $r['lote'], 'data_validade' => $r['validade'] ?: null,
                        'quantidade_atual' => $qtd, 'custo_unitario' => $custo,
                    ]);
                }
            }
            $pendentes = CompraPedidoItem::where('compra_pedido_id', $pedido->id)
                ->whereRaw('quantidade_recebida < quantidade_pedida')->count();
            $pedido->update(['status' => $pendentes > 0 ? 'parcialmente_recebido' : 'recebido']);
        });
        $this->mostrarRecebimento = false;
        $this->selecionarPedido($this->pedidoId);
        $this->toast('Recebimento confirmado!');
    }

    public function excluir(int $id): void
    {
        $temRecebimento = CompraRecebimento::where('compra_pedido_id', $id)->exists();
        if ($temRecebimento) {
            $this->addError('pedido', 'Não é possível excluir: pedido já possui recebimento(s).');
            return;
        }
        $p = CompraPedido::findOrFail($id);
        CompraPedidoItem::where('compra_pedido_id', $id)->delete();
        $p->delete();
        $this->novoPedido();
        $this->toast('Pedido excluído.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.compra-manager')
            ->layout('components.layouts.app', ['title' => 'Compras · ERP Mercado']);
    }
}

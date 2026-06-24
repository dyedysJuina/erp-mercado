<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\ProdutoVariacao;
use App\Models\EstoqueSaldo;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\FinanceiroLancamento;
use App\Models\Notificacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class StorefrontManager extends Component
{
    public string $busca = '';
    public string $clienteNome = '';
    public string $clienteWhatsApp = '';

    // Carrinho em Alpine, não no Livewire
    public ?int $ultimoPedidoId = null;

    #[Computed]
    public function lojaId(): int
    {
        return Loja::where('ativo', true)
            ->whereNotNull('tabela_preco_id')
            ->value('id') ?? Loja::where('ativo', true)->value('id') ?? 1;
    }

    #[Computed]
    public function tabelaId(): ?int
    {
        return Loja::where('id', $this->lojaId)->value('tabela_preco_id');
    }

    #[Computed]
    public function produtos(): array
    {
        $tabelaId = $this->tabelaId;
        if (!$tabelaId) return [];

        $q = ProdutoVariacao::with(['marca:id,nome', 'unidadeMedida:id,sigla'])
            ->join('tabela_precos_itens as price', function ($j) use ($tabelaId) {
                $j->on('price.produto_variacao_id', '=', 'produto_variacoes.id')
                  ->where('price.tabela_preco_id', '=', $tabelaId);
            })
            ->leftJoin('estoque_saldos as stock', function ($j) {
                $j->on('stock.produto_variacao_id', '=', 'produto_variacoes.id')
                  ->where('stock.loja_id', '=', $this->lojaId);
            })
            ->where('produto_variacoes.ativo', true)
            ->where('price.preco_venda', '>', 0);

        if (strlen(trim($this->busca)) >= 2) {
            $q->where('produto_variacoes.nome_completo', 'like', '%' . $this->busca . '%');
        }

        return $q->limit(30)->get([
            'produto_variacoes.id',
            'produto_variacoes.nome_completo',
            'price.preco_venda',
            DB::raw('COALESCE(stock.quantidade_atual, 0) as estoque'),
        ])->toArray();
    }

    #[Computed]
    public function clientePadrao(): int
    {
        $nome = trim($this->clienteNome);
        if (!$nome) return 1;

        $cliente = DB::table('clientes')->where('nome', $nome)->first();
        if ($cliente) return (int)$cliente->id;

        return DB::table('clientes')->insertGetId([
            'nome' => $nome,
            'whatsapp' => trim($this->clienteWhatsApp) ?: null,
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function finalizar(array $carrinho): ?int
    {
        if (empty($carrinho)) return null;
        if (!session('cliente_id')) return null;
        if (strlen(trim($this->clienteNome)) < 2) return null;

        $total = 0;
        $itensValidos = [];

        foreach ($carrinho as $item) {
            $v = ProdutoVariacao::find($item['id']);
            if (!$v || !$v->ativo) continue;

            $preco = DB::table('tabela_precos_itens')
                ->where('tabela_preco_id', $this->tabelaId)
                ->where('produto_variacao_id', $item['id'])
                ->value('preco_venda');

            if (!$preco || $preco <= 0) continue;

            $qtd = max(0.001, (float)$item['quantidade']);
            $totalItem = $qtd * (float)$preco;
            $total += $totalItem;

            $itensValidos[] = [
                'variacao_id' => (int)$item['id'],
                'nome' => $v->nome_completo,
                'quantidade' => $qtd,
                'preco' => (float)$preco,
                'total' => $totalItem,
            ];
        }

        if (empty($itensValidos)) return null;

        $clienteId = (int) session('cliente_id');

        $pedido = null;

        DB::transaction(function () use ($itensValidos, $total, $clienteId, &$pedido) {
            $codigo = 'PED-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(6));

            $pedido = DB::table('pedidos')->insertGetId([
                'loja_id' => $this->lojaId,
                'cliente_id' => $clienteId,
                'codigo' => $codigo,
                'origem' => 'site',
                'tipo_entrega' => 'retirada',
                'status' => 'recebido',
                'subtotal' => $total,
                'desconto' => 0,
                'taxa_entrega' => 0,
                'total' => $total,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($itensValidos as $item) {
                DB::table('pedidos_itens')->insert([
                    'pedido_id' => $pedido,
                    'produto_variacao_id' => $item['variacao_id'],
                    'quantidade_solicitada' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'total_item' => $item['total'],
                    'status_item' => 'pendente',
                ]);

                // Baixa estoque
                $saldo = EstoqueSaldo::where('loja_id', $this->lojaId)
                    ->where('produto_variacao_id', $item['variacao_id'])
                    ->first();
                if ($saldo) {
                    $saldo->decrement('quantidade_atual', $item['quantidade']);
                }
            }

            // Gera financeiro (receita pendente)
            FinanceiroLancamento::create([
                'empresa_id' => 1,
                'loja_id' => $this->lojaId,
                'pdv_venda_id' => null,
                'pedido_id' => $pedido,
                'tipo' => 'receita',
                'descricao' => "Pedido #{$pedido} - {$this->clienteNome}",
                'valor' => $total,
                'data_competencia' => now(),
                'data_vencimento' => now()->addDays(7),
                'status' => 'pendente',
            ]);
        });

        // Notificar administradores sobre novo pedido
        $admins = \App\Models\User::where('ativo', true)->whereHas('roles', fn($q) => $q->where('name', 'Admin'))->pluck('id');
        foreach ($admins as $uid) {
            Notificacao::create([
                'usuario_id' => $uid,
                'canal' => 'sistema',
                'titulo' => 'Novo Pedido Online',
                'mensagem' => "Pedido #{$pedido} de {$this->clienteNome} - R$ " . number_format($total, 2, ',', '.'),
                'link' => '/pedidos-online',
                'status' => 'pendente',
            ]);
        }

        $this->ultimoPedidoId = $pedido;
        $this->clienteNome = '';
        $this->clienteWhatsApp = '';

        return $pedido;
    }

    public function render()
    {
        return view('livewire.storefront-manager')
            ->layout('layouts.storefront');
    }
}

<?php

namespace App\Livewire;

use App\Models\CompraPedido;
use App\Models\CompraRecebimento;
use App\Models\CompraRecebimentoItem;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueMovimentacao;
use App\Models\FiscalDocumento;
use App\Models\Fornecedor;
use App\Models\Loja;
use App\Models\ProdutoVariacao;
use App\Models\TabelaPrecoItem;
use App\Models\User;
use App\Models\Notificacao;
use App\Support\BrazilianNumber;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NfeEntradaManager extends Component
{
    public string $aba = 'registrar';
    public string $busca = '';

    // Form
    public string $chave_nfe = '';
    public string $numero_nota = '';
    public string $fornecedor_busca = '';
    public ?int $fornecedor_id = null;
    public string $fornecedor_nome = '';
    public string $loja_id = '';
    public string $data_emissao = '';
    public string $data_recebimento = '';

    // Products
    public array $itens = [];
    public string $buscaProduto = '';
    public array $resultadosBusca = [];
    public ?int $compra_pedido_id = null;

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(): void
    {
        $this->data_recebimento = now()->format('Y-m-d');
        $this->data_emissao = now()->format('Y-m-d');
    }

    #[Computed]
    public function lojas(): array
    {
        return Loja::where('ativo', true)->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function buscarFornecedor(): void
    {
        $q = trim($this->fornecedor_busca);
        if (strlen($q) < 2) return;
        $this->fornecedor_id = null;
        $this->fornecedor_nome = '';

        $forn = Fornecedor::where('cnpj_cpf', preg_replace('/\D/', '', $q))
            ->orWhere('razao_social', 'like', "%{$q}%")
            ->first();
        if ($forn) {
            $this->fornecedor_id = $forn->id;
            $this->fornecedor_nome = $forn->razao_social;
            $this->fornecedor_busca = '';
        }
    }

    public function limparFornecedor(): void
    {
        $this->fornecedor_id = null;
        $this->fornecedor_nome = '';
        $this->fornecedor_busca = '';
    }

    public function buscarProduto(): void
    {
        $q = trim($this->buscaProduto);
        if (strlen($q) < 2) { $this->resultadosBusca = []; return; }
        $this->resultadosBusca = ProdutoVariacao::where('ativo', true)
            ->where(function ($w) use ($q) {
                $w->where('nome_completo', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            })
            ->with('marca', 'unidadeMedida')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function adicionarItem(int $variacaoId): void
    {
        $v = ProdutoVariacao::with('marca', 'unidadeMedida')->find($variacaoId);
        if (!$v) return;
        $this->itens[] = [
            'variacao_id' => $v->id,
            'nome' => $v->nome_completo,
            'sku' => $v->sku ?? '',
            'unid' => $v->unidadeMedida?->sigla ?? 'UN',
            'quantidade' => 1,
            'custo' => 0,
            'total' => 0,
        ];
        $this->buscaProduto = '';
        $this->resultadosBusca = [];
    }

    public function removerItem(int $idx): void
    {
        if (isset($this->itens[$idx])) {
            unset($this->itens[$idx]);
            $this->itens = array_values($this->itens);
        }
    }

    public function updatedItens(): void
    {
        foreach ($this->itens as $i => &$item) {
            $qtd = (float)$item['quantidade'];
            $custo = (float)$item['custo'];
            $item['total'] = round($qtd * $custo, 2);
        }
    }

    public function registrar(): void
    {
        $this->validate([
            'loja_id' => ['required'],
            'fornecedor_id' => ['required'],
            'itens' => ['required', 'array', 'min:1'],
        ]);

        if (empty($this->itens)) { $this->addError('itens', 'Adicione pelo menos um produto.'); return; }

        DB::transaction(function () {
            $lojaId = (int)$this->loja_id;

            // 1. Create FiscalDocumento
            $doc = FiscalDocumento::create([
                'loja_id' => $lojaId,
                'fornecedor_id' => $this->fornecedor_id,
                'tipo' => 'nfe_entrada',
                'modelo' => '55',
                'serie' => '0',
                'numero' => $this->numero_nota ?: '0',
                'chave_acesso' => $this->chave_nfe ?: null,
                'status' => 'rascunho',
                'valor_total' => collect($this->itens)->sum('total'),
                'emitida_at' => $this->data_emissao ? \Carbon\Carbon::parse($this->data_emissao) : now(),
            ]);

            // 2. Create CompraRecebimento
            $rec = CompraRecebimento::create([
                'compra_pedido_id' => $this->compra_pedido_id,
                'loja_id' => $lojaId,
                'fornecedor_id' => $this->fornecedor_id,
                'usuario_id' => auth()->id(),
                'numero_nota' => $this->numero_nota ?: null,
                'chave_nfe' => $this->chave_nfe ?: null,
                'status' => 'conferido',
            ]);

            // 3. Process each item
            foreach ($this->itens as $item) {
                $qtd = (float)$item['quantidade'];
                $custo = (float)$item['custo'];
                $variacaoId = (int)$item['variacao_id'];

                CompraRecebimentoItem::create([
                    'recebimento_id' => $rec->id,
                    'produto_variacao_id' => $variacaoId,
                    'quantidade_recebida' => $qtd,
                    'custo_unitario' => $custo,
                ]);

                // Update/create estoque_saldos
                EstoqueSaldo::updateOrCreate(
                    ['loja_id' => $lojaId, 'produto_variacao_id' => $variacaoId],
                    ['quantidade_atual' => DB::raw('COALESCE(quantidade_atual, 0) + ' . $qtd)]
                );

                // Record movement
                EstoqueMovimentacao::create([
                    'loja_id' => $lojaId,
                    'produto_variacao_id' => $variacaoId,
                    'usuario_id' => auth()->id(),
                    'origem_tipo' => 'nfe_entrada',
                    'origem_id' => $rec->id,
                    'tipo' => 'entrada_compra',
                    'quantidade' => $qtd,
                    'custo_unitario' => $custo,
                    'justificativa' => "NF-e Entrada #{$doc->id}",
                ]);

                // Update cost price in all price tables for this store
                if ($custo > 0) {
                    $tabelaPrecoId = Loja::where('id', $lojaId)->value('tabela_preco_id');
                    if ($tabelaPrecoId) {
                        TabelaPrecoItem::updateOrCreate(
                            ['tabela_preco_id' => $tabelaPrecoId, 'produto_variacao_id' => $variacaoId],
                            ['preco_custo' => $custo]
                        );
                    }
                }
            }

            // 4. Create financial entry (contas a pagar)
            if ($this->data_recebimento) {
                $totalNota = collect($this->itens)->sum('total');
                $empresaId = Loja::where('id', $lojaId)->value('empresa_id') ?? 1;
                DB::table('financeiro_lancamentos')->insert([
                    'empresa_id' => $empresaId,
                    'loja_id' => $lojaId,
                    'fornecedor_id' => $this->fornecedor_id,
                    'compra_pedido_id' => $this->compra_pedido_id,
                    'tipo' => 'despesa',
                    'descricao' => "NF-e Entrada #{$doc->id} - {$this->fornecedor_nome}",
                    'valor' => $totalNota,
                    'data_competencia' => $this->data_emissao ?: $this->data_recebimento,
                    'data_vencimento' => $this->data_recebimento,
                    'status' => 'pendente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        // Notificar admins sobre entrada de mercadoria
        $admins = User::where('ativo', true)->whereHas('roles', fn($q) => $q->where('name', 'Admin'))->pluck('id');
        $totalItens = count($this->itens);
        foreach ($admins as $uid) {
            Notificacao::create([
                'usuario_id' => $uid,
                'canal' => 'sistema',
                'titulo' => 'Entrada de Mercadoria',
                'mensagem' => "NF-e de {$this->fornecedor_nome} registrada - {$totalItens} produto(s) no estoque",
                'link' => '/nfe-entrada',
                'status' => 'pendente',
            ]);
        }

        $this->limpar();
        $this->toast('NF-e de entrada registrada com sucesso! Estoque atualizado.');
    }

    // List registered NF-es
    public function listagem()
    {
        return FiscalDocumento::where('tipo', 'nfe_entrada')
            ->with('fornecedor', 'loja')
            ->orderBy('created_at', 'desc')
            ->paginate(25);
    }

    public function limpar(): void
    {
        $this->chave_nfe = '';
        $this->numero_nota = '';
        $this->itens = [];
        $this->fornecedor_id = null;
        $this->fornecedor_nome = '';
        $this->compra_pedido_id = null;
        $this->resetErrorBag();
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.nfe-entrada-manager')
            ->layout('components.layouts.app', ['title' => 'NF-e de Entrada · ERP Mercado']);
    }
}

<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\EstoqueSaldo;
use App\Models\EstoqueMovimentacao;
use App\Models\ProdutoVariacao;
use App\Models\Ncm;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class EstoqueManager extends Component
{
    use WithPagination;
    public string $loja_id = '2';
    public string $busca = '';
    public string $buscaVariacao = '';
    public string $filtroStatus = 'todos';

    // Modal movimentação
    public bool $modalOpen = false;
    public string $movVariacaoId = '';
    public string $movVariacaoNome = '';
    public string $movTipo = 'entrada_compra';
    public string $movQuantidade = '1';
    public string $movCusto = '';
    public string $movJustificativa = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        return [
            'movVariacaoId' => ['required', 'exists:produto_variacoes,id'],
            'movTipo' => ['required'],
            'movQuantidade' => ['required', 'numeric', 'min:0'],
            'movJustificativa' => ['nullable', 'string', 'max:255'],
        ];
    }

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function tiposMov(): array
    {
        return [
            'entrada_compra' => 'Entrada - Compra',
            'ajuste' => 'Correção (Balanço)',
            'perda' => 'Perda',
            'avaria' => 'Avaria',
            'vencimento' => 'Vencimento',
            'saida_venda_pdv' => 'Saída - Venda PDV',
            'transferencia_entrada' => 'Transferência (entrada)',
            'transferencia_saida' => 'Transferência (saída)',
        ];
    }

    #[Computed]
    public function saldos(): array
    {
        if (!$this->loja_id) return [];

        $q = EstoqueSaldo::where('estoque_saldos.loja_id', (int)$this->loja_id)
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_saldos.produto_variacao_id')
            ->leftJoin('marcas', 'marcas.id', '=', 'produto_variacoes.marca_id')
            ->leftJoin('unidades_medida', 'unidades_medida.id', '=', 'produto_variacoes.unidade_medida_id')
            ->leftJoin('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->leftJoin('tabela_precos_itens as precos', function ($join) {
                $join->on('precos.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('precos.tabela_preco_id', function ($q) {
                        $q->select('tabela_preco_id')->from('lojas')->where('id', (int)$this->loja_id)->limit(1);
                    });
            })
            ->select([
                'estoque_saldos.*',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'produto_variacoes.ncm_id',
                'marcas.nome as marca_nome',
                'unidades_medida.sigla as unid_sigla',
                'categorias.nome as cat_nome',
                DB::raw('COALESCE(precos.preco_custo, 0) as preco_custo'),
            ])
            ->orderBy('estoque_saldos.quantidade_atual', 'desc');

        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('produto_variacoes.nome_completo', 'like', '%' . $this->busca . '%')
                  ->orWhere('produto_variacoes.sku', 'like', '%' . $this->busca . '%');
            });
        }

        if ($this->filtroStatus === 'baixo') {
            $q->where('estoque_saldos.quantidade_atual', '>', 0)
              ->whereColumn('estoque_saldos.quantidade_atual', '<=', 'estoque_saldos.estoque_minimo');
        } elseif ($this->filtroStatus === 'zerado') {
            $q->where('estoque_saldos.quantidade_atual', '<=', 0);
        }

        $result = $q->get()->toArray();

        // Adiciona código de barras + NCM
        foreach ($result as &$item) {
            $item['codigo_barras'] = DB::table('produto_codigos_barras')
                ->where('produto_variacao_id', $item['produto_variacao_id'])
                ->where('principal', true)->value('codigo') ?? '—';

            $item['ncm_codigo'] = '—';
            if ($item['ncm_id']) {
                $n = Ncm::find($item['ncm_id']);
                $item['ncm_codigo'] = $n?->codigo ?? '—';
            }
        }

        return $result;
    }

    public function saldosTable()
    {
        if (!$this->loja_id) return collect([]);

        $q = EstoqueSaldo::where('estoque_saldos.loja_id', (int)$this->loja_id)
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_saldos.produto_variacao_id')
            ->leftJoin('marcas', 'marcas.id', '=', 'produto_variacoes.marca_id')
            ->leftJoin('unidades_medida', 'unidades_medida.id', '=', 'produto_variacoes.unidade_medida_id')
            ->leftJoin('produtos_base', 'produtos_base.id', '=', 'produto_variacoes.produto_base_id')
            ->leftJoin('categorias', 'categorias.id', '=', 'produtos_base.categoria_id')
            ->leftJoin('tabela_precos_itens as precos', function ($join) {
                $join->on('precos.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('precos.tabela_preco_id', function ($q) {
                        $q->select('tabela_preco_id')->from('lojas')->where('id', (int)$this->loja_id)->limit(1);
                    });
            })
            ->select([
                'estoque_saldos.*',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'produto_variacoes.ncm_id',
                'marcas.nome as marca_nome',
                'unidades_medida.sigla as unid_sigla',
                'categorias.nome as cat_nome',
                DB::raw('COALESCE(precos.preco_custo, 0) as preco_custo'),
            ])
            ->orderBy('estoque_saldos.quantidade_atual', 'desc');

        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('produto_variacoes.nome_completo', 'like', '%' . $this->busca . '%')
                  ->orWhere('produto_variacoes.sku', 'like', '%' . $this->busca . '%');
            });
        }

        if ($this->filtroStatus === 'baixo') {
            $q->where('estoque_saldos.quantidade_atual', '>', 0)
              ->whereColumn('estoque_saldos.quantidade_atual', '<=', 'estoque_saldos.estoque_minimo');
        } elseif ($this->filtroStatus === 'zerado') {
            $q->where('estoque_saldos.quantidade_atual', '<=', 0);
        }

        return $q->paginate(25)->through(function ($item) {
            $item->codigo_barras = DB::table('produto_codigos_barras')
                ->where('produto_variacao_id', $item->produto_variacao_id)
                ->where('principal', true)->value('codigo') ?? '—';

            $item->ncm_codigo = '—';
            if ($item->ncm_id) {
                $n = Ncm::find($item->ncm_id);
                $item->ncm_codigo = $n?->codigo ?? '—';
            }
            return $item;
        });
    }

    #[Computed]
    public function custoTotal(): float
    {
        $total = 0;
        foreach ($this->saldos as $s) {
            $total += ($s['quantidade_atual'] ?? 0) * ($s['preco_custo'] ?? 0);
        }
        return $total;
    }

    #[Computed]
    public function unidadesFisicas(): float
    {
        $total = 0;
        foreach ($this->saldos as $s) {
            $total += $s['quantidade_atual'] ?? 0;
        }
        return $total;
    }

    #[Computed]
    public function itensCriticos(): int
    {
        $count = 0;
        foreach ($this->saldos as $s) {
            $qtd = $s['quantidade_atual'] ?? 0;
            $min = $s['estoque_minimo'] ?? 0;
            if ($qtd > 0 && $qtd <= $min) $count++;
        }
        return $count;
    }

    #[Computed]
    public function itensZerados(): int
    {
        $count = 0;
        foreach ($this->saldos as $s) {
            if (($s['quantidade_atual'] ?? 0) <= 0) $count++;
        }
        return $count;
    }

    #[Computed]
    public function movimentacoesRecentes(): array
    {
        if (!$this->loja_id) return [];
        return EstoqueMovimentacao::where('estoque_movimentacoes.loja_id', (int)$this->loja_id)
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_movimentacoes.produto_variacao_id')
            ->leftJoin('users', 'users.id', '=', 'estoque_movimentacoes.usuario_id')
            ->select([
                'estoque_movimentacoes.*',
                'produto_variacoes.nome_completo as prod_nome',
                'users.name as user_nome',
            ])
            ->latest('estoque_movimentacoes.created_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function abrirModal(?int $variacaoId = null): void
    {
        $this->modalOpen = true;
        if ($variacaoId) {
            $v = ProdutoVariacao::find($variacaoId);
            $this->movVariacaoId = (string)$variacaoId;
            $this->movVariacaoNome = $v?->nome_completo ?? '';
        } else {
            $this->movVariacaoId = '';
            $this->movVariacaoNome = '';
        }
        $this->movTipo = 'entrada_compra';
        $this->movQuantidade = '1';
        $this->movCusto = '';
        $this->movJustificativa = '';
        $this->resetErrorBag();
    }

    public function fecharModal(): void
    {
        $this->modalOpen = false;
    }

    public function registrarMovimentacao(): void
    {
        $this->validate();

        $qtd = (float)$this->movQuantidade;
        $tipo = $this->movTipo;

        DB::transaction(function () use ($qtd, $tipo) {
            $saldo = EstoqueSaldo::firstOrCreate(
                ['loja_id' => (int)$this->loja_id, 'produto_variacao_id' => (int)$this->movVariacaoId],
                ['quantidade_atual' => 0, 'quantidade_reservada' => 0]
            );

            if (in_array($tipo, ['entrada_compra', 'transferencia_entrada'])) {
                $saldo->increment('quantidade_atual', $qtd);
            } elseif ($tipo === 'ajuste') {
                $saldo->update(['quantidade_atual' => max(0, $qtd)]);
            } else {
                $updated = DB::table('estoque_saldos')
                    ->where('id', $saldo->id)
                    ->where('quantidade_atual', '>=', $qtd)
                    ->decrement('quantidade_atual', $qtd);
                if (!$updated) {
                    throw new \Exception('Estoque insuficiente para esta movimentação.');
                }
            }

            EstoqueMovimentacao::create([
                'loja_id' => (int)$this->loja_id,
                'produto_variacao_id' => (int)$this->movVariacaoId,
                'tipo' => $tipo,
                'quantidade' => $qtd,
                'custo_unitario' => $this->movCusto ? (float)$this->movCusto : null,
                'justificativa' => $this->movJustificativa ?: null,
                'usuario_id' => auth()->id(),
            ]);
        });

        $this->modalOpen = false;
        $this->toast('Movimentação registrada com sucesso!');
        $this->resetErrorBag();
    }

    public function filtrarPorProduto(string $nome): void
    {
        $this->busca = $nome;
        $this->filtroStatus = 'todos';
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.estoque-manager')
            ->layout('components.layouts.app', ['title' => 'Estoque · ERP Mercado']);
    }
}

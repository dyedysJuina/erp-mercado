<?php

namespace App\Livewire;

use App\Models\PrecoHistorico;
use App\Models\ProdutoVariacao;
use App\Models\TabelaPreco;
use App\Models\TabelaPrecoItem;
use App\Services\PriceTableService;
use App\Support\BrazilianNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PrecoManager extends Component
{
    use WithPagination;
    public ?int $tabelaId = null;
    public string $busca = '';
    public string $buscaProduto = '';
    public string $novaTabelaNome = '';
    public ?int $categoriaFiltro = null;

    public string $loteMargem = '';
    public string $loteAcrescimo = '';
    public string $loteAlvo = 'all';

    public string $mensagem = '';
    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        return ['novaTabelaNome' => ['required', 'string', 'max:150']];
    }

    #[Computed]
    public function listas(): array
    {
        return TabelaPreco::orderBy('nome')->get()->toArray();
    }

    #[Computed]
    public function tabelaAtual(): ?array
    {
        if (!$this->tabelaId) return null;
        return TabelaPreco::with(['lojas:id,nome', 'itens.variacao.marca', 'itens.variacao.unidadeMedida'])
            ->find($this->tabelaId)?->toArray();
    }

    #[Computed]
    public function tabelasAtivas(): int
    {
        return TabelaPreco::where('is_active', true)->count();
    }

    #[Computed]
    public function totalItens(): int
    {
        return TabelaPrecoItem::count();
    }

    #[Computed]
    public function margemMedia(): float
    {
        return (float) TabelaPrecoItem::whereNotNull('margem_percentual')
            ->where('margem_percentual', '>', 0)
            ->avg('margem_percentual');
    }

    public function itensTabela()
    {
        if (!$this->tabelaId) return collect([]);

        $q = TabelaPrecoItem::where('tabela_preco_id', $this->tabelaId)
            ->with('variacao.marca', 'variacao.unidadeMedida')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'tabela_precos_itens.produto_variacao_id')
            ->leftJoin('produto_imagens', function ($join) {
                $join->on('produto_imagens.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('produto_imagens.principal', true);
            })
            ->select(
                'tabela_precos_itens.*',
                'produto_variacoes.nome_completo',
                'produto_variacoes.sku',
                'produto_variacoes.foto_capa_url',
                DB::raw('COALESCE(produto_imagens.url, produto_variacoes.foto_capa_url) as foto_url')
            );

        if (strlen(trim($this->buscaProduto)) >= 2) {
            $q->where(function ($w) {
                $w->where('produto_variacoes.nome_completo', 'like', '%' . $this->buscaProduto . '%')
                  ->orWhere('produto_variacoes.sku', 'like', '%' . $this->buscaProduto . '%');
            });
        }

        return $q->orderBy('produto_variacoes.nome_completo')->paginate(25);
    }

    public function selecionar(int $id): void
    {
        $this->tabelaId = $id;
        $this->buscaProduto = '';
        $this->mensagem = '';
    }

    public function criarTabela(PriceTableService $service): void
    {
        $this->validate();
        $t = TabelaPreco::create(['nome' => $this->novaTabelaNome, 'is_active' => true]);
        $service->duplicatePrices(0, $t->id);
        $this->novaTabelaNome = '';
        $this->selecionar($t->id);
        $this->toast('Tabela criada com sucesso!');
    }

    public function duplicarTabela(int $id): void
    {
        $orig = TabelaPreco::findOrFail($id);
        $nova = TabelaPreco::create([
            'nome' => $orig->nome . ' (cópia)',
            'is_active' => true,
        ]);
        app(PriceTableService::class)->duplicatePrices($id, $nova->id);
        $this->toast('Tabela duplicada!');
    }

    public function atualizarPreco(int $itemId, string $field, mixed $valor): void
    {
        if (!in_array($field, ['preco_custo', 'margem_percentual', 'preco_venda', 'preco_atacado'], true)) return;

        $item = TabelaPrecoItem::findOrFail($itemId);
        try {
            $valor = (float) BrazilianNumber::decimal($valor, $field === 'margem_percentual' ? 4 : 2);
        } catch (InvalidArgumentException) {
            $this->toast('Valor inválido.');
            return;
        }

        if ($field === 'margem_percentual' && ($valor < -9999 || $valor > 9999)) {
            $this->toast('Margem deve ficar entre -9999% e 9999%.');
            return;
        }
        if (in_array($field, ['preco_custo', 'preco_venda', 'preco_atacado']) && ($valor < 0 || $valor > 99999999)) {
            $this->toast('Valor deve ficar entre R$ 0 e R$ 99.999.999.');
            return;
        }

        $oldPrecoVenda = $item->preco_venda;

        if ($field === 'preco_custo' && $valor > 0 && $item->preco_venda > 0) {
            $margem = round(($item->preco_venda - $valor) / $item->preco_venda * 100, 1);
            $item->update(['preco_custo' => $valor, 'margem_percentual' => $margem]);
        } elseif ($field === 'margem_percentual' && $item->preco_custo > 0) {
            $precoVenda = round($item->preco_custo * (1 + $valor / 100), 2);
            $item->update(['margem_percentual' => $valor, 'preco_venda' => $precoVenda]);
        } elseif ($field === 'preco_venda') {
            $margem = $item->preco_custo > 0 ? round(($valor - $item->preco_custo) / $valor * 100, 1) : $item->margem_percentual;
            $item->update(['preco_venda' => $valor, 'margem_percentual' => $margem]);
        } else {
            $item->update([$field => $valor]);
        }

        $novoPrecoVenda = $item->fresh()->preco_venda;
        if ($novoPrecoVenda != $oldPrecoVenda) {
            $this->registrarHistorico($item->produto_variacao_id, $oldPrecoVenda, $novoPrecoVenda);
        }

        $this->toast('Preço atualizado!');
    }

    private function registrarHistorico(int $variacaoId, float $antigo, float $novo): void
    {
        $lojas = \App\Models\Loja::where('tabela_preco_id', $this->tabelaId)->where('ativo', true)->pluck('id');
        foreach ($lojas as $lojaId) {
            PrecoHistorico::create([
                'loja_id' => $lojaId,
                'produto_variacao_id' => $variacaoId,
                'usuario_id' => auth()->id(),
                'preco_anterior' => $antigo,
                'preco_novo' => $novo,
                'motivo' => 'Ajuste manual',
            ]);
        }
    }

    public function aplicarEmLote(): void
    {
        if (!$this->tabelaId) return;
        DB::transaction(function () {
            $itens = TabelaPrecoItem::where('tabela_preco_id', $this->tabelaId)->with('variacao')->lockForUpdate()->get();
            $margem = (float)$this->loteMargem;
            $acrescimo = (float)$this->loteAcrescimo;
            $count = 0;

            foreach ($itens as $item) {
                $oldPrecoVenda = $item->preco_venda;
                if ($margem > 0 && $item->preco_custo > 0) {
                    $item->update([
                        'margem_percentual' => $margem,
                        'preco_venda' => round($item->preco_custo * (1 + $margem / 100), 2),
                    ]);
                    $count++;
                }
                if ($acrescimo > 0) {
                    $item->increment('preco_custo', $acrescimo);
                    if ($item->margem_percentual > 0) {
                        $item->update([
                            'preco_venda' => round($item->preco_custo * (1 + $item->margem_percentual / 100), 2),
                        ]);
                    }
                    $count++;
                }
                $novoPrecoVenda = $item->fresh()->preco_venda;
                if ($novoPrecoVenda != $oldPrecoVenda) {
                    $this->registrarHistorico($item->produto_variacao_id, $oldPrecoVenda, $novoPrecoVenda);
                }
            }

            $this->loteMargem = '';
            $this->loteAcrescimo = '';
            $this->toast("$count itens atualizados em lote!");
        });
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.preco-manager')
            ->layout('components.layouts.app', ['title' => 'Tabela de Preços · ERP Mercado']);
    }
}

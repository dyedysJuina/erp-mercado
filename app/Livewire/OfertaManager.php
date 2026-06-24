<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\OfertaCampanha;
use App\Models\OfertaProduto;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\BrazilianNumber;
use InvalidArgumentException;

class OfertaManager extends Component
{
    use WithPagination;

    public string $aba = 'campanhas';
    public string $busca = '';
    public string $filtroStatus = '';

    // Campaign form
    public ?int $editandoId = null;
    public string $modo = 'create';
    public string $nome = '';
    public string $descricao = '';
    public string $data_inicio = '';
    public string $data_fim = '';
    public bool $ativo = true;
    public array $lojasSelecionadas = [];

    // Product search / add
    public string $buscaProduto = '';
    public array $produtosResultado = [];
    public array $produtosCampanha = [];
    public string $descontoEmLote = '';
    public string $tipoDescontoLote = 'percentual'; // 'percentual' | 'valor'

    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['boolean'],
            'lojasSelecionadas' => ['required', 'array', 'min:1'],
        ];
    }

    protected function messages(): array
    {
        return [
            'lojasSelecionadas.required' => 'Selecione pelo menos uma loja.',
            'lojasSelecionadas.min' => 'Selecione pelo menos uma loja.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ];
    }

    #[Computed]
    public function lojas(): array
    {
        return Loja::where('ativo', true)->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function campanhas()
    {
        $q = OfertaCampanha::withCount('produtos')->orderBy('data_inicio', 'desc');
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('nome', 'like', "%{$this->busca}%");
        }
        if ($this->filtroStatus === 'ativas') {
            $q->where('data_inicio', '<=', now())->where('data_fim', '>=', now());
        } elseif ($this->filtroStatus === 'agendadas') {
            $q->where('data_inicio', '>', now());
        } elseif ($this->filtroStatus === 'expiradas') {
            $q->where('data_fim', '<', now());
        }
        return $q->paginate(15);
    }

    #[Computed]
    public function totais(): array
    {
        return [
            'total' => OfertaCampanha::count(),
            'ativas' => OfertaCampanha::where('data_inicio', '<=', now())->where('data_fim', '>=', now())->count(),
            'agendadas' => OfertaCampanha::where('data_inicio', '>', now())->count(),
            'expiradas' => OfertaCampanha::where('data_fim', '<', now())->count(),
        ];
    }

    public function statusCampanha(OfertaCampanha $c): array
    {
        $agora = now();
        if ($c->data_fim < $agora) return ['label' => 'Expirada', 'class' => 'badge-inativo'];
        if ($c->data_inicio > $agora) return ['label' => 'Agendada', 'class' => 'badge-warning'];
        return ['label' => 'Ativa', 'class' => 'badge-ativo'];
    }

    public function selecionar(?int $id = null): void
    {
        if (!$id) { $this->novo(); return; }
        $this->aba = 'produtos';
        $c = OfertaCampanha::with('lojas')->findOrFail($id);
        $this->editandoId = $c->id;
        $this->modo = 'edit';
        $this->nome = $c->nome;
        $this->descricao = $c->descricao ?? '';
        $this->data_inicio = $c->data_inicio->format('Y-m-d\TH:i');
        $this->data_fim = $c->data_fim->format('Y-m-d\TH:i');
        $this->ativo = $c->ativo;
        $this->lojasSelecionadas = $c->lojas->pluck('id')->map(fn($id) => (string)$id)->toArray();
        $this->carregarProdutos();
    }

    public function novo(): void
    {
        $this->aba = 'produtos';
        $this->editandoId = null;
        $this->modo = 'create';
        $this->nome = '';
        $this->descricao = '';
        $this->data_inicio = now()->format('Y-m-d\TH:i');
        $this->data_fim = now()->addDays(30)->format('Y-m-d\TH:i');
        $this->ativo = true;
        $this->lojasSelecionadas = [];
        $this->produtosCampanha = [];
        $this->resetErrorBag();
    }

    public function salvar(): void
    {
        $this->validate();

        $data = [
            'empresa_id' => 1,
            'nome' => $this->nome,
            'descricao' => $this->descricao ?: null,
            'data_inicio' => Carbon::parse($this->data_inicio),
            'data_fim' => Carbon::parse($this->data_fim),
            'ativo' => $this->ativo,
        ];

        if ($this->modo === 'create') {
            $camp = OfertaCampanha::create($data);
        } else {
            $camp = OfertaCampanha::findOrFail($this->editandoId);
            $camp->update($data);
        }

        $camp->lojas()->sync(collect($this->lojasSelecionadas)->filter()->map(fn($id) => (int)$id)->values()->toArray());

        // Sync products
        if ($this->modo === 'edit' && $this->editandoId) {
            OfertaProduto::where('campanha_id', $camp->id)->delete();
        }
        foreach ($this->produtosCampanha as $p) {
            $precoPor = $this->parseBr($p['preco_por'] ?? 0);
            if (($p['variacao_id'] ?? 0) && $precoPor > 0) {
                foreach ($this->lojasSelecionadas as $lojaId) {
                    OfertaProduto::create([
                        'campanha_id' => $camp->id,
                        'loja_id' => (int)$lojaId,
                        'produto_variacao_id' => (int)$p['variacao_id'],
                        'preco_de' => $this->parseBr($p['preco_de'] ?? 0) ?: null,
                        'preco_por' => $precoPor,
                        'preco_clube' => $this->parseBr($p['preco_clube'] ?? 0) ?: null,
                        'ativo' => true,
                    ]);
                }
            }
        }

        $this->toast('Campanha salva com sucesso!');
        if ($this->modo === 'create') $this->selecionar($camp->id);
    }

    public function excluir(int $id): void
    {
        OfertaProduto::where('campanha_id', $id)->delete();
        OfertaCampanha::findOrFail($id)->delete();
        $this->novo();
        $this->toast('Campanha excluída.');
    }

    // ─── Products ───

    public function carregarProdutos(): void
    {
        if (!$this->editandoId) { $this->produtosCampanha = []; return; }
        $this->produtosCampanha = OfertaProduto::where('campanha_id', $this->editandoId)
            ->where('loja_id', function ($q) {
                $q->select('id')->from('lojas')->where('ativo', true)->limit(1);
            })
            ->with('variacao')
            ->get()
            ->groupBy('produto_variacao_id')
            ->map(fn($grupo) => [
                'variacao_id' => $grupo->first()->produto_variacao_id,
                'nome' => $grupo->first()->variacao?->nome_completo ?? '—',
                'preco_de' => (float)$grupo->first()->preco_de,
                'preco_por' => (float)$grupo->first()->preco_por,
                'preco_clube' => (float)$grupo->first()->preco_clube,
                'desconto_pct' => $grupo->first()->preco_de > 0 ? round(($grupo->first()->preco_de - $grupo->first()->preco_por) / $grupo->first()->preco_de * 100, 1) : 0,
            ])->values()->toArray();
    }

    public function buscarProdutos(): void
    {
        if (strlen(trim($this->buscaProduto)) < 2) { $this->produtosResultado = []; return; }
        $q = $this->buscaProduto;
        $this->produtosResultado = ProdutoVariacao::where('ativo', true)
            ->where(function ($w) use ($q) {
                $w->where('nome_completo', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            })
            ->with('marca')
            ->limit(15)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'nome' => $v->nome_completo,
                'sku' => $v->sku,
                'marca' => $v->marca?->nome ?? '',
                'preco_atual' => (float) DB::table('tabela_precos_itens')
                    ->join('lojas', 'lojas.tabela_preco_id', '=', 'tabela_precos_itens.tabela_preco_id')
                    ->where('lojas.ativo', true)
                    ->where('tabela_precos_itens.produto_variacao_id', $v->id)
                    ->value('preco_venda'),
            ])
            ->toArray();
    }

    public function adicionarProduto(int $variacaoId): void
    {
        $jaExiste = collect($this->produtosCampanha)->firstWhere('variacao_id', $variacaoId);
        if ($jaExiste) { $this->toast('Produto já está na campanha.'); return; }

        $v = ProdutoVariacao::with('marca')->find($variacaoId);
        if (!$v) return;

        $precoAtual = (float) DB::table('tabela_precos_itens')
            ->join('lojas', 'lojas.tabela_preco_id', '=', 'tabela_precos_itens.tabela_preco_id')
            ->where('lojas.ativo', true)
            ->where('tabela_precos_itens.produto_variacao_id', $v->id)
            ->value('preco_venda');

        $this->produtosCampanha[] = [
            'variacao_id' => $v->id,
            'nome' => $v->nome_completo,
            'preco_de' => $precoAtual,
            'preco_por' => $precoAtual,
            'preco_clube' => 0,
            'desconto_pct' => 0,
        ];
        $this->buscaProduto = '';
        $this->produtosResultado = [];
        $this->toast('Produto adicionado!');
    }

    public function removerProduto(int $idx): void
    {
        if (isset($this->produtosCampanha[$idx])) {
            unset($this->produtosCampanha[$idx]);
            $this->produtosCampanha = array_values($this->produtosCampanha);
        }
    }

    public function updatedProdutosCampanha(): void
    {
        foreach ($this->produtosCampanha as $i => &$p) {
            $precoDe = $this->parseBr($p['preco_de'] ?? 0);
            $precoPor = $this->parseBr($p['preco_por'] ?? 0);
            $p['preco_de'] = $precoDe;
            $p['preco_por'] = $precoPor;
            $p['preco_clube'] = $this->parseBr($p['preco_clube'] ?? 0);
            $p['desconto_pct'] = $precoDe > 0 ? round(($precoDe - $precoPor) / $precoDe * 100, 1) : 0;
        }
    }

    public function aplicarDescontoEmLote(): void
    {
        $valor = (float)$this->descontoEmLote;
        if ($valor <= 0) return;
        foreach ($this->produtosCampanha as $i => &$p) {
            $precoDe = (float)$p['preco_de'];
            if ($precoDe <= 0) continue;
            if ($this->tipoDescontoLote === 'percentual') {
                $p['preco_por'] = round($precoDe * (1 - $valor / 100), 2);
            } else {
                $p['preco_por'] = round(max(0, $precoDe - $valor), 2);
            }
            $p['desconto_pct'] = round(($precoDe - $p['preco_por']) / $precoDe * 100, 1);
        }
        $this->descontoEmLote = '';
        $this->toast('Desconto aplicado em lote!');
    }

    public function adicionarLoteCategoria(int $categoriaId): void
    {
        $variacoes = ProdutoVariacao::where('ativo', true)
            ->whereHas('produtoBase', fn($q) => $q->where('categoria_id', $categoriaId))
            ->get();

        $count = 0;
        foreach ($variacoes as $v) {
            $jaExiste = collect($this->produtosCampanha)->firstWhere('variacao_id', $v->id);
            if ($jaExiste) continue;
            $precoAtual = (float) DB::table('tabela_precos_itens')
                ->join('lojas', 'lojas.tabela_preco_id', '=', 'tabela_precos_itens.tabela_preco_id')
                ->where('lojas.ativo', true)
                ->where('tabela_precos_itens.produto_variacao_id', $v->id)
                ->value('preco_venda');
            $this->produtosCampanha[] = [
                'variacao_id' => $v->id,
                'nome' => $v->nome_completo,
                'preco_de' => $precoAtual,
                'preco_por' => $precoAtual,
                'preco_clube' => 0,
                'desconto_pct' => 0,
            ];
            $count++;
        }
        $this->toast("{$count} produto(s) adicionado(s) da categoria!");
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    private function parseBr(mixed $value): float
    {
        if (is_numeric($value)) return (float)$value;
        try {
            return (float) BrazilianNumber::decimal($value, 2);
        } catch (InvalidArgumentException) {
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.oferta-manager')
            ->layout('components.layouts.app', ['title' => 'Campanhas & Ofertas · ERP Mercado']);
    }
}

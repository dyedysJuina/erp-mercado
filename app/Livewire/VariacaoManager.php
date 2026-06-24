<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Cfop;
use App\Models\Cest as CestModel;
use App\Models\IcmsCst;
use App\Models\Ncm;
use App\Models\ProdutoBase;
use App\Models\ProdutoVariacao;
use App\Models\ProdutoCodigoBarras;
use App\Models\ProdutoImagem;
use App\Models\ProdutoVariacaoAtributo;
use App\Models\ProdutoApresentacao;
use App\Models\Marca;
use App\Models\Embalagem;
use App\Models\UnidadeMedida;
use App\Models\Atributo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB as DBFacade;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class VariacaoManager extends Component
{
    use WithFileUploads, WithPagination;

    // ─── Busca ───
    public string $buscaProduto = '';
    public ?int $produtoBaseSelecionado = null;

    // ─── Edição ───
    public ?int $editandoId = null;
    public string $modo = 'create';

    // ─── Form ───
    public string $sku = '';
    public string $marca_id = '';
    public string $embalagem_id = '';
    public string $unidade_medida_id = '';
    public string $conteudo_quantidade = '1';
    public string $qtd_por_embalagem = '1'; // quantas unidades dentro (fardo, caixa, pack)
    public bool $pesavel = false;
    public bool $fracionado = false;
    public string $quantidade_minima_venda = '1';
    public string $passo_venda = '1';
    public bool $ativo = true;

    // ─── Fiscal ───
    public ?string $ncm_id = null;
    public string $ncm_busca = '';
    public ?string $cfop_id = null;
    public ?string $cest_id = null;
    public ?string $cst_icms = null;
    public string $origem_mercadoria = '0';
    public bool $usar_aliquotas = false;
    public ?string $aliquota_icms = null;
    public ?string $aliquota_pis = null;
    public ?string $aliquota_cofins = null;

    // ─── Códigos de barras ───
    public array $codigosBarras = [];

    // ─── Atributos da categoria ───
    public array $atributosValores = [];

    // ─── Imagens ───
    public array $novasImagens = [];

    // ─── Apresentações ───
    public array $apresentacoes = [];

    // ─── Toast ───
    public string $toastMsg = '';
    public bool $toastShow = false;

    // ─── Lista ───
    public string $filtroLista = '';
    protected string $paginationTheme = 'tailwind';

    // ─── Paginação personalizada ───
    public function updatedPage(): void { $this->resetForm(); }

    protected $messages = [
        'unidade_medida_id.required' => 'Selecione uma unidade de medida.',
        'unidade_medida_id.exists' => 'Unidade de medida inválida.',
        'conteudo_quantidade.required' => 'Informe o conteúdo da variação.',
        'sku.unique' => 'Este SKU já está em uso.',
        'codigosBarras.*.codigo.required_with' => 'Informe o código de barras.',
        'codigosBarras.*.codigo.distinct' => 'Código de barras duplicado.',
        'apresentacoes.*.nome.required' => 'Informe o nome da apresentação.',
    ];

    protected function rules(): array
    {
        $rules = [
            'sku' => ['nullable', 'string', 'max:50'],
            'conteudo_quantidade' => ['required', 'numeric', 'min:0.001'],
            'unidade_medida_id' => ['required', 'exists:unidades_medida,id'],
            'pesavel' => ['boolean'],
            'fracionado' => ['boolean'],
            'quantidade_minima_venda' => ['required', 'numeric', 'min:0'],
            'passo_venda' => ['required', 'numeric', 'min:0'],
            'ativo' => ['boolean'],
            'codigosBarras' => ['array', 'max:10'],
            'codigosBarras.*.codigo' => ['required_with:codigosBarras.*.tipo', 'string', 'max:30', 'distinct'],
            'codigosBarras.*.tipo' => ['required_with:codigosBarras.*.codigo', 'string', 'in:ean13,ean8,dun14,interno,balanca'],
            'novasImagens' => ['array', 'max:8'],
            'novasImagens.*' => ['image', 'max:5120', 'mimes:jpeg,jpg,png,webp,avif'],
            'apresentacoes' => ['array', 'max:10'],
            'apresentacoes.*.nome' => ['required', 'string', 'max:120'],
            'apresentacoes.*.conteudo_quantidade' => ['required', 'numeric', 'min:0.001'],
        ];

        if ($this->usar_aliquotas) {
            $rules['aliquota_icms'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['aliquota_pis'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['aliquota_cofins'] = ['nullable', 'numeric', 'min:0', 'max:100'];
        }

        return $rules;
    }

    // ═══════════════════════════════════════════════════
    //  BUSCA
    // ═══════════════════════════════════════════════════

    #[Computed]
    public function resultadosBusca(): array
    {
        if (strlen(trim($this->buscaProduto)) < 2) return [];
        return ProdutoBase::where('nome', 'like', '%' . $this->buscaProduto . '%')
            ->orWhere('slug', 'like', '%' . $this->buscaProduto . '%')
            ->with('categoria')->orderBy('nome')->limit(10)
            ->get()->toArray();
    }

    public function selecionarProduto(int $id): void
    {
        $this->produtoBaseSelecionado = $id;
        $this->buscaProduto = '';
        $this->resetForm();
        $this->carregarDefaultsFiscais();
        $this->resetPage();
    }

    public function limparSelecao(): void
    {
        $this->produtoBaseSelecionado = null;
        $this->editandoId = null;
        $this->modo = 'create';
        $this->resetForm();
    }

    #[Computed]
    public function produtoBase(): ?array
    {
        if (!$this->produtoBaseSelecionado) return null;
        $p = ProdutoBase::with('categoria')->find($this->produtoBaseSelecionado);
        return $p?->toArray();
    }

    #[Computed]
    public function breadcrumb(): string
    {
        $p = $this->produtoBase;
        return $p['categoria']['caminho'] ?? ($p['categoria']['nome'] ?? '');
    }

    // ═══════════════════════════════════════════════════
    //  OPÇÕES
    // ═══════════════════════════════════════════════════

    #[Computed]
    public function optsMarcas(): array { return Marca::where('ativo',true)->orderBy('nome')->get(['id','nome'])->toArray(); }
    #[Computed]
    public function optsEmbalagens(): array { return Embalagem::where('ativo',true)->orderBy('nome')->get(['id','nome','sigla'])->toArray(); }
    #[Computed]
    public function optsUnidades(): array { return UnidadeMedida::orderBy('nome')->get(['id','sigla','nome'])->toArray(); }
    public function optsTiposCodigo(): array { return ['ean13'=>'EAN-13','ean8'=>'EAN-8','dun14'=>'DUN-14','interno'=>'Interno','balanca'=>'Balança']; }
    public function optsTiposApresentacao(): array { return ['multipla'=>'Múltipla','venda'=>'Venda','compra'=>'Compra','estoque'=>'Estoque','fiscal'=>'Fiscal']; }

    // ─── Fiscais ───

    #[Computed]
    public function ncmOpts(): array
    {
        $q = trim($this->ncm_busca);
        if (strlen($q) < 1) return [];
        return Ncm::where('ativo', true)
            ->where(fn($qry) => $qry->where('codigo','like',"%{$q}%")->orWhere('descricao','like',"%{$q}%"))
            ->orderBy('codigo')->limit(20)->get(['id','codigo','descricao'])->toArray();
    }

    #[Computed]
    public function cfopOpts(): array
    {
        return Cfop::where('ativo',true)->where('aplicacao','saida')->orderBy('codigo')->get(['id','codigo','descricao'])->toArray();
    }

    #[Computed]
    public function csosnOpts(): array
    {
        return IcmsCst::where('ativo',true)->where('regime','simples_nacional')->orderBy('codigo')->get(['codigo','descricao'])->toArray();
    }

    public function selecionarNcm(int $id): void
    {
        $this->ncm_id = (string)$id;
        $this->ncm_busca = '';
    }

    private function carregarDefaultsFiscais(): void
    {
        $pb = ProdutoBase::find($this->produtoBaseSelecionado);
        if (!$pb) return;
        $this->ncm_id = $pb->ncm_id ? (string)$pb->ncm_id : null;
        $this->cfop_id = $pb->cfop_id ? (string)$pb->cfop_id : null;
        $this->cest_id = $pb->cest_id ? (string)$pb->cest_id : null;
        $this->cst_icms = $pb->cst_icms ?: '102';
        $this->origem_mercadoria = $pb->origem_mercadoria ?: '0';
    }

    // ═══════════════════════════════════════════════════
    //  ATRIBUTOS DA CATEGORIA
    // ═══════════════════════════════════════════════════

    #[Computed]
    public function atributosCategoria(): array
    {
        if (!$this->produtoBaseSelecionado) return [];
        $p = ProdutoBase::find($this->produtoBaseSelecionado);
        if (!$p || !$p->categoria_id) return [];
        $ids = DBFacade::table('categorias_atributos')
            ->where('categoria_id', $p->categoria_id)->where('ativo', true)->orderBy('ordem')
            ->pluck('atributo_id');
        if ($ids->isEmpty()) return [];
        return Atributo::whereIn('id', $ids)->where('ativo', true)->orderBy('nome')->get()->toArray();
    }

    protected function initAtributosValores(): void
    {
        $this->atributosValores = [];
        foreach ($this->atributosCategoria as $attr) {
            $this->atributosValores[$attr['id']] = [
                'atributo_id' => $attr['id'], 'valor_texto' => '',
                'valor_numero' => null, 'valor_booleano' => null, 'valor_data' => null,
            ];
        }
    }

    // ═══════════════════════════════════════════════════
    //  NOME AUTO
    // ═══════════════════════════════════════════════════

    #[Computed]
    public function nomePreview(): string
    {
        $p = $this->produtoBase; if (!$p) return '';
        $parts = [$p['nome']];
        if ($this->marca_id) {
            $m = collect($this->optsMarcas)->firstWhere('id', (int)$this->marca_id);
            if ($m) $parts[] = $m['nome'];
        }
        if ($this->conteudo_quantidade && $this->conteudo_quantidade !== '0') $parts[] = (string)(float)$this->conteudo_quantidade;
        if ($this->unidade_medida_id) {
            $u = collect($this->optsUnidades)->firstWhere('id', (int)$this->unidade_medida_id);
            if ($u) $parts[] = $u['sigla'];
        }

        $qtdEmb = (int)$this->qtd_por_embalagem;
        if ($qtdEmb > 1 && $this->embalagem_id) {
            $e = collect($this->optsEmbalagens)->firstWhere('id', (int)$this->embalagem_id);
            if ($e) $parts[] = $e['nome'] . ' ' . $qtdEmb . ' Un';
        } elseif ($this->embalagem_id) {
            $e = collect($this->optsEmbalagens)->firstWhere('id', (int)$this->embalagem_id);
            if ($e) $parts[] = $e['nome'];
        }

        return implode(' ', $parts);
    }

    // ═══════════════════════════════════════════════════
    //  FORM ACTIONS
    // ═══════════════════════════════════════════════════

    public function resetForm(): void
    {
        $this->editandoId = null; $this->modo = 'create';
        $this->sku = ''; $this->marca_id = ''; $this->embalagem_id = '';
        $this->unidade_medida_id = ''; $this->conteudo_quantidade = '1'; $this->qtd_por_embalagem = '1';
        $this->pesavel = false; $this->fracionado = false;
        $this->quantidade_minima_venda = '1'; $this->passo_venda = '1'; $this->ativo = true;
        $this->ncm_id = null; $this->ncm_busca = ''; $this->cfop_id = null;
        $this->cest_id = null; $this->cst_icms = null; $this->origem_mercadoria = '0';
        $this->usar_aliquotas = false; $this->aliquota_icms = null;
        $this->aliquota_pis = null; $this->aliquota_cofins = null;
        $this->codigosBarras = []; $this->novasImagens = []; $this->apresentacoes = [];
        $this->resetErrorBag();
        $this->initAtributosValores();
        $this->carregarDefaultsFiscais();
    }

    public function novaVariacao(): void { $this->resetForm(); }

    public function editarVariacao(int $id): void
    {
        $v = ProdutoVariacao::with(['codigosBarras', 'imagens', 'apresentacoes'])->findOrFail($id);
        $this->editandoId = $v->id; $this->modo = 'edit';
        $this->sku = $v->sku ?? '';
        $this->marca_id = (string)$v->marca_id;
        $this->embalagem_id = (string)$v->embalagem_id;
        $this->unidade_medida_id = (string)$v->unidade_medida_id;
        $this->conteudo_quantidade = (string)$v->conteudo_quantidade;
        $this->qtd_por_embalagem = '1'; // default, apresentacoes tem seu proprio qtd
        $this->pesavel = $v->pesavel; $this->fracionado = $v->fracionado;
        $this->quantidade_minima_venda = (string)$v->quantidade_minima_venda;
        $this->passo_venda = (string)$v->passo_venda;
        $this->ativo = $v->ativo;
        $this->ncm_id = $v->ncm_id ? (string)$v->ncm_id : null;
        $this->ncm_busca = '';
        $this->cfop_id = $v->cfop_id ? (string)$v->cfop_id : null;
        $this->cest_id = $v->cest_id ? (string)$v->cest_id : null;
        $this->cst_icms = $v->cst_icms ?: ($v->produtoBase?->cst_icms ?: '102');
        $this->origem_mercadoria = $v->origem_mercadoria ?? '0';
        if ($v->aliquota_icms !== null) { $this->usar_aliquotas = true; $this->aliquota_icms = (string)$v->aliquota_icms; }
        if ($v->aliquota_pis !== null) { $this->usar_aliquotas = true; $this->aliquota_pis = (string)$v->aliquota_pis; }
        if ($v->aliquota_cofins !== null) { $this->usar_aliquotas = true; $this->aliquota_cofins = (string)$v->aliquota_cofins; }

        $this->codigosBarras = $v->codigosBarras->map(fn($b) => [
            'id' => $b->id, 'codigo' => $b->codigo, 'tipo' => $b->tipo,
            'descricao' => $b->descricao ?? '', 'principal' => $b->principal,
        ])->toArray();

        $this->apresentacoes = $v->apresentacoes->map(fn($a) => [
            'id' => $a->id, 'nome' => $a->nome, 'tipo' => $a->tipo,
            'embalagem_id' => (string)$a->embalagem_id,
            'unidade_medida_id' => (string)$a->unidade_medida_id,
            'conteudo_quantidade' => (string)$a->conteudo_quantidade,
            'fator_conversao_estoque' => (string)$a->fator_conversao_estoque,
            'permite_venda' => $a->permite_venda, 'permite_compra' => $a->permite_compra,
            'controla_estoque' => $a->controla_estoque,
            'principal_venda' => $a->principal_venda, 'principal_compra' => $a->principal_compra,
            'principal_estoque' => $a->principal_estoque,
        ])->toArray();

        $this->initAtributosValores();
        $valores = ProdutoVariacaoAtributo::where('produto_variacao_id', $v->id)->get();
        foreach ($valores as $val) {
            if (isset($this->atributosValores[$val->atributo_id])) {
                $this->atributosValores[$val->atributo_id] = [
                    'atributo_id' => $val->atributo_id, 'valor_texto' => $val->valor_texto ?? '',
                    'valor_numero' => $val->valor_numero, 'valor_booleano' => $val->valor_booleano,
                    'valor_data' => $val->valor_data,
                ];
            }
        }
    }

    public function duplicarVariacao(int $id): void
    {
        $v = ProdutoVariacao::with(['codigosBarras', 'apresentacoes'])->findOrFail($id);
        $this->resetForm();
        $this->produtoBaseSelecionado = $v->produto_base_id;
        $this->marca_id = (string)$v->marca_id;
        $this->embalagem_id = (string)$v->embalagem_id;
        $this->unidade_medida_id = (string)$v->unidade_medida_id;
        $this->conteudo_quantidade = (string)$v->conteudo_quantidade;
        $this->pesavel = $v->pesavel; $this->fracionado = $v->fracionado;
        $this->quantidade_minima_venda = (string)$v->quantidade_minima_venda;
        $this->passo_venda = (string)$v->passo_venda;
        $this->ncm_id = $v->ncm_id ? (string)$v->ncm_id : null;
        $this->ncm_busca = '';
        $this->cfop_id = $v->cfop_id ? (string)$v->cfop_id : null;
        $this->cest_id = $v->cest_id ? (string)$v->cest_id : null;
        $this->cst_icms = $v->cst_icms ?: '102';
        $this->origem_mercadoria = $v->origem_mercadoria ?? '0';
        if ($v->aliquota_icms !== null) { $this->usar_aliquotas = true; $this->aliquota_icms = (string)$v->aliquota_icms; }
        if ($v->aliquota_pis !== null) { $this->usar_aliquotas = true; $this->aliquota_pis = (string)$v->aliquota_pis; }
        if ($v->aliquota_cofins !== null) { $this->usar_aliquotas = true; $this->aliquota_cofins = (string)$v->aliquota_cofins; }
        $this->codigosBarras = [];
        $this->apresentacoes = $v->apresentacoes->map(fn($a) => [
            'nome' => $a->nome, 'tipo' => $a->tipo,
            'embalagem_id' => (string)$a->embalagem_id,
            'unidade_medida_id' => (string)$a->unidade_medida_id,
            'conteudo_quantidade' => (string)$a->conteudo_quantidade,
            'fator_conversao_estoque' => (string)$a->fator_conversao_estoque,
            'permite_venda' => $a->permite_venda, 'permite_compra' => $a->permite_compra,
            'controla_estoque' => $a->controla_estoque,
            'principal_venda' => false, 'principal_compra' => false, 'principal_estoque' => false,
        ])->toArray();
    }

    public function adicionarApresentacao(): void
    {
        $this->apresentacoes[] = [
            'id' => null, 'nome' => '', 'tipo' => 'multipla',
            'embalagem_id' => '', 'unidade_medida_id' => '',
            'conteudo_quantidade' => '1', 'fator_conversao_estoque' => '1',
            'permite_venda' => true, 'permite_compra' => true, 'controla_estoque' => true,
            'principal_venda' => false, 'principal_compra' => false, 'principal_estoque' => false,
        ];
    }

    public function removerApresentacao(int $idx): void
    {
        if (isset($this->apresentacoes[$idx])) {
            if ($this->apresentacoes[$idx]['id']) {
                ProdutoApresentacao::find($this->apresentacoes[$idx]['id'])?->delete();
            }
            unset($this->apresentacoes[$idx]);
            $this->apresentacoes = array_values($this->apresentacoes);
        }
    }

    public function adicionarCodigoBarras(): void
    {
        $this->codigosBarras[] = [
            'id' => null, 'codigo' => '', 'tipo' => 'ean13',
            'descricao' => '', 'principal' => count($this->codigosBarras) === 0,
        ];
    }

    public function removerCodigoBarras(int $idx): void
    {
        if (isset($this->codigosBarras[$idx])) {
            if ($this->codigosBarras[$idx]['id'])
                ProdutoCodigoBarras::find($this->codigosBarras[$idx]['id'])?->delete();
            unset($this->codigosBarras[$idx]);
            $this->codigosBarras = array_values($this->codigosBarras);
        }
    }

    // ═══════════════════════════════════════════════════
    //  SALVAR (COM TRANSAÇÃO)
    // ═══════════════════════════════════════════════════

    public function salvar(): void
    {
        $this->validate();
        if (!$this->produtoBaseSelecionado || !$this->unidade_medida_id) {
            $this->addError('unidade_medida_id', 'Selecione produto base e unidade.'); return;
        }

        $nomeCompleto = $this->nomePreview;
        $slug = Str::slug($nomeCompleto) . '-' . time();

        // Checa SKU duplicado
        if (trim($this->sku)) {
            $skuExists = ProdutoVariacao::where('sku', trim($this->sku))
                ->when($this->modo === 'edit', fn($q) => $q->where('id', '!=', $this->editandoId))
                ->exists();
            if ($skuExists) { $this->addError('sku', 'SKU já existe.'); return; }
        }

        try {
            DBFacade::beginTransaction();

            $data = [
                'produto_base_id' => $this->produtoBaseSelecionado,
                'marca_id' => $this->marca_id ? (int)$this->marca_id : null,
                'unidade_medida_id' => (int)$this->unidade_medida_id,
                'embalagem_id' => $this->embalagem_id ? (int)$this->embalagem_id : null,
                'nome_completo' => $nomeCompleto, 'slug' => $slug,
                'sku' => trim($this->sku) ?: null,
                'conteudo_quantidade' => (float)$this->conteudo_quantidade,
                'pesavel' => $this->pesavel, 'fracionado' => $this->fracionado,
                'quantidade_minima_venda' => (float)$this->quantidade_minima_venda,
                'passo_venda' => (float)$this->passo_venda,
                'ncm_id' => $this->ncm_id ? (int)$this->ncm_id : null,
                'cfop_id' => $this->cfop_id ? (int)$this->cfop_id : null,
                'cest_id' => $this->cest_id ? (int)$this->cest_id : null,
                'cst_icms' => $this->cst_icms ?: '102',
                'origem_mercadoria' => $this->origem_mercadoria ?: '0',
                'aliquota_icms' => $this->usar_aliquotas && $this->aliquota_icms !== '' ? (float)$this->aliquota_icms : null,
                'aliquota_pis' => $this->usar_aliquotas && $this->aliquota_pis !== '' ? (float)$this->aliquota_pis : null,
                'aliquota_cofins' => $this->usar_aliquotas && $this->aliquota_cofins !== '' ? (float)$this->aliquota_cofins : null,
                'ativo' => $this->ativo,
            ];

            if ($this->modo === 'create') {
                $var = ProdutoVariacao::create($data);
            } else {
                $var = ProdutoVariacao::findOrFail($this->editandoId);
                $var->update($data);
                ProdutoCodigoBarras::where('produto_variacao_id', $var->id)->delete();
                ProdutoVariacaoAtributo::where('produto_variacao_id', $var->id)->delete();
                ProdutoApresentacao::where('produto_variacao_id', $var->id)->delete();
            }

            // Códigos
            foreach ($this->codigosBarras as $cb) {
                if (trim($cb['codigo'] ?? ''))
                    ProdutoCodigoBarras::create([
                        'produto_variacao_id' => $var->id, 'codigo' => trim($cb['codigo']),
                        'tipo' => $cb['tipo'] ?? 'ean13', 'descricao' => $cb['descricao'] ?? null,
                        'principal' => $cb['principal'] ?? false,
                    ]);
            }

            // Atributos
            foreach ($this->atributosValores as $av) {
                if (trim($av['valor_texto'] ?? '') || $av['valor_numero'] || $av['valor_booleano'] || $av['valor_data'])
                    ProdutoVariacaoAtributo::create([
                        'produto_variacao_id' => $var->id, 'atributo_id' => $av['atributo_id'],
                        'valor_texto' => $av['valor_texto'] ?: null,
                        'valor_numero' => $av['valor_numero'], 'valor_booleano' => $av['valor_booleano'],
                        'valor_data' => $av['valor_data'],
                    ]);
            }

            // Auto-gerar apresentação se for embalagem multipla (fardo, caixa, pack...)
            $qtdEmb = (int)$this->qtd_por_embalagem;
            if ($qtdEmb > 1 && $this->embalagem_id) {
                $emb = Embalagem::find((int)$this->embalagem_id);
                $unid = UnidadeMedida::find((int)$this->unidade_medida_id);
                $embNome = $emb->nome ?? 'Un';
                $unidSigla = $unid->sigla ?? 'UN';
                $nomeApres = $this->nomePreview;
                ProdutoApresentacao::create([
                    'produto_variacao_id' => $var->id,
                    'nome' => $embNome . ' ' . $qtdEmb . ' ' . $unidSigla,
                    'tipo' => 'estoque',
                    'embalagem_id' => (int)$this->embalagem_id,
                    'unidade_medida_id' => (int)$this->unidade_medida_id,
                    'conteudo_quantidade' => $qtdEmb * (float)$this->conteudo_quantidade,
                    'fator_conversao_estoque' => $qtdEmb,
                    'permite_venda' => false,
                    'permite_compra' => true,
                    'controla_estoque' => true,
                    'principal_estoque' => true,
                ]);
            }

            // Apresentações manuais
            foreach ($this->apresentacoes as $ap) {
                ProdutoApresentacao::create([
                    'produto_variacao_id' => $var->id, 'nome' => $ap['nome'], 'tipo' => $ap['tipo'],
                    'embalagem_id' => $ap['embalagem_id'] ? (int)$ap['embalagem_id'] : null,
                    'unidade_medida_id' => (int)($ap['unidade_medida_id'] ?: $this->unidade_medida_id),
                    'conteudo_quantidade' => (float)$ap['conteudo_quantidade'],
                    'fator_conversao_estoque' => (float)($ap['fator_conversao_estoque'] ?? 1),
                    'permite_venda' => $ap['permite_venda'] ?? true,
                    'permite_compra' => $ap['permite_compra'] ?? true,
                    'controla_estoque' => $ap['controla_estoque'] ?? true,
                    'principal_venda' => $ap['principal_venda'] ?? false,
                    'principal_compra' => $ap['principal_compra'] ?? false,
                    'principal_estoque' => $ap['principal_estoque'] ?? false,
                ]);
            }

            // Imagens
            if (count($this->novasImagens) > 0) {
                $ordem = ProdutoImagem::where('produto_variacao_id', $var->id)->count();
                foreach ($this->novasImagens as $i => $arquivo) {
                    $path = $this->otimizarImagem($arquivo);
                    ProdutoImagem::create([
                        'produto_variacao_id' => $var->id, 'url' => $path,
                        'ordem' => $ordem++, 'principal' => $ordem === 1, 'is_active' => true,
                    ]);
                }
            }

            DBFacade::commit();
            $this->toast($this->modo === 'create' ? 'Variação cadastrada!' : 'Variação atualizada!');
            $this->resetForm();
            $this->modo = 'create';
            $this->editandoId = null;
        } catch (\Exception $e) {
            DBFacade::rollBack();
            $this->addError('conteudo_quantidade', 'Erro: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    //  EXCLUSÃO
    // ═══════════════════════════════════════════════════

    public function excluir(int $id): void
    {
        $v = ProdutoVariacao::findOrFail($id);
        $deps = [];
        if (DBFacade::table('precos_produtos_lojas')->where('produto_variacao_id', $id)->exists()) $deps[] = 'preços';
        if (DBFacade::table('tabela_precos_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'tabela de preços';
        if (DBFacade::table('estoque_saldos')->where('produto_variacao_id', $id)->exists()) $deps[] = 'estoque';
        if (DBFacade::table('pedidos_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'pedidos';
        if (DBFacade::table('pdv_venda_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'vendas PDV';
        if (DBFacade::table('fiscal_documento_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'fiscal';
        if (DBFacade::table('compras_pedido_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'compras';
        if (DBFacade::table('compras_recebimento_itens')->where('produto_variacao_id', $id)->exists()) $deps[] = 'recebimentos';
        if (DBFacade::table('ofertas_produtos')->where('produto_variacao_id', $id)->exists()) $deps[] = 'ofertas';
        if (DBFacade::table('precos_historico')->where('produto_variacao_id', $id)->exists()) $deps[] = 'histórico de preços';
        if (!empty($deps)) { $this->addError('exclusao', 'Possui ' . implode(', ', $deps) . '. Inative em vez de excluir.'); return; }

        ProdutoCodigoBarras::where('produto_variacao_id', $id)->delete();
        ProdutoVariacaoAtributo::where('produto_variacao_id', $id)->delete();
        ProdutoApresentacao::where('produto_variacao_id', $id)->delete();
        foreach (ProdutoImagem::where('produto_variacao_id', $id)->get() as $img) {
            \Illuminate\Support\Facades\Storage::delete($img->url); $img->delete();
        }
        $v->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Variação excluída.');
    }

    // ═══════════════════════════════════════════════════
    //  IMAGENS
    // ═══════════════════════════════════════════════════

    public function removerImagemTemp(int $idx): void
    { if (isset($this->novasImagens[$idx])) { unset($this->novasImagens[$idx]); $this->novasImagens = array_values($this->novasImagens); } }

    public function definirPrincipal(int $imgId): void
    { $img = ProdutoImagem::findOrFail($imgId); ProdutoImagem::where('produto_variacao_id', $img->produto_variacao_id)->update(['principal'=>false]); $img->update(['principal'=>true]); }

    public function desativarImagem(int $imgId): void
    { $img = ProdutoImagem::findOrFail($imgId); $img->update(['is_active' => !$img->is_active]); }

    public function removerImagemSalva(int $imgId): void
    { $img = ProdutoImagem::findOrFail($imgId); \Illuminate\Support\Facades\Storage::delete($img->url); $img->delete(); }

    protected function otimizarImagem($arquivo): string
    {
        $path = $arquivo->store('produtos', 'public');
        $full = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
        $info = @getimagesize($full);
        if (!$info) return $path;
        [$w, $h] = $info;
        if ($w > 1200 || $h > 1200) {
            $r = min(1200/$w, 1200/$h);
            $src = match ($info['mime']) {
                'image/jpeg' => @imagecreatefromjpeg($full), 'image/png' => @imagecreatefrompng($full),
                'image/webp' => @imagecreatefromwebp($full),
                'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($full) : null,
                default => null,
            };
            if ($src) { $dst = imagecreatetruecolor((int)($w*$r), (int)($h*$r)); imagecopyresampled($dst, $src, 0,0,0,0,(int)($w*$r),(int)($h*$r),$w,$h); imagewebp($dst, $full, 80); imagedestroy($src); imagedestroy($dst); }
        }
        return $path;
    }

    // ═══════════════════════════════════════════════════
    //  LISTAGEM COM PAGINAÇÃO
    // ═══════════════════════════════════════════════════

    #[Computed]
    public function listaVariacoes()
    {
        if (!$this->produtoBaseSelecionado) return collect([]);
        $query = ProdutoVariacao::where('produto_base_id', $this->produtoBaseSelecionado)
            ->with(['marca:id,nome', 'embalagem:id,nome,sigla', 'unidadeMedida:id,sigla'])
            ->orderBy('nome_completo');
        if (strlen(trim($this->filtroLista)) >= 2)
            $query->where('nome_completo', 'like', '%' . $this->filtroLista . '%');
        return $query->paginate(20);
    }

    #[Computed]
    public function totalGeral(): int
    { return $this->produtoBaseSelecionado ? ProdutoVariacao::where('produto_base_id', $this->produtoBaseSelecionado)->count() : 0; }

    #[Computed]
    public function totalAtivas(): int
    { return $this->produtoBaseSelecionado ? ProdutoVariacao::where('produto_base_id', $this->produtoBaseSelecionado)->where('ativo', true)->count() : 0; }

    #[Computed]
    public function totalComSku(): int
    { return $this->produtoBaseSelecionado ? ProdutoVariacao::where('produto_base_id', $this->produtoBaseSelecionado)->whereNotNull('sku')->count() : 0; }

    public function desativar(int $id): void
    { $v = ProdutoVariacao::findOrFail($id); $v->update(['ativo' => !$v->ativo]);
      $this->toast($v->ativo ? 'Variação ativada.' : 'Variação desativada.'); }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.variacao-manager')
            ->layout('components.layouts.app', ['title' => 'Variações · ERP Mercado']);
    }
}

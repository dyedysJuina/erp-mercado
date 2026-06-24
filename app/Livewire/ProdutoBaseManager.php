<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Cfop;
use App\Models\IcmsCst;
use App\Models\Ncm;
use App\Models\ProdutoBase;
use App\Models\RegraFiscalNcm;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ProdutoBaseManager extends Component
{
    public string $nivel1 = '';
    public string $nivel2 = '';
    public string $nivel3 = '';
    public string $nome = '';
    public string $busca = '';

    public ?string $ncm_id = null;
    public string $ncm_busca = '';
    public ?string $cfop_id = null;
    public ?string $cest_id = null;
    public ?string $origem_mercadoria = null;
    public ?string $cst_icms = null;
    public bool $usar_aliquotas = false;
    public ?string $aliquota_icms = null;
    public ?string $aliquota_pis = null;
    public ?string $aliquota_cofins = null;

    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        $rules = ['nome' => ['required', 'string', 'max:180']];
        if ($this->usar_aliquotas) {
            $rules['aliquota_icms'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['aliquota_pis'] = ['nullable', 'numeric', 'min:0', 'max:100'];
            $rules['aliquota_cofins'] = ['nullable', 'numeric', 'min:0', 'max:100'];
        }
        return $rules;
    }

    protected $messages = [
        'nome.required' => 'O nome do produto é obrigatório.',
    ];

    public function mount(): void
    {
        $this->origem_mercadoria = '0';
        $this->cst_icms = '102';
        $cfop = Cfop::where('codigo', '5102')->first();
        if ($cfop) $this->cfop_id = (string)$cfop->id;
    }

    public function updatedNome(): void
    {
        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
    }

    // ─── CASCATA ───

    #[Computed]
    public function optsNivel1(): array
    {
        return Categoria::whereNull('parent_id')
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function optsNivel2(): array
    {
        if (!$this->nivel1) return [];
        return Categoria::where('parent_id', (int)$this->nivel1)
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function optsNivel3(): array
    {
        if (!$this->nivel2) return [];
        return Categoria::where('parent_id', (int)$this->nivel2)
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    public function updatedNivel1(): void { $this->nivel2 = ''; $this->nivel3 = ''; $this->nome = ''; }
    public function updatedNivel2(): void { $this->nivel3 = ''; $this->nome = ''; }
    public function updatedNivel3(): void { $this->nome = ''; }

    // ─── LIVE DUPLICATE ───

    #[Computed]
    public function duplicatas(): array
    {
        $nome = trim($this->nome);
        if (strlen($nome) < 2 || !$this->nivel3) return [];
        return ProdutoBase::where('categoria_id', (int)$this->nivel3)
            ->where('nome', 'like', "%{$nome}%")
            ->orderBy('nome')->limit(6)->pluck('nome')->toArray();
    }

    // ─── FISCAIS ───

    #[Computed]
    public function ncmOpts(): array
    {
        $q = trim($this->ncm_busca);
        if (strlen($q) < 1) return [];
        return Ncm::where('ativo', true)
            ->where(function ($query) use ($q) {
                $query->where('codigo', 'like', "%{$q}%")
                      ->orWhere('descricao', 'like', "%{$q}%");
            })->orderBy('codigo')->limit(20)
            ->get(['id', 'codigo', 'descricao'])->toArray();
    }

    #[Computed]
    public function cfopOpts(): array
    {
        return Cfop::where('ativo', true)
            ->where('aplicacao', 'saida')->orderBy('codigo')
            ->get(['id', 'codigo', 'descricao'])->toArray();
    }

    #[Computed]
    public function csosnOpts(): array
    {
        return IcmsCst::where('ativo', true)
            ->where('regime', 'simples_nacional')->orderBy('codigo')
            ->get(['codigo', 'descricao'])->toArray();
    }

    public function selecionarNcm(int $id): void
    {
        $this->ncm_id = (string)$id;
        $this->ncm_busca = '';
        $ncm = Ncm::find($id);
        if ($ncm) {
            $sugerido = RegraFiscalNcm::sugerirCsosn($ncm->codigo);
            $this->cst_icms = $sugerido;
        }
    }

    // ─── CADASTRAR ───

    public function cadastrar(): void
    {
        $this->validate();
        if (!$this->nivel3) {
            $this->addError('nivel1', 'Selecione uma categoria Nível 3.');
            return;
        }
        $this->nome = ucwords(mb_strtolower(trim(preg_replace('/\s+/', ' ', $this->nome))));
        $slug = Str::slug($this->nome);
        $existe = ProdutoBase::where('slug', $slug)->exists();
        if ($existe) {
            $this->addError('nome', "Produto '{$this->nome}' já existe.");
            return;
        }
        $cat = Categoria::find((int)$this->nivel3);
        $caminho = $cat ? $cat->caminho : '';
        ProdutoBase::create([
            'categoria_id' => (int)$this->nivel3,
            'nome' => $this->nome,
            'slug' => $slug,
            'ativo' => true,
            'ncm_id' => $this->ncm_id ? (int)$this->ncm_id : null,
            'cfop_id' => $this->cfop_id ? (int)$this->cfop_id : null,
            'cest_id' => $this->cest_id ? (int)$this->cest_id : null,
            'origem_mercadoria' => $this->origem_mercadoria ?: null,
            'cst_icms' => $this->cst_icms ?: null,
            'aliquota_icms' => $this->usar_aliquotas && $this->aliquota_icms !== '' ? (float)$this->aliquota_icms : null,
            'aliquota_pis' => $this->usar_aliquotas && $this->aliquota_pis !== '' ? (float)$this->aliquota_pis : null,
            'aliquota_cofins' => $this->usar_aliquotas && $this->aliquota_cofins !== '' ? (float)$this->aliquota_cofins : null,
        ]);
        $this->limparForm();
        $this->toast('Produto base cadastrado com sucesso!');
    }

    private function limparForm(): void
    {
        $this->nome = '';
        $this->ncm_id = null;
        $this->ncm_busca = '';
        $this->cfop_id = null;
        $this->cest_id = null;
        $this->origem_mercadoria = null;
        $this->cst_icms = null;
        $this->usar_aliquotas = false;
        $this->aliquota_icms = null;
        $this->aliquota_pis = null;
        $this->aliquota_cofins = null;
        $this->resetErrorBag('nome');
    }

    // ─── MÉTRICAS ───

    #[Computed]
    public function totalGeral(): int { return ProdutoBase::count(); }

    #[Computed]
    public function totalAtivos(): int { return ProdutoBase::where('ativo', true)->count(); }

    #[Computed]
    public function totalInativos(): int { return ProdutoBase::where('ativo', false)->count(); }

    #[Computed]
    public function totalComNcm(): int { return ProdutoBase::whereNotNull('ncm_id')->count(); }

    // ─── LISTAGEM ───

    public function listagem(): array
    {
        $query = ProdutoBase::with('categoria', 'ncm')->orderBy('nome');
        if (strlen(trim($this->busca)) >= 2) {
            $q = $this->busca;
            $query->where('nome', 'like', "%{$q}%");
        }
        return $query->get()->toArray();
    }

    // ─── ACOES ───

    public function desativar(int $id): void
    {
        $p = ProdutoBase::findOrFail($id);
        $p->update(['ativo' => !$p->ativo]);
        $this->toast($p->ativo ? 'Produto ativado.' : 'Produto desativado.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.produto-base-manager')
            ->layout('components.layouts.app', ['title' => 'Produtos Base · ERP Mercado']);
    }
}

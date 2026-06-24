<?php

namespace App\Livewire;

use App\Models\Atributo;
use App\Models\UnidadeMedida;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;

class AtributoManager extends Component
{
    public string $nome = '';
    public string $slug = '';
    public string $tipo = 'texto';
    public string $unidade_medida_id = '';
    public string $opcoes = '';
    public bool $ativo = true;
    public string $busca = '';
    public ?int $editandoId = null;

    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'in:texto,numero,decimal,booleano,lista,checkbox,radio,data'],
            'unidade_medida_id' => ['nullable', 'exists:unidades_medida,id'],
            'opcoes' => ['nullable', 'string'],
            'ativo' => ['boolean'],
        ];
    }

    public function updatedNome(): void
    {
        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
        if (!$this->editandoId) {
            $this->slug = Str::slug($this->nome);
        }
    }

    #[Computed]
    public function duplicata(): ?string
    {
        if (strlen(trim($this->nome)) < 2) return null;
        $existe = Atributo::where('slug', $this->slug ?: Str::slug($this->nome))
            ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))
            ->exists();
        return $existe ? $this->nome : null;
    }

    #[Computed]
    public function tipos(): array
    {
        return [
            'texto' => 'Texto Livre',
            'numero' => 'Número Inteiro',
            'decimal' => 'Decimal',
            'booleano' => 'Sim / Não (Checkbox)',
            'lista' => 'Lista (Select)',
            'checkbox' => 'Multi-escolha (Checkboxes)',
            'radio' => 'Escola Única (Radio)',
            'data' => 'Data',
        ];
    }

    #[Computed]
    public function listaUnidades(): array
    {
        return UnidadeMedida::orderBy('nome')
            ->get(['id', 'sigla', 'nome'])
            ->map(fn($u) => ['id' => $u->id, 'label' => "{$u->sigla} - {$u->nome}"])
            ->toArray();
    }

    #[Computed]
    public function totalAtivos(): int { return Atributo::where('ativo', true)->count(); }
    #[Computed]
    public function totalInativos(): int { return Atributo::where('ativo', false)->count(); }
    #[Computed]
    public function totalGeral(): int { return Atributo::count(); }
    #[Computed]
    public function totalTipos(): int { return Atributo::distinct('tipo')->count('tipo'); }
    #[Computed]
    public function totalVinculados(): int
    {
        return \App\Models\ProdutoVariacaoAtributo::distinct('atributo_id')->count('atributo_id');
    }

    public function selecionar(int $id): void
    {
        $attr = Atributo::findOrFail($id);
        $this->editandoId = $attr->id;
        $this->nome = $attr->nome;
        $this->slug = $attr->slug;
        $this->tipo = $attr->tipo;
        $this->unidade_medida_id = (string)$attr->unidade_medida_id;
        $this->opcoes = is_array($attr->opcoes) ? implode(', ', $attr->opcoes) : ($attr->opcoes ?? '');
        $this->ativo = $attr->ativo;
    }

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->nome = '';
        $this->slug = '';
        $this->tipo = 'texto';
        $this->unidade_medida_id = '';
        $this->opcoes = '';
        $this->ativo = true;
        $this->resetErrorBag();
    }

    public function cadastrar(): void
    {
        $this->validate();

        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
        if (!$this->slug) $this->slug = Str::slug($this->nome);

        if ($this->duplicata) {
            $this->addError('nome', "Atributo '{$this->nome}' já existe.");
            return;
        }

        $data = [
            'nome' => $this->nome,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'unidade_medida_id' => $this->unidade_medida_id ? (int)$this->unidade_medida_id : null,
            'ativo' => $this->ativo,
        ];

        if (in_array($this->tipo, ['lista', 'checkbox', 'radio']) && trim($this->opcoes)) {
            $arr = array_map('trim', explode(',', $this->opcoes));
            $data['opcoes'] = $arr;
        }

        if ($this->editandoId) {
            Atributo::findOrFail($this->editandoId)->update($data);
            $this->toast('Atributo atualizado com sucesso!');
        } else {
            Atributo::create($data);
            $this->toast('Atributo cadastrado com sucesso!');
        }

        $this->resetForm();
    }

    #[Computed]
    public function estrutura(): array
    {
        $query = Atributo::with('unidadeMedida')->orderBy('nome');
        if (strlen(trim($this->busca)) >= 2) {
            $q = $this->busca;
            $query->where(function ($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('slug', 'like', "%{$q}%");
            });
        }
        return $query->get()->toArray();
    }

    public function excluir(int $id): void
    {
        Atributo::findOrFail($id)->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Atributo excluído.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.atributo-manager')
            ->layout('components.layouts.app', ['title' => 'Atributos · ERP Mercado']);
    }
}

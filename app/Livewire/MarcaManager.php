<?php

namespace App\Livewire;

use App\Models\Marca;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MarcaManager extends Component
{
    public string $nome = '';

    public string $slug = '';

    public string $logo_url = '';

    public bool $ativo = true;

    public string $busca = '';

    public ?int $editandoId = null;

    public string $toastMsg = '';

    public bool $toastShow = false;

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'ativo' => ['boolean'],
        ];
    }

    protected $messages = [
        'nome.required' => 'O nome da marca é obrigatório.',
    ];

    public function updatedNome(): void
    {
        $this->slug = Str::slug($this->nome);

        $this->resetErrorBag(['nome', 'slug']);
    }

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->nome = '';
        $this->slug = '';
        $this->logo_url = '';
        $this->ativo = true;
        $this->resetErrorBag();
    }

    public function selecionar(int $id): void
    {
        $m = Marca::findOrFail($id);
        $this->editandoId = $m->id;
        $this->nome = $m->nome;
        $this->slug = $m->slug;
        $this->logo_url = $m->logo_url ?? '';
        $this->ativo = $m->ativo;
    }

    #[Computed]
    public function duplicata(): ?string
    {
        if (mb_strlen(trim($this->nome)) < 2) {
            return null;
        }

        $slug = $this->slug ?: Str::slug($this->nome);
        $q = Marca::where('slug', $slug);
        if ($this->editandoId) {
            $q->where('id', '!=', $this->editandoId);
        }

        return $q->exists() ? $this->nome : null;
    }

    #[Computed]
    public function sugestoesNome(): array
    {
        $term = trim($this->nome);

        if (mb_strlen($term) < 2) {
            return [];
        }

        return Marca::query()
            ->when($this->editandoId, fn ($query) => $query->where('id', '!=', $this->editandoId))
            ->where(function ($query) use ($term): void {
                $query->where('nome', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', '%'.Str::slug($term).'%');
            })
            ->orderBy('nome')
            ->limit(6)
            ->get(['id', 'nome', 'slug', 'ativo'])
            ->toArray();
    }

    #[Computed]
    public function totalGeral(): int
    {
        return Marca::count();
    }

    #[Computed]
    public function totalAtivos(): int
    {
        return Marca::where('ativo', true)->count();
    }

    #[Computed]
    public function totalInativos(): int
    {
        return Marca::where('ativo', false)->count();
    }

    #[Computed]
    public function totalComLogo(): int
    {
        return Marca::whereNotNull('logo_url')->where('logo_url', '!=', '')->count();
    }

    public function cadastrar(): void
    {
        $this->validate();

        $this->nome = preg_replace('/\s+/u', ' ', trim($this->nome)) ?? '';
        $this->slug = Str::slug($this->nome);

        if ($this->duplicata) {
            $this->addError('slug', "Marca '{$this->nome}' já existe.");

            return;
        }

        $data = [
            'nome' => $this->nome,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url ?: null,
            'ativo' => $this->ativo,
        ];

        if ($this->editandoId) {
            Marca::findOrFail($this->editandoId)->update($data);
            $this->toast('Marca atualizada com sucesso!');
        } else {
            Marca::create($data);
            $this->toast('Marca cadastrada com sucesso!');
        }

        $this->resetForm();
    }

    public function estrutura(): array
    {
        $query = Marca::orderBy('nome');

        if (strlen(trim($this->busca)) >= 2) {
            $q = $this->busca;
            $query->where(function ($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            });
        }

        return $query->limit(200)->get()->toArray();
    }

    public function excluir(int $id): void
    {
        Marca::findOrFail($id)->delete();
        if ($this->editandoId === $id) {
            $this->resetForm();
        }
        $this->toast('Marca excluída.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.marca-manager')
            ->layout('components.layouts.app', ['title' => 'Marcas · ERP Mercado']);
    }
}

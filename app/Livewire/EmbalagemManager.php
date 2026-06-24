<?php

namespace App\Livewire;

use App\Models\Embalagem;
use Livewire\Component;
use Livewire\Attributes\Computed;

class EmbalagemManager extends Component
{
    public string $nome = '';
    public string $sigla = '';
    public bool $ativo = true;
    public string $busca = '';
    public ?int $editandoId = null;

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function updatedNome(): void
    {
        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
    }

    public function updatedSigla(): void
    {
        $this->sigla = mb_strtoupper(trim($this->sigla));
    }

    #[Computed]
    public function duplicata(): ?string
    {
        if (strlen(trim($this->nome)) < 2) return null;
        $q = Embalagem::where('nome', $this->nome);
        if ($this->editandoId) $q->where('id', '!=', $this->editandoId);
        return $q->exists() ? $this->nome : null;
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:80'],
            'sigla' => ['nullable', 'string', 'max:20'],
            'ativo' => ['boolean'],
        ];
    }

    protected $messages = [
        'nome.required' => 'O nome da embalagem é obrigatório.',
    ];

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->nome = '';
        $this->sigla = '';
        $this->ativo = true;
        $this->resetErrorBag();
    }

    public function selecionar(int $id): void
    {
        $e = Embalagem::findOrFail($id);
        $this->editandoId = $e->id;
        $this->nome = $e->nome;
        $this->sigla = $e->sigla ?? '';
        $this->ativo = $e->ativo;
    }

    #[Computed]
    public function totalGeral(): int { return Embalagem::count(); }

    #[Computed]
    public function totalAtivos(): int { return Embalagem::where('ativo', true)->count(); }

    #[Computed]
    public function totalInativos(): int { return Embalagem::where('ativo', false)->count(); }

    #[Computed]
    public function totalComSigla(): int { return Embalagem::whereNotNull('sigla')->where('sigla', '!=', '')->count(); }

    public function cadastrar(): void
    {
        $this->validate();

        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
        $this->sigla = $this->sigla ? mb_strtoupper(trim($this->sigla)) : '';

        if ($this->duplicata) {
            $this->addError('nome', "Embalagem '{$this->nome}' já existe.");
            return;
        }

        $data = [
            'nome' => $this->nome,
            'sigla' => $this->sigla ?: null,
            'ativo' => $this->ativo,
        ];

        if ($this->editandoId) {
            Embalagem::findOrFail($this->editandoId)->update($data);
            $this->toast('Embalagem atualizada com sucesso!');
        } else {
            Embalagem::create($data);
            $this->toast('Embalagem cadastrada com sucesso!');
        }

        $this->resetForm();
    }

    public function estrutura(): array
    {
        $query = Embalagem::orderBy('nome');

        if (strlen(trim($this->busca)) >= 2) {
            $q = $this->busca;
            $query->where(function ($w) use ($q) {
                $w->where('nome', 'like', "%{$q}%")
                  ->orWhere('sigla', 'like', "%{$q}%");
            });
        }

        return $query->get()->toArray();
    }

    public function excluir(int $id): void
    {
        Embalagem::findOrFail($id)->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Embalagem excluída.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.embalagem-manager')
            ->layout('components.layouts.app', ['title' => 'Embalagens · ERP Mercado']);
    }
}

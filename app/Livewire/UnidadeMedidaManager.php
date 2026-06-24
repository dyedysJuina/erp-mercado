<?php

namespace App\Livewire;

use App\Models\UnidadeMedida;
use Livewire\Component;
use Livewire\Attributes\Computed;

class UnidadeMedidaManager extends Component
{
    public string $sigla = '';
    public string $nome = '';
    public bool $permite_decimal = false;
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
        $q = UnidadeMedida::where(function ($w) {
            $w->where('sigla', $this->sigla)->orWhere('nome', $this->nome);
        });
        if ($this->editandoId) $q->where('id', '!=', $this->editandoId);
        return $q->exists() ? $this->nome : null;
    }

    protected function rules(): array
    {
        return [
            'sigla' => ['required', 'string', 'max:10'],
            'nome' => ['required', 'string', 'max:80'],
            'permite_decimal' => ['boolean'],
        ];
    }

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->sigla = '';
        $this->nome = '';
        $this->permite_decimal = false;
        $this->resetErrorBag();
    }

    public function selecionar(int $id): void
    {
        $u = UnidadeMedida::findOrFail($id);
        $this->editandoId = $u->id;
        $this->sigla = $u->sigla;
        $this->nome = $u->nome;
        $this->permite_decimal = $u->permite_decimal;
    }

    #[Computed]
    public function totalGeral(): int { return UnidadeMedida::count(); }

    #[Computed]
    public function totalDecimal(): int { return UnidadeMedida::where('permite_decimal', true)->count(); }

    #[Computed]
    public function totalInteiro(): int { return UnidadeMedida::where('permite_decimal', false)->count(); }

    #[Computed]
    public function totalSiglas(): int { return UnidadeMedida::distinct('sigla')->count('sigla'); }

    public function cadastrar(): void
    {
        $this->validate();

        $this->sigla = mb_strtoupper(trim($this->sigla));
        $this->nome = ucwords(mb_strtolower(trim($this->nome)));

        if ($this->duplicata) {
            $this->addError('sigla', "Unidade '{$this->sigla}' já existe.");
            return;
        }

        $data = [
            'sigla' => $this->sigla,
            'nome' => $this->nome,
            'permite_decimal' => $this->permite_decimal,
        ];

        if ($this->editandoId) {
            UnidadeMedida::findOrFail($this->editandoId)->update($data);
            $this->toast('Unidade atualizada com sucesso!');
        } else {
            UnidadeMedida::create($data);
            $this->toast('Unidade cadastrada com sucesso!');
        }

        $this->resetForm();
    }

    public function estrutura(): array
    {
        $query = UnidadeMedida::orderBy('nome');

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
        UnidadeMedida::findOrFail($id)->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Unidade excluída.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.unidade-medida-manager')
            ->layout('components.layouts.app', ['title' => 'Unidades de Medida · ERP Mercado']);
    }
}

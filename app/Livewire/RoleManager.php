<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManager extends Component
{
    public string $toastMsg = '';
    public bool $toastShow = false;

    public ?int $editandoId = null;
    public string $nome = '';
    public array $permissoesSelecionadas = [];

    public array $gruposPermissoes = [
        'dashboard' => ['label' => 'Dashboard', 'perms' => ['dashboard']],
        'cadastros' => ['label' => 'Cadastros', 'perms' => ['categorias', 'atributos', 'unidades', 'embalagens', 'marcas', 'produtos-base', 'variacoes', 'clientes']],
        'compras' => ['label' => 'Compras', 'perms' => ['fornecedores', 'compras', 'pedidos']],
        'estoque' => ['label' => 'Estoque', 'perms' => ['estoque', 'lotes', 'precos']],
        'financeiro' => ['label' => 'Financeiro', 'perms' => ['pdv', 'financeiro', 'relatorios']],
        'admin' => ['label' => 'Administrativo', 'perms' => ['lojas', 'admin.temas', 'admin.fiscal']],
        'seguranca' => ['label' => 'Segurança', 'perms' => ['usuarios', 'papeis']],
    ];

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->nome = '';
        $this->permissoesSelecionadas = [];
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        return ['nome' => ['required', 'string', 'max:255']];
    }

    public function selecionar(int $id): void
    {
        $r = Role::with('permissions')->findOrFail($id);
        $this->editandoId = $r->id;
        $this->nome = $r->name;
        $this->permissoesSelecionadas = $r->permissions->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    #[Computed]
    public function lista(): array
    {
        return Role::with('permissions')->orderBy('name')->get()->toArray();
    }

    #[Computed]
    public function totalUsuariosPorPapel(): array
    {
        $result = [];
        foreach (Role::all() as $role) {
            $result[$role->id] = $role->users()->count();
        }
        return $result;
    }

    public function alternarGrupo(string $grupo): void
    {
        $idsGrupo = collect($this->gruposPermissoes[$grupo]['perms'])
            ->map(fn($name) => Permission::where('name', $name)->value('id'))
            ->filter()
            ->map(fn($id) => (string)$id)
            ->values()
            ->toArray();

        $todasSelecionadas = collect($idsGrupo)->every(fn($id) => in_array($id, $this->permissoesSelecionadas));

        if ($todasSelecionadas) {
            $this->permissoesSelecionadas = array_values(array_diff($this->permissoesSelecionadas, $idsGrupo));
        } else {
            $this->permissoesSelecionadas = array_values(array_unique(array_merge($this->permissoesSelecionadas, $idsGrupo)));
        }
    }

    public function grupoCompleto(string $grupo): bool
    {
        $idsGrupo = collect($this->gruposPermissoes[$grupo]['perms'])
            ->map(fn($name) => Permission::where('name', $name)->value('id'))
            ->filter()
            ->map(fn($id) => (string)$id)
            ->values()
            ->toArray();

        if (empty($idsGrupo)) return false;

        return collect($idsGrupo)->every(fn($id) => in_array($id, $this->permissoesSelecionadas));
    }

    public function grupoParcial(string $grupo): bool
    {
        $idsGrupo = collect($this->gruposPermissoes[$grupo]['perms'])
            ->map(fn($name) => Permission::where('name', $name)->value('id'))
            ->filter()
            ->map(fn($id) => (string)$id)
            ->values()
            ->toArray();

        if (empty($idsGrupo)) return false;

        $algum = collect($idsGrupo)->some(fn($id) => in_array($id, $this->permissoesSelecionadas));
        $todos = collect($idsGrupo)->every(fn($id) => in_array($id, $this->permissoesSelecionadas));
        return $algum && !$todos;
    }

    public function salvar(): void
    {
        $this->validate();

        if ($this->editandoId) {
            $role = Role::findOrFail($this->editandoId);
            $role->update(['name' => $this->nome]);
            $this->toast('Papel atualizado!');
        } else {
            $role = Role::create(['name' => $this->nome, 'guard_name' => 'web']);
            $this->toast('Papel criado!');
        }

        $role->syncPermissions(
            collect($this->permissoesSelecionadas)->filter()->map(fn($id) => (int)$id)->values()->toArray()
        );

        $this->resetForm();
    }

    public function excluir(int $id): void
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Admin') { $this->addError('nome', 'Não pode excluir o papel Admin.'); return; }
        $role->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Papel excluído.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.role-manager')
            ->layout('components.layouts.app', ['title' => 'Papéis · ERP Mercado']);
    }
}

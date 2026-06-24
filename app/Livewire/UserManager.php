<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Role;

class UserManager extends Component
{
    public string $busca = '';
    public ?int $editandoId = null;

    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $loja_id = '';
    public bool $ativo = true;
    public array $rolesSelecionados = [];

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->loja_id = '';
        $this->ativo = true;
        $this->rolesSelecionados = [];
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'ativo' => ['boolean'],
        ];
        if (!$this->editandoId) {
            $rules['password'] = ['required', 'string', 'min:6'];
        }
        return $rules;
    }

    protected $messages = [
        'name.required' => 'O nome é obrigatório.',
        'email.required' => 'O email é obrigatório.',
        'email.email' => 'Informe um email válido.',
        'password.required' => 'A senha é obrigatória.',
        'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
    ];

    public function selecionar(int $id): void
    {
        $u = User::with('roles')->findOrFail($id);
        $this->editandoId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';
        $this->loja_id = (string)($u->loja_id ?? '');
        $this->ativo = $u->ativo;
        $this->rolesSelecionados = $u->roles->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    #[Computed]
    public function totalGeral(): int { return User::count(); }
    #[Computed]
    public function totalAtivos(): int { return User::where('ativo', true)->count(); }
    #[Computed]
    public function totalAdmins(): int { return User::role('Admin')->count(); }

    #[Computed]
    public function permissoesEfetivas(): array
    {
        if (!$this->editandoId) return [];
        $user = User::with('roles.permissions')->find($this->editandoId);
        if (!$user) return [];
        $permIds = [];
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $perm) {
                $permIds[$perm->id] = $perm->name;
            }
        }
        return $permIds;
    }

    #[Computed]
    public function lista(): array
    {
        $q = User::with('roles')->orderBy('name');
        if (strlen(trim($this->busca)) >= 2) {
            $s = $this->busca;
            $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
            });
        }
        return $q->get()->toArray();
    }

    #[Computed]
    public function rolesDisponiveis(): array
    {
        return Role::orderBy('name')->get(['id', 'name'])->toArray();
    }

    #[Computed]
    public function lojasDisponiveis(): array
    {
        return \App\Models\Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function salvar(): void
    {
        $this->validate();

        $exists = User::where('email', $this->email)
            ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))
            ->exists();
        if ($exists) { $this->addError('email', 'Email já cadastrado.'); return; }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'ativo' => $this->ativo,
            'loja_id' => $this->loja_id ? (int)$this->loja_id : null,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->editandoId) {
            $user = User::findOrFail($this->editandoId);
            $user->update($data);
            $this->toast('Usuário atualizado!');
        } else {
            if (!$this->password) { $this->addError('password', 'Senha obrigatória.'); return; }
            $data['password'] = bcrypt($this->password);
            $user = User::create($data);
            $this->toast('Usuário cadastrado!');
        }

        $user->syncRoles(
            collect($this->rolesSelecionados)->filter()->map(fn($id) => (int)$id)->values()->toArray()
        );

        $this->resetForm();
    }

    public function excluir(int $id): void
    {
        if ($id === auth()->id()) { $this->addError('exclusao', 'Você não pode excluir a si mesmo.'); return; }
        User::findOrFail($id)->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->toast('Usuário excluído.');
    }

    public function desativar(int $id): void
    {
        $u = User::findOrFail($id);
        $u->update(['ativo' => !$u->ativo]);
        $this->toast($u->ativo ? 'Usuário ativado.' : 'Usuário desativado.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.user-manager')
            ->layout('components.layouts.app', ['title' => 'Usuários · ERP Mercado']);
    }
}

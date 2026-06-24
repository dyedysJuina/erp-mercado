<?php

namespace App\Livewire;

use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\ClientesEndereco;
use App\Models\Estado;
use App\Support\BrazilianNumber;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ClienteManager extends Component
{
    use WithPagination;
    public string $viewState = 'list';

    public string $busca = '';
    public ?int $editandoId = null;
    public string $modo = 'create';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $nome = '';
    public string $email = '';
    public string $cpf = '';
    public string $whatsapp = '';
    public string $data_nascimento = '';
    public bool $ativo = true;
    public bool $aceita_marketing = false;

    public array $enderecos = [];
    public array $gruposSelecionados = [];

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->modo = 'create';
        $this->nome = '';
        $this->email = '';
        $this->cpf = '';
        $this->whatsapp = '';
        $this->data_nascimento = '';
        $this->ativo = true;
        $this->aceita_marketing = false;
        $this->enderecos = [];
        $this->gruposSelecionados = [];
        $this->resetErrorBag();
    }

    public function voltarLista(): void
    {
        $this->viewState = 'list';
        $this->resetForm();
    }

    public function novo(): void
    {
        $this->viewState = 'create';
        $this->resetForm();
    }

    public function adicionarEndereco(): void
    {
        $this->enderecos[] = [
            'id' => null, 'titulo' => 'Principal',
            'cep' => '', 'logradouro' => '', 'numero' => '',
            'bairro' => '', 'complemento' => '',
            'cidade_id' => '', 'estado_id' => '',
            'principal' => count($this->enderecos) === 0,
        ];
    }

    public function removerEndereco(int $idx): void
    {
        if (isset($this->enderecos[$idx])) {
            if ($this->enderecos[$idx]['id'])
                ClientesEndereco::find($this->enderecos[$idx]['id'])?->delete();
            unset($this->enderecos[$idx]);
            $this->enderecos = array_values($this->enderecos);
        }
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'data_nascimento' => ['nullable', 'date'],
            'ativo' => ['boolean'],
            'aceita_marketing' => ['boolean'],
        ];
    }

    protected $messages = [
        'nome.required' => 'O nome do cliente é obrigatório.',
        'email.email' => 'Informe um email válido.',
    ];

    public function updatedCpf(): void
    {
        $this->resetErrorBag('cpf');
        $cpf = preg_replace('/\D/', '', $this->cpf ?? '');
        if (strlen($cpf) !== 11) return;
        if (!BrazilianNumber::validarCpf($cpf))
            $this->addError('cpf', 'CPF inválido.');
    }

    public function selecionar(int $id): void
    {
        $c = Cliente::with(['enderecos.cidade', 'grupos'])->findOrFail($id);
        $this->viewState = 'detail';
        $this->editandoId = $c->id;
        $this->modo = 'edit';
        $this->nome = $c->nome;
        $this->email = $c->email ?? '';
        $this->cpf = $c->cpf ?? '';
        $this->whatsapp = $c->whatsapp ?? '';
        $this->data_nascimento = $c->data_nascimento?->format('Y-m-d') ?? '';
        $this->ativo = $c->ativo;
        $this->aceita_marketing = $c->aceita_marketing;
        $this->enderecos = $c->enderecos->map(fn($e) => [
            'id' => $e->id, 'titulo' => $e->titulo,
            'cep' => $e->cep ?? '', 'logradouro' => $e->logradouro,
            'numero' => $e->numero ?? '', 'bairro' => $e->bairro,
            'complemento' => $e->complemento ?? '',
            'cidade_id' => (string)$e->cidade_id, 'estado_id' => (string)($e->cidade?->estado_id ?? ''),
            'principal' => $e->principal,
        ])->toArray();
        $this->gruposSelecionados = $c->grupos->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    #[Computed]
    public function totalGeral(): int { return Cliente::count(); }
    #[Computed]
    public function totalAtivos(): int { return Cliente::where('ativo', true)->count(); }
    #[Computed]
    public function totalInativos(): int { return Cliente::where('ativo', false)->count(); }
    #[Computed]
    public function totalComCpf(): int { return Cliente::whereNotNull('cpf')->count(); }

    #[Computed]
    public function estados(): array
    {
        return \App\Models\Estado::orderBy('uf')->get(['id', 'uf', 'nome'])->toArray();
    }

    #[Computed]
    public function comprasCliente(): array
    {
        if (!$this->editandoId) return ['pdv' => [], 'pedidos' => []];
        $pdv = \App\Models\PdvVenda::where('cliente_id', $this->editandoId)
            ->with('pagamentos')->orderBy('created_at', 'desc')->limit(20)->get()->toArray();
        $pedidos = \App\Models\Pedido::where('cliente_id', $this->editandoId)
            ->orderBy('created_at', 'desc')->limit(20)->get()->toArray();
        return ['pdv' => $pdv, 'pedidos' => $pedidos];
    }

    #[Computed]
    public function financeiroCliente(): array
    {
        if (!$this->editandoId) return [];
        $pdvIds = \App\Models\PdvVenda::where('cliente_id', $this->editandoId)->pluck('id');
        $pedidoIds = \App\Models\Pedido::where('cliente_id', $this->editandoId)->pluck('id');
        return \App\Models\FinanceiroLancamento::where(function ($q) use ($pdvIds, $pedidoIds) {
            $q->whereIn('pdv_venda_id', $pdvIds)->orWhereIn('pedido_id', $pedidoIds);
        })->orderBy('created_at', 'desc')->limit(50)->get()->toArray();
    }

    #[Computed]
    public function gruposDisponiveis(): array
    {
        return \App\Models\CrmGruposCliente::where('ativo', true)->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function pontosCliente(): array
    {
        if (!$this->editandoId) return ['total' => 0, 'historico' => []];
        $historico = \App\Models\CrmPontosMovimentacao::where('cliente_id', $this->editandoId)
            ->orderBy('created_at', 'desc')->limit(50)->get()->toArray();
        $total = \App\Models\CrmPontosMovimentacao::where('cliente_id', $this->editandoId)
            ->where('tipo', 'credito')->sum('pontos')
            - \App\Models\CrmPontosMovimentacao::where('cliente_id', $this->editandoId)
                ->whereIn('tipo', ['debito', 'expiracao'])->sum('pontos');
        return ['total' => max(0, $total), 'historico' => $historico];
    }

    public function salvar(): void
    {
        $this->validate();

        $this->nome = ucwords(mb_strtolower(trim($this->nome)));
        if ($this->email) $this->email = mb_strtolower(trim($this->email));

        if ($this->email) {
            $exists = Cliente::where('email', $this->email)
                ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))->exists();
            if ($exists) { $this->addError('email', 'Email já cadastrado.'); return; }
        }

        if ($this->cpf) {
            $cpfLimpo = preg_replace('/\D/', '', $this->cpf);
            $this->cpf = $cpfLimpo;
            if (!BrazilianNumber::validarCpf($cpfLimpo)) { $this->addError('cpf', 'CPF inválido.'); return; }
            $exists = Cliente::where('cpf', $cpfLimpo)
                ->when($this->editandoId, fn($q) => $q->where('id', '!=', $this->editandoId))->exists();
            if ($exists) { $this->addError('cpf', 'CPF já cadastrado.'); return; }
        }

        $data = [
            'nome' => $this->nome, 'email' => $this->email ?: null,
            'cpf' => $this->cpf ?: null, 'whatsapp' => $this->whatsapp ?: null,
            'data_nascimento' => $this->data_nascimento ?: null,
            'ativo' => $this->ativo, 'aceita_marketing' => $this->aceita_marketing,
        ];

        if ($this->modo === 'create') {
            $cliente = Cliente::create($data);
            $this->toast('Cliente cadastrado com sucesso!');
        } else {
            $cliente = Cliente::findOrFail($this->editandoId);
            $cliente->update($data);
            $this->toast('Cliente atualizado com sucesso!');
            ClientesEndereco::where('cliente_id', $cliente->id)->delete();
        }

        $cliente->grupos()->sync(
            collect($this->gruposSelecionados)->filter()->map(fn($id) => (int)$id)->values()->toArray()
        );

        foreach ($this->enderecos as $end) {
            if (trim($end['logradouro'] ?? '') && trim($end['cidade_id'] ?? '')) {
                ClientesEndereco::create([
                    'cliente_id' => $cliente->id, 'titulo' => $end['titulo'] ?: 'Principal',
                    'cidade_id' => (int)$end['cidade_id'], 'cep' => $end['cep'] ?: null,
                    'bairro' => $end['bairro'] ?? '', 'logradouro' => $end['logradouro'],
                    'numero' => $end['numero'] ?: null, 'complemento' => $end['complemento'] ?: null,
                    'principal' => $end['principal'] ?? false,
                ]);
            }
        }

        $this->resetForm();
        $this->viewState = 'list';
    }

    public function excluir(int $id): void
    {
        Cliente::findOrFail($id)->delete();
        if ($this->editandoId === $id) $this->resetForm();
        $this->viewState = 'list';
        $this->toast('Cliente excluído.');
    }

    public function desativar(int $id): void
    {
        $c = Cliente::findOrFail($id);
        $c->update(['ativo' => !$c->ativo]);
        $this->toast($c->ativo ? 'Cliente ativado.' : 'Cliente desativado.');
    }

    public function lista()
    {
        $q = Cliente::withCount('enderecos')->orderBy('nome');
        if (strlen(trim($this->busca)) >= 2) {
            $s = $this->busca;
            $q->where(function ($w) use ($s) {
                $w->where('nome', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('cpf', 'like', "%{$s}%")
                  ->orWhere('whatsapp', 'like', "%{$s}%");
            });
        }
        return $q->paginate(25);
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.cliente-manager')
            ->layout('components.layouts.app', ['title' => 'Clientes · ERP Mercado']);
    }
}

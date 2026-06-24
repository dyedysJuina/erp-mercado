<?php

namespace App\Livewire;

use App\Models\Fornecedor;
use App\Models\FornecedorContato;
use App\Models\Cidade;
use App\Models\Estado;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

class FornecedorManager extends Component
{
    public string $busca = '';
    public ?int $editandoId = null;
    public string $modo = 'create';

    public string $toastMsg = '';
    public bool $toastShow = false;

    // Identificação
    public string $tipo_pessoa = 'juridica';
    public string $razao_social = '';
    public string $nome_fantasia = '';
    public string $cnpj = '';
    public string $cpf = '';
    public string $inscricao_estadual = '';

    // Contato
    public string $telefone_fixo = '';
    public string $telefone_celular = '';
    public string $email = '';

    // Endereço
    public string $cep = '';
    public string $logradouro = '';
    public string $numero = '';
    public string $bairro = '';
    public string $complemento = '';
    public string $cidade_id = '';
    public string $estado_id = '';

    // Contatos
    public array $contatos = [];
    public bool $ativo = true;

    protected function rules(): array
    {
        $rules = [
            'tipo_pessoa' => ['required', 'in:juridica,fisica'],
            'razao_social' => ['required', 'string', 'max:255'],
            'nome_fantasia' => ['nullable', 'string', 'max:255'],
            'inscricao_estadual' => ['nullable', 'string', 'max:30'],
            'telefone_fixo' => ['nullable', 'string', 'max:20'],
            'telefone_celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'cep' => ['nullable', 'string', 'max:10'],
            'logradouro' => ['nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:20'],
            'bairro' => ['nullable', 'string', 'max:150'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'cidade_id' => ['nullable', 'exists:cidades,id'],
            'ativo' => ['boolean'],
            'contatos' => ['array', 'max:10'],
        ];

        if ($this->tipo_pessoa === 'juridica') {
            $rules['cnpj'] = ['required', 'string', 'max:18'];
        } else {
            $rules['cpf'] = ['required', 'string', 'max:14'];
        }

        return $rules;
    }

    public function getCidades($search, $estadoId = null): array
    {
        $q = Cidade::with('estado');
        if (strlen(trim($search)) >= 2) {
            $q->where('nome', 'like', "%{$search}%");
        }
        if ($estadoId) $q->where('estado_id', (int)$estadoId);
        return $q->orderBy('nome')->limit(50)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'nome_completo' => $c->nome . '/' . $c->estado->uf])
            ->toArray();
    }

    public function getCidadesPorEstado($estadoId): array
    {
        if (!$estadoId) return [];
        return Cidade::with('estado')
            ->where('estado_id', (int)$estadoId)
            ->orderBy('nome')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'nome_completo' => $c->nome . '/' . $c->estado->uf])
            ->toArray();
    }

    #[Computed]
    public function estados(): array
    {
        return Estado::orderBy('nome')->get(['id', 'nome', 'uf'])->toArray();
    }

    #[Computed]
    public function lista(): array
    {
        $q = Fornecedor::with(['cidade.estado', 'contatos'])->orderBy('razao_social');
        $f = trim($this->busca);
        if (strlen($f) >= 2) {
            $q->where(function ($w) use ($f) {
                $w->where('razao_social', 'like', "%{$f}%")
                  ->orWhere('nome_fantasia', 'like', "%{$f}%")
                  ->orWhere('cnpj_cpf', 'like', "%{$f}%");
            });
        }
        return $q->get()->toArray();
    }

    #[Computed]
    public function totalGeral(): int { return Fornecedor::count(); }

    #[Computed]
    public function totalAtivos(): int { return Fornecedor::where('ativo', true)->count(); }

    public function selecionar(int $id): void
    {
        $f = Fornecedor::with('contatos')->findOrFail($id);
        $this->editandoId = $f->id;
        $this->modo = 'edit';
        $this->tipo_pessoa = $f->tipo_pessoa;
        $this->razao_social = $f->razao_social;
        $this->nome_fantasia = $f->nome_fantasia ?? '';

        $isJuridica = $f->tipo_pessoa === 'juridica';
        $this->cnpj = $isJuridica ? ($f->cnpj_cpf ?? '') : '';
        $this->cpf = !$isJuridica ? ($f->cnpj_cpf ?? '') : '';
        $this->inscricao_estadual = $f->inscricao_estadual ?? '';
        $this->telefone_fixo = $f->telefone ?? '';
        $this->telefone_celular = $f->telefone ?? '';
        $this->email = $f->email ?? '';
        $this->cep = $f->cep ?? '';
        $this->logradouro = $f->logradouro ?? '';
        $this->numero = $f->numero ?? '';
        $this->bairro = $f->bairro ?? '';
        $this->complemento = $f->complemento ?? '';
        $this->cidade_id = (string)$f->cidade_id;
        $this->estado_id = $f->cidade ? (string)$f->cidade->estado_id : '';
        $this->ativo = $f->ativo;

        $this->contatos = $f->contatos->map(fn($c) => [
            'nome' => $c->nome ?? '', 'cargo' => $c->cargo ?? '',
            'telefone' => $c->telefone ?? '', 'email' => $c->email ?? '',
            'principal' => $c->principal,
        ])->toArray();
    }

    public function novo(): void
    {
        $this->editandoId = null;
        $this->modo = 'create';
        $this->tipo_pessoa = 'juridica';
        $this->razao_social = ''; $this->nome_fantasia = '';
        $this->cnpj = ''; $this->cpf = ''; $this->inscricao_estadual = '';
        $this->telefone_fixo = ''; $this->telefone_celular = ''; $this->email = '';
        $this->cep = ''; $this->logradouro = ''; $this->numero = '';
        $this->bairro = ''; $this->complemento = ''; $this->cidade_id = '';
        $this->estado_id = '';
        $this->contatos = []; $this->ativo = true;
        $this->resetErrorBag();
    }

    public function updatedTipoPessoa(): void
    {
        $this->cnpj = '';
        $this->cpf = '';
        $this->inscricao_estadual = '';
    }

    public function adicionarContato(): void
    {
        $this->contatos[] = [
            'nome' => '', 'cargo' => '', 'telefone' => '', 'email' => '',
            'principal' => count($this->contatos) === 0,
        ];
    }

    public function removerContato(int $idx): void
    {
        if (isset($this->contatos[$idx])) {
            unset($this->contatos[$idx]);
            $this->contatos = array_values($this->contatos);
        }
    }

    public function salvar(): void
    {
        $this->validate();

        $cnpjCpf = $this->tipo_pessoa === 'juridica' ? $this->cnpj : $this->cpf;

        $dup = Fornecedor::where('cnpj_cpf', $cnpjCpf)
            ->when($this->modo === 'edit', fn($q) => $q->where('id', '!=', $this->editandoId))
            ->exists();
        if ($dup) {
            $field = $this->tipo_pessoa === 'juridica' ? 'cnpj' : 'cpf';
            $this->addError($field, 'Já cadastrado.');
            return;
        }

        $data = [
            'tipo_pessoa' => $this->tipo_pessoa,
            'razao_social' => $this->razao_social,
            'nome_fantasia' => $this->nome_fantasia ?: null,
            'cnpj_cpf' => $cnpjCpf,
            'inscricao_estadual' => $this->inscricao_estadual ?: null,
            'telefone' => $this->telefone_celular ?: ($this->telefone_fixo ?: null),
            'email' => $this->email ?: null,
            'cep' => $this->cep ?: null,
            'logradouro' => $this->logradouro ?: null,
            'numero' => $this->numero ?: null,
            'bairro' => $this->bairro ?: null,
            'complemento' => $this->complemento ?: null,
            'cidade_id' => $this->cidade_id ? (int)$this->cidade_id : null,
            'ativo' => $this->ativo,
        ];

        if ($this->modo === 'create') {
            $forn = Fornecedor::create($data);
            $msg = 'cadastrado';
        } else {
            $forn = Fornecedor::findOrFail($this->editandoId);
            $forn->update($data);
            FornecedorContato::where('fornecedor_id', $forn->id)->delete();
            $msg = 'atualizado';
        }

        foreach ($this->contatos as $ct) {
            if (trim($ct['nome'] ?? '')) {
                FornecedorContato::create([
                    'fornecedor_id' => $forn->id, 'nome' => $ct['nome'],
                    'cargo' => $ct['cargo'] ?: null,
                    'telefone' => $ct['telefone'] ?: null,
                    'email' => $ct['email'] ?: null,
                    'principal' => $ct['principal'] ?? false,
                ]);
            }
        }

        $this->selecionar($forn->id);
        $this->toast("Fornecedor \"{$forn->razao_social}\" {$msg} com sucesso!");
    }

    public function excluir(int $id): void
    {
        $compraCount = DB::table('compras_pedidos')->where('fornecedor_id', $id)->count();
        if ($compraCount > 0) {
            $this->addError('fornecedor', 'Não é possível excluir: fornecedor possui pedidos de compra vinculados.');
            return;
        }
        $f = Fornecedor::findOrFail($id);
        FornecedorContato::where('fornecedor_id', $id)->delete();
        $f->delete();
        $this->novo();
        $this->toast('Fornecedor excluído.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.fornecedor-manager')
            ->layout('components.layouts.app', ['title' => 'Fornecedores · ERP Mercado']);
    }
}

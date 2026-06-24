<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\TabelaPreco;
use App\Models\Cidade;
use App\Models\Estado;
use Livewire\Component;
use Livewire\Attributes\Computed;

class LojaManager extends Component
{
    public string $busca = '';
    public ?int $editandoId = null;
    public string $modo = 'create';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public $empresa_id = 1;
    public $nome = '';
    public $nome_fantasia = '';
    public $codigo_interno = '';
    public $cnpj = '';
    public $inscricao_estadual = '';
    public $tipo = 'matriz';
    public $telefone = '';
    public $email = '';
    public $estado_id = '';
    public $cidade_id = '';
    public $cep = '';
    public $bairro = '';
    public $logradouro = '';
    public $numero = '';
    public $complemento = '';
    public $latitude = '';
    public $longitude = '';
    public $status_operacional = 'aberta';
    public $ativo = true;
    public $tabela_preco_id = '';

    #[Computed]
    public function estados(): array
    {
        return Estado::orderBy('nome')->get(['id', 'nome', 'uf'])->toArray();
    }

    public function cidadesPorEstado(): array
    {
        if (!$this->estado_id) return [];
        return Cidade::where('estado_id', (int)$this->estado_id)
            ->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function updatedEstadoId(): void
    {
        $this->cidade_id = '';
    }

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'tipo' => ['required', 'in:matriz,filial'],
            'cidade_id' => ['required', 'exists:cidades,id'],
            'status_operacional' => ['required', 'in:aberta,fechada,manutencao'],
            'ativo' => ['boolean'],
        ];
    }

    #[Computed]
    public function totalGeral(): int { return Loja::count(); }

    #[Computed]
    public function totalMatriz(): int { return Loja::where('tipo', 'matriz')->count(); }

    #[Computed]
    public function totalFilial(): int { return Loja::where('tipo', 'filial')->count(); }

    #[Computed]
    public function totalAtivas(): int { return Loja::where('ativo', true)->count(); }

    #[Computed]
    public function totalInativas(): int { return Loja::where('ativo', false)->count(); }

    public function lista(): array
    {
        $q = Loja::with('cidade.estado')->orderBy('nome');
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('nome', 'like', "%{$this->busca}%");
        }
        return $q->get()->toArray();
    }

    #[Computed]
    public function tabelasPreco(): array
    {
        return TabelaPreco::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function selecionar(int $id): void
    {
        $l = Loja::findOrFail($id);
        $this->editandoId = $l->id; $this->modo = 'edit';
        $this->empresa_id = $l->empresa_id;
        $this->nome = $l->nome; $this->nome_fantasia = $l->nome_fantasia ?? '';
        $this->codigo_interno = $l->codigo_interno ?? '';
        $this->cnpj = $l->cnpj ?? ''; $this->inscricao_estadual = $l->inscricao_estadual ?? ''; $this->tipo = $l->tipo;
        $this->telefone = $l->telefone ?? ''; $this->email = $l->email ?? '';
        $this->cidade_id = (string)$l->cidade_id;
        $this->estado_id = (string)($l->cidade?->estado_id ?? '');
        $this->cep = $l->cep ?? ''; $this->bairro = $l->bairro ?? '';
        $this->logradouro = $l->logradouro ?? ''; $this->numero = $l->numero ?? '';
        $this->complemento = $l->complemento ?? '';
        $this->latitude = $l->latitude ?? ''; $this->longitude = $l->longitude ?? '';
        $this->status_operacional = $l->status_operacional;
        $this->ativo = $l->ativo;
        $this->tabela_preco_id = (string)$l->tabela_preco_id;
    }

    public function novo(): void
    {
        $this->editandoId = null; $this->modo = 'create';
        $this->empresa_id = 1;
        $this->nome = ''; $this->nome_fantasia = ''; $this->codigo_interno = '';
        $this->cnpj = ''; $this->inscricao_estadual = '';
        $this->tipo = 'matriz'; $this->telefone = ''; $this->email = '';
        $this->estado_id = ''; $this->cidade_id = ''; $this->cep = ''; $this->bairro = '';
        $this->logradouro = ''; $this->numero = ''; $this->complemento = '';
        $this->latitude = ''; $this->longitude = '';
        $this->status_operacional = 'aberta'; $this->ativo = true;
        $this->tabela_preco_id = '';
        $this->resetErrorBag();
    }

    public function salvar(): void
    {
        $this->validate();
        $data = [
            'empresa_id' => 1, 'nome' => $this->nome,
            'nome_fantasia' => $this->nome_fantasia ?: null,
            'codigo_interno' => $this->codigo_interno ?: null,
            'cnpj' => $this->cnpj ?: null,
            'inscricao_estadual' => $this->inscricao_estadual ?: null, 'tipo' => $this->tipo,
            'telefone' => $this->telefone ?: null, 'email' => $this->email ?: null,
            'cidade_id' => (int)$this->cidade_id,
            'cep' => $this->cep ?: null, 'bairro' => $this->bairro ?: null,
            'logradouro' => $this->logradouro ?: null,
            'numero' => $this->numero ?: null, 'complemento' => $this->complemento ?: null,
            'status_operacional' => $this->status_operacional, 'ativo' => $this->ativo,
            'tabela_preco_id' => $this->tabela_preco_id ? (int)$this->tabela_preco_id : null,
        ];

        if ($this->modo === 'create') {
            $l = Loja::create($data);
            $this->toast('Loja cadastrada com sucesso!');
        } else {
            $l = Loja::findOrFail($this->editandoId);
            $l->update($data);
            $this->toast('Loja atualizada com sucesso!');
        }
        $this->selecionar($l->id);
    }

    public function excluir(int $id): void
    {
        $pdvCount = DB::table('pdv_vendas')->where('loja_id', $id)->count();
        $compraCount = DB::table('compras_pedidos')->where('loja_id', $id)->count();
        $financeiroCount = DB::table('financeiro_lancamentos')->where('loja_id', $id)->count();
        if ($pdvCount > 0 || $compraCount > 0 || $financeiroCount > 0) {
            $this->addError('loja', 'Não é possível excluir: loja possui vendas, compras ou lançamentos vinculados.');
            return;
        }
        Loja::findOrFail($id)->delete();
        $this->novo();
        $this->toast('Loja excluída.');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.loja-manager')
            ->layout('components.layouts.app', ['title' => 'Lojas · ERP Mercado']);
    }
}

<?php

namespace App\Livewire;

use App\Models\FinanceiroLancamento;
use App\Models\FinanceiroCategoria;
use App\Models\FinanceiroConta;
use App\Models\FinanceiroCentroCusto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class FinanceiroManager extends Component
{
    use WithPagination;
    public string $aba = 'lancamentos';
    public string $busca = '';
    public string $filtroStatus = '';
    public string $filtroTipo = '';
    public bool $modalOpen = false;

    public ?int $editandoId = null;
    public string $tipo = 'despesa';
    public string $descricao = '';
    public string $valor = '';
    public string $data_vencimento = '';
    public string $data_pagamento = '';
    public string $status = 'pendente';
    public string $categoria_id = '';
    public string $conta_id = '';
    public string $centro_custo_id = '';
    public string $dataInicio = '';
    public string $dataFim = '';

    public string $catNome = '';
    public string $catTipo = 'despesa';
    public ?int $catParentId = null;

    public string $ctNome = '';
    public string $ctTipo = 'banco';
    public string $ctBanco = '';
    public string $ctAgencia = '';
    public string $ctConta = '';
    public string $ctSaldo = '0';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function atualizarStatusAtrasados(): void
    {
        FinanceiroLancamento::where('status', 'pendente')
            ->where('data_vencimento', '<', now()->startOfDay())
            ->update(['status' => 'atrasado']);
    }

    protected function rules(): array
    {
        return [
            'descricao' => ['required', 'string', 'max:255'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data_vencimento' => ['required', 'date'],
            'tipo' => ['required', 'in:receita,despesa'],
            'status' => ['required', 'in:pendente,pago,cancelado'],
        ];
    }

    // ─── MÉTRICAS ───

    #[Computed]
    public function saldoEmCaixa(): float
    {
        $receitas = FinanceiroLancamento::where('tipo', 'receita')->where('status', 'pago')->sum('valor');
        $despesas = FinanceiroLancamento::where('tipo', 'despesa')->where('status', 'pago')->sum('valor');
        return $receitas - $despesas;
    }

    #[Computed]
    public function aReceber(): float
    {
        return FinanceiroLancamento::where('tipo', 'receita')->where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function aPagar(): float
    {
        return FinanceiroLancamento::where('tipo', 'despesa')->where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function resultadoMes(): float
    {
        $rec = FinanceiroLancamento::where('tipo', 'receita')->whereMonth('data_vencimento', now()->month)->where('status', 'pago')->sum('valor');
        $des = FinanceiroLancamento::where('tipo', 'despesa')->whereMonth('data_vencimento', now()->month)->where('status', 'pago')->sum('valor');
        return $rec - $des;
    }

    #[Computed]
    public function fluxoCaixa7d(): float
    {
        $rec = FinanceiroLancamento::where('tipo', 'receita')->whereBetween('data_vencimento', [now(), now()->addDays(7)])->sum('valor');
        $des = FinanceiroLancamento::where('tipo', 'despesa')->whereBetween('data_vencimento', [now(), now()->addDays(7)])->sum('valor');
        return $rec - $des;
    }

    #[Computed]
    public function lancamentosRecentes(): array
    {
        return FinanceiroLancamento::with('categoria')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function proximosVencimentos(): array
    {
        return FinanceiroLancamento::where('status', 'pendente')
            ->whereDate('data_vencimento', '>=', now())
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function totalPendente(): float
    {
        return FinanceiroLancamento::where('status', 'pendente')->sum('valor');
    }

    #[Computed]
    public function totalPago(): float
    {
        return FinanceiroLancamento::where('status', 'pago')->sum('valor');
    }

    #[Computed]
    public function totalVencido(): float
    {
        return FinanceiroLancamento::where('status', 'pendente')
            ->where('data_vencimento', '<', now())->sum('valor');
    }

    #[Computed]
    public function lancamentos()
    {
        $q = FinanceiroLancamento::with('categoria', 'conta')->orderBy('created_at', 'desc');
        if ($this->filtroStatus) $q->where('status', $this->filtroStatus);
        if ($this->filtroTipo) $q->where('tipo', $this->filtroTipo);
        if ($this->dataInicio) $q->whereDate('data_vencimento', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('data_vencimento', '<=', $this->dataFim);
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('descricao', 'like', '%' . $this->busca . '%');
        }
        return $q->paginate(25);
    }

    #[Computed]
    public function categorias(): array { return FinanceiroCategoria::where('ativo', true)->get()->toArray(); }
    #[Computed]
    public function contas(): array { return FinanceiroConta::where('ativo', true)->get()->toArray(); }
    #[Computed]
    public function centrosCusto(): array { return FinanceiroCentroCusto::where('ativo', true)->get()->toArray(); }

    // ─── CRUD ───

    public function selecionar(int $id): void
    {
        $l = FinanceiroLancamento::findOrFail($id);
        $this->editandoId = $l->id;
        $this->tipo = $l->tipo;
        $this->descricao = $l->descricao;
        $this->valor = number_format($l->valor, 2, ',', '.');
        $this->data_vencimento = $l->data_vencimento?->format('Y-m-d') ?? '';
        $this->data_pagamento = $l->data_pagamento?->format('Y-m-d') ?? '';
        $this->status = $l->status;
        $this->categoria_id = (string)$l->categoria_id;
        $this->conta_id = (string)$l->conta_id;
        $this->centro_custo_id = (string)$l->centro_custo_id;
        $this->modalOpen = true;
    }

    public function resetForm(): void
    {
        $this->editandoId = null;
        $this->tipo = 'despesa';
        $this->descricao = '';
        $this->valor = '';
        $this->data_vencimento = '';
        $this->data_pagamento = '';
        $this->status = 'pendente';
        $this->categoria_id = '';
        $this->conta_id = '';
        $this->centro_custo_id = '';
    }

    public function abrirModal(): void
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function fecharModal(): void
    {
        $this->modalOpen = false;
        $this->resetErrorBag();
    }

    public function salvar(): void
    {
        $this->validate();
        $valor = (float)str_replace(['.', ','], ['', '.'], $this->valor);

        $data = [
            'empresa_id' => auth()->user()->empresa_id ?? 1,
            'loja_id' => auth()->user()->loja_id,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'valor' => $valor,
            'data_competencia' => $this->data_competencia ?: $this->data_vencimento,
            'data_vencimento' => $this->data_vencimento,
            'data_pagamento' => $this->status === 'pago' ? ($this->data_pagamento ?: now()->format('Y-m-d')) : null,
            'status' => $this->status,
            'categoria_id' => $this->categoria_id ? (int)$this->categoria_id : null,
            'centro_custo_id' => $this->centro_custo_id ? (int)$this->centro_custo_id : null,
            'conta_id' => $this->conta_id ? (int)$this->conta_id : null,
            'usuario_id' => auth()->id(),
        ];

        if ($this->editandoId) {
            FinanceiroLancamento::findOrFail($this->editandoId)->update($data);
        } else {
            FinanceiroLancamento::create($data);
        }
        $this->resetForm();
        $this->fecharModal();
        $this->toast('Lançamento salvo!');
    }

    public function pagar(int $id): void
    {
        FinanceiroLancamento::findOrFail($id)->update(['status' => 'pago', 'data_pagamento' => now()]);
        $this->toast('Lançamento marcado como pago!');
    }

    public function estornar(int $id): void
    {
        FinanceiroLancamento::findOrFail($id)->update(['status' => 'pendente', 'data_pagamento' => null]);
        $this->toast('Lançamento estornado!');
    }

    public function excluir(int $id): void
    {
        FinanceiroLancamento::findOrFail($id)->delete();
        $this->resetForm();
        $this->toast('Lançamento excluído!');
    }

    public function salvarCategoria(): void
    {
        FinanceiroCategoria::create(['nome' => $this->catNome, 'tipo' => $this->catTipo, 'parent_id' => $this->catParentId]);
        $this->catNome = '';
        $this->toast('Categoria criada!');
    }

    public function excluirCategoria(int $id): void
    {
        FinanceiroCategoria::findOrFail($id)->update(['ativo' => false]);
        $this->toast('Categoria desativada!');
    }

    public function salvarConta(): void
    {
        FinanceiroConta::create([
            'nome' => $this->ctNome,
            'tipo' => $this->ctTipo,
            'banco' => $this->ctBanco ?: null,
            'agencia' => $this->ctAgencia ?: null,
            'conta' => $this->ctConta ?: null,
            'saldo_inicial' => (float)str_replace(['.', ','], ['', '.'], $this->ctSaldo),
        ]);
        $this->ctNome = '';
        $this->toast('Conta criada!');
    }

    public function excluirConta(int $id): void
    {
        FinanceiroConta::findOrFail($id)->update(['ativo' => false]);
        $this->toast('Conta desativada!');
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        $this->atualizarStatusAtrasados();
        return view('livewire.financeiro-manager')
            ->layout('components.layouts.app', ['title' => 'Financeiro · ERP Mercado']);
    }
}

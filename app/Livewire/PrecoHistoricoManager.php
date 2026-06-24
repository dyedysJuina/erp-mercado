<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\PrecoHistorico;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PrecoHistoricoManager extends Component
{
    use WithPagination;

    public string $busca = '';
    public string $lojaFiltro = '';
    public string $dataInicio = '';
    public string $dataFim = '';
    public string $periodo = '';

    public ?int $produtoDetalhe = null;
    public string $produtoDetalheNome = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    #[Computed]
    public function lojas(): array
    {
        return Loja::orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function historico()
    {
        $q = PrecoHistorico::with('variacao', 'usuario', 'loja')
            ->orderBy('created_at', 'desc');

        if (strlen(trim($this->busca)) >= 2) {
            $q->whereHas('variacao', fn($w) => $w->where('nome_completo', 'like', "%{$this->busca}%")
                ->orWhere('sku', 'like', "%{$this->busca}%"));
        }
        if ($this->lojaFiltro) {
            $q->where('loja_id', (int)$this->lojaFiltro);
        }
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);
        $this->aplicarFiltroPeriodo($q);

        return $q->paginate(25);
    }

    private function aplicarFiltroPeriodo($q): void
    {
        $periodoInt = (int)$this->periodo;
        if ($periodoInt > 0 && !$this->dataInicio && !$this->dataFim) {
            $q->where('created_at', '>=', now()->subDays($periodoInt));
        }
    }

    #[Computed]
    public function totais(): array
    {
        $q = PrecoHistorico::query();
        if ($this->lojaFiltro) $q->where('loja_id', (int)$this->lojaFiltro);
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);
        $this->aplicarFiltroPeriodo($q);

        return [
            'total_ajustes' => $q->count(),
            'media_variacao' => (float)$q->avg(DB::raw('preco_novo - preco_anterior')),
            'maior_subida' => (float)(clone $q)->whereColumn('preco_novo', '>', 'preco_anterior')->max(DB::raw('preco_novo - preco_anterior')) ?: 0,
            'maior_queda' => (float)(clone $q)->whereColumn('preco_novo', '<', 'preco_anterior')->min(DB::raw('preco_novo - preco_anterior')) ?: 0,
        ];
    }

    public function verDetalhe(int $variacaoId, string $nome): void
    {
        $this->produtoDetalhe = $variacaoId;
        $this->produtoDetalheNome = $nome;
    }

    public function fecharDetalhe(): void
    {
        $this->produtoDetalhe = null;
        $this->produtoDetalheNome = '';
    }

    #[Computed]
    public function evolucaoProduto(): array
    {
        if (!$this->produtoDetalhe) return [];
        return PrecoHistorico::with('usuario')
            ->where('produto_variacao_id', $this->produtoDetalhe)
            ->when($this->lojaFiltro, fn($q) => $q->where('loja_id', (int)$this->lojaFiltro))
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.preco-historico-manager')
            ->layout('components.layouts.app', ['title' => 'Histórico de Preços · ERP Mercado']);
    }
}

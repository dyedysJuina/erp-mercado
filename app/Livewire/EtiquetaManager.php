<?php

namespace App\Livewire;

use App\Models\Loja;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class EtiquetaManager extends Component
{
    use WithPagination;

    public string $busca = '';
    public string $lojaId = '';
    public array $selecionados = [];
    public string $qtdEtiquetas = '1';
    public string $precoTipo = 'venda'; // venda | atacado

    public string $toastMsg = '';
    public bool $toastShow = false;

    #[Computed]
    public function lojas(): array
    {
        return Loja::where('ativo', true)->orderBy('nome')->get(['id', 'nome'])->toArray();
    }

    public function resultados()
    {
        $q = ProdutoVariacao::where('ativo', true)
            ->with('marca', 'unidadeMedida')
            ->orderBy('nome_completo');

        if (strlen(trim($this->busca)) >= 2) {
            $q->where(function ($w) {
                $w->where('nome_completo', 'like', "%{$this->busca}%")
                  ->orWhere('sku', 'like', "%{$this->busca}%");
            });
        }

        return $q->paginate(20);
    }

    public function toggleSelecao(int $id): void
    {
        if (in_array($id, $this->selecionados)) {
            $this->selecionados = array_values(array_diff($this->selecionados, [$id]));
        } else {
            $this->selecionados[] = $id;
        }
    }

    public function selecionarTodos(): void
    {
        $ids = ProdutoVariacao::where('ativo', true)
            ->when(strlen(trim($this->busca)) >= 2, fn($q) => $q->where(function ($w) {
                $w->where('nome_completo', 'like', "%{$this->busca}%")->orWhere('sku', 'like', "%{$this->busca}%");
            }))
            ->pluck('id')
            ->toArray();
        $this->selecionados = $ids;
    }

    public function limparSelecao(): void
    {
        $this->selecionados = [];
    }

    public function imprimir(): void
    {
        if (empty($this->selecionados)) {
            $this->toast('Selecione pelo menos um produto.');
            return;
        }
        $this->dispatch('imprimir-etiquetas');
    }

    #[Computed]
    public function precosPorProduto(): array
    {
        if (!$this->lojaId || empty($this->selecionados)) return [];
        $campo = $this->precoTipo === 'atacado' ? 'preco_atacado' : 'preco_venda';
        $precos = DB::table('tabela_precos_itens')
            ->join('lojas', 'lojas.tabela_preco_id', '=', 'tabela_precos_itens.tabela_preco_id')
            ->where('lojas.id', (int)$this->lojaId)
            ->whereIn('tabela_precos_itens.produto_variacao_id', $this->selecionados)
            ->select('produto_variacao_id', $campo)
            ->get()
            ->keyBy('produto_variacao_id');

        $result = [];
        foreach ($precos as $id => $p) {
            $result[$id] = (float)$p->$campo;
        }
        return $result;
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.etiqueta-manager')
            ->layout('components.layouts.app', ['title' => 'Etiquetas · ERP Mercado']);
    }
}

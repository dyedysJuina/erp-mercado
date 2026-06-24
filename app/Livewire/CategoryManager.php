<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\ProdutoBase;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Computed;

class CategoryManager extends Component
{
    public string $nome = '';
    public string $descricao = '';
    public string $status = 'active';

    public string $nivel1 = '';
    public string $nivel2 = '';
    public string $nivel3 = '';

    public string $mode = 'create';
    public ?int $editingId = null;
    public string $search = '';
    public string $treeSearch = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    protected function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:active,inactive,pending'],
        ];
    }

    public function mount(): void {}

    #[Computed]
    public function deptos(): array
    {
        return Categoria::whereNull('parent_id')
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function categorias(): array
    {
        if (!$this->nivel1) return [];
        return Categoria::where('parent_id', (int)$this->nivel1)
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function subcategorias(): array
    {
        if (!$this->nivel2) return [];
        return Categoria::where('parent_id', (int)$this->nivel2)
            ->orderBy('ordem')->orderBy('nome')
            ->get(['id', 'nome'])->toArray();
    }

    #[Computed]
    public function metrics(): array
    {
        $raiz = Categoria::whereNull('parent_id')->count();
        $n2 = Categoria::whereNotNull('parent_id')
            ->whereIn('parent_id', fn($q) => $q->select('id')->from('categorias')->whereNull('parent_id'))
            ->count();
        $n3 = Categoria::whereNotNull('parent_id')
            ->whereNotIn('parent_id', fn($q) => $q->select('id')->from('categorias')->whereNull('parent_id'))
            ->count();
        $inativas = Categoria::where('ativo', false)->count();
        $produtos = ProdutoBase::count();
        return ['raiz' => $raiz, 'n2' => $n2, 'n3' => $n3, 'inativas' => $inativas, 'produtos' => $produtos];
    }

    #[Computed]
    public function estrutura(): array
    {
        $todas = Categoria::orderBy('ordem')->orderBy('nome')->get()->keyBy('id');
        $contagens = ProdutoBase::selectRaw('categoria_id, COUNT(*) as total')
            ->whereIn('categoria_id', $todas->pluck('id'))
            ->groupBy('categoria_id')
            ->pluck('total', 'categoria_id');

        $deptos = $todas->whereNull('parent_id');
        $result = [];
        foreach ($deptos as $d) {
            $cats = $todas->where('parent_id', $d->id);
            $catList = [];
            foreach ($cats as $c) {
                $subs = $todas->where('parent_id', $c->id);
                $subArr = [];
                foreach ($subs as $s) {
                    $subArr[] = ['id' => $s->id, 'nome' => $s->nome, 'qtd' => (int)($contagens[$s->id] ?? 0)];
                }
                $catList[] = ['id' => $c->id, 'nome' => $c->nome, 'subs' => $subArr, 'qtd' => (int)($contagens[$c->id] ?? 0)];
            }
            $catIds = $cats->pluck('id');
            $result[] = ['id' => $d->id, 'nome' => $d->nome, 'categorias' => $catList, 'qtd' => (int)$todas->whereIn('parent_id', $catIds)->sum(fn($s) => $contagens[$s->id] ?? 0)];
        }
        return $result;
    }

    #[Computed]
    public function searchResults(): array
    {
        if (strlen($this->search) < 2) return [];
        return Categoria::where('nome', 'like', "%{$this->search}%")
            ->orWhere('caminho', 'like', "%{$this->search}%")
            ->orderByRaw('nivel, ordem, nome')->limit(8)->get()->toArray();
    }

    #[Computed]
    public function totalProdutosCategoria(): int
    {
        if (!$this->editingId) return 0;
        return ProdutoBase::where('categoria_id', $this->editingId)->count();
    }

    // ─── NAVEGAÇÃO ───

    public function updatedNivel1(): void
    {
        $this->nivel2 = '';
        $this->nivel3 = '';
        if (!$this->nivel1) { $this->resetForm(); return; }
        // Em modo create, não carrega dados da categoria (só atualiza cascade)
        if ($this->mode === 'edit') $this->carregarSelecionada();
    }

    public function updatedNivel2(): void
    {
        $this->nivel3 = '';
        if ($this->nivel2) {
            if ($this->mode === 'edit') $this->carregarSelecionada();
        } elseif ($this->nivel1) {
            if ($this->mode === 'edit') $this->carregarSelecionada();
        } else {
            $this->resetForm();
        }
    }

    public function updatedNivel3(): void
    {
        if ($this->nivel3) {
            if ($this->mode === 'edit') $this->carregarSelecionada();
        } elseif ($this->nivel2) {
            if ($this->mode === 'edit') $this->carregarSelecionada();
        } elseif ($this->nivel1) {
            if ($this->mode === 'edit') $this->carregarSelecionada();
        } else {
            $this->resetForm();
        }
    }

    protected function carregarSelecionada(): void
    {
        $id = $this->nivel3 ?: ($this->nivel2 ?: $this->nivel1);
        if (!$id) return;
        $cat = Categoria::find((int)$id);
        if (!$cat) return;
        $this->editingId = $cat->id;
        $this->mode = 'edit';
        $this->nome = $cat->nome;
        $this->descricao = '';
        $this->status = $cat->ativo ? 'active' : 'inactive';
    }

    protected function parentId(): ?int
    {
        if ($this->nivel2) return (int)$this->nivel2;
        if ($this->nivel1) return (int)$this->nivel1;
        return null;
    }

    protected function nivelDestino(): int
    {
        if ($this->nivel3) return 3;
        if ($this->nivel2) return 2;
        if ($this->nivel1) return 1;
        return 0;
    }

    public function selectCategoria(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        $this->editingId = $cat->id;
        $this->mode = 'edit';
        $this->nome = $cat->nome;
        $this->descricao = '';
        $this->status = $cat->ativo ? 'active' : 'inactive';
        $this->posicionarCascata($cat);
    }

    protected function posicionarCascata(Categoria $cat): void
    {
        $ids = [];
        $atual = $cat;
        while ($atual) {
            $ids[] = $atual->id;
            $atual = $atual->parent;
        }
        $ids = array_reverse($ids);
        $this->nivel1 = (string)($ids[0] ?? '');
        $this->nivel2 = (string)($ids[1] ?? '');
        $this->nivel3 = (string)($ids[2] ?? '');
    }

    // ─── CRUD ───

    public function save(): void
    {
        $this->validate();
        $parentId = $this->parentId();

        if ($this->mode === 'edit' && $parentId == $this->editingId) {
            $this->addError('nivel1', 'Uma categoria nao pode ser pai dela mesma.'); return;
        }
        if ($parentId && $this->mode === 'edit') {
            $cat = Categoria::find($this->editingId);
            $novoPai = Categoria::find($parentId);
            if ($cat && $novoPai && $cat->isAncestorOf($novoPai)) {
                $this->addError('nivel1', 'Nao e possivel mover para um descendente.'); return;
            }
        }

        $slug = Categoria::gerarSlugUnico(Str::slug($this->nome), $this->mode === 'edit' ? $this->editingId : null);
        $nivel = $this->nivelDestino() + 1;
        $caminho = $this->nome;
        if ($parentId) {
            $pai = Categoria::find($parentId);
            if ($pai) $caminho = $pai->caminho . ' > ' . $this->nome;
        }

        $data = [
            'parent_id' => $parentId, 'nome' => $this->nome, 'slug' => $slug,
            'caminho' => $caminho, 'nivel' => $nivel, 'ativo' => $this->status === 'active',
        ];

        if ($this->mode === 'create') {
            $cat = Categoria::create($data);
            $cat->update(['categoria_raiz_id' => $cat->id]);
            $this->toast('Categoria criada com sucesso!');
            // Mantém cascade, limpa nome pra cadastrar outra rapidamente
            $this->nome = '';
            $this->descricao = '';
            $this->status = 'active';
            $this->resetErrorBag();
            $this->dispatch('focus-nome');
        } else {
            $cat = Categoria::findOrFail($this->editingId);
            $mudouPai = $cat->parent_id !== $parentId;
            $cat->update($data);
            if ($mudouPai) Categoria::recalcularDescendentes($cat->id);
            $this->toast('Categoria atualizada com sucesso!');
        }
    }

    public function saveAndAddSub(): void
    {
        $this->validate();
        $parentId = $this->parentId();
        $slug = Categoria::gerarSlugUnico(Str::slug($this->nome));
        $nivel = $this->nivelDestino() + 1;
        $caminho = $this->nome;
        if ($parentId) {
            $pai = Categoria::find($parentId);
            if ($pai) $caminho = $pai->caminho . ' > ' . $this->nome;
        }
        $cat = Categoria::create([
            'parent_id' => $parentId, 'nome' => $this->nome, 'slug' => $slug,
            'caminho' => $caminho, 'nivel' => $nivel, 'ativo' => $this->status === 'active',
        ]);
        $cat->update(['categoria_raiz_id' => $cat->id]);
        // Mantém cascade, limpa nome pra cadastrar sub rapidamente
        $this->nome = '';
        $this->descricao = '';
        $this->status = 'active';
        $this->resetErrorBag();
        $this->posicionarCascata($cat);
        $this->toast('Categoria criada! Agora crie uma subcategoria.');
        $this->dispatch('focus-nome');
    }

    public function novaRaiz(): void { $this->resetAll(); }

    public function resetAll(): void
    {
        $this->mode = 'create'; $this->editingId = null; $this->nome = '';
        $this->descricao = ''; $this->status = 'active';
        $this->nivel1 = ''; $this->nivel2 = ''; $this->nivel3 = '';
        $this->search = ''; $this->resetErrorBag();
    }

    public function resetCascade(): void
    {
        $this->nivel1 = ''; $this->nivel2 = ''; $this->nivel3 = '';
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->mode = 'create'; $this->editingId = null;
        $this->nome = ''; $this->descricao = ''; $this->status = 'active';
        $this->resetErrorBag();
    }

    public function excluir(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        $filhos = Categoria::where('parent_id', $id)->count();
        if ($filhos > 0) {
            $this->addError('exclusao', "'{$cat->nome}' possui {$filhos} subcategoria(s). Remova ou mova os filhos primeiro.");
            return;
        }
        $cat->delete();
        $this->resetAll();
        $this->toast("Categoria '{$cat->nome}' excluida.");
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.category-manager')
            ->layout('components.layouts.app', ['title' => 'Categorias · ERP Mercado']);
    }
}

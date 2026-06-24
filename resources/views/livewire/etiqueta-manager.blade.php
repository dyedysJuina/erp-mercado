<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Etiquetas e Codigo de Barras</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Etiquetas</span>
                </div>
            </div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:200px;margin:0;"><label>Buscar Produto</label><input wire:model.live.debounce.300ms="busca" placeholder="Nome ou SKU..." style="height:38px;font-size:13px;"></div>
            <div class="field" style="width:150px;margin:0;"><label>Loja (preco)</label><select wire:model.live="lojaId" style="height:38px;font-size:12px;"><option value="">Selecione...</option>@foreach ($this->lojas as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
            <div class="field" style="width:120px;margin:0;"><label>Tipo Preco</label><select wire:model.live="precoTipo" style="height:38px;font-size:12px;"><option value="venda">Venda</option><option value="atacado">Atacado</option></select></div>
            <div class="field" style="width:80px;margin:0;"><label>Qtd</label><input wire:model="qtdEtiquetas" type="number" min="1" max="10" style="height:38px;font-size:12px;text-align:center;"></div>
            <button wire:click="selecionarTodos" class="btn-sm btn-secondary" style="height:38px;">Selecionar Todos</button>
            <button wire:click="limparSelecao" class="btn-sm btn-secondary" style="height:38px;">Limpar</button>
        </div>

        @if (count($this->selecionados) > 0)
            <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:color-mix(in srgb,var(--primary-500)8%,transparent);border-radius:8px;margin-bottom:16px;">
                <span style="font-weight:700;font-size:13px;">{{ count($this->selecionados) }} produto(s) selecionado(s)</span>
                <button x-on:click="
                    let ids = {{ json_encode($this->selecionados) }};
                    let loja = '{{ $this->lojaId }}';
                    let tipo = '{{ $this->precoTipo }}';
                    let qtd = '{{ $this->qtdEtiquetas }}';
                    if (!ids.length || !loja) return;
                    let url = '/etiquetas/imprimir?ids=' + ids.join(',') + '&loja=' + loja + '&tipo=' + tipo + '&qtd=' + qtd;
                    window.open(url, '_blank', 'width=800,height=600');
                " class="btn btn-primary" style="height:38px;"><i class="fas fa-print"></i> Imprimir Etiquetas</button>
            </div>
        @endif

        <div class="data-table-wrap">
            <div style="overflow-x:auto;">
                @php $resultados = $this->resultados(); @endphp
                <table class="data-table" style="font-size:12px;">
                    <thead><tr><th class="data-table-th text-center" style="width:32px;"></th><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">SKU</th><th class="data-table-th text-left">Marca</th><th class="data-table-th text-center">Unid</th></tr></thead>
                    <tbody>
                        @forelse ($resultados as $p)
                            <tr class="data-table-tr">
                                <td class="data-table-td text-center"><input type="checkbox" wire:click="toggleSelecao({{ $p->id }})" {{ in_array($p->id, $this->selecionados) ? 'checked' : '' }} style="accent-color:var(--primary-500);width:16px;height:16px;"></td>
                                <td class="data-table-td font-semibold" style="text-transform:uppercase;font-size:11px;">{{ $p->nome_completo }}</td>
                                <td class="data-table-td font-mono text-muted" style="font-size:10px;">{{ $p->sku }}</td>
                                <td class="data-table-td text-muted" style="font-size:11px;">{{ $p->marca?->nome ?? '-' }}</td>
                                <td class="data-table-td text-center text-muted" style="font-size:11px;">{{ $p->unidadeMedida?->sigla ?? 'UN' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="data-table-empty">Nenhum produto encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($resultados->hasPages())
                <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $resultados->links('livewire.pagination-custom') }}</div>
            @endif
        </div>
    </div>
</div>

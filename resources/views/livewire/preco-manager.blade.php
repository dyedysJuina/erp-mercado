<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-2 scale-95"
         class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Tabela de Preços</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Preços</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-tags"></i></div><div><span class="metric-label">Tabelas Ativas</span><span class="metric-value" style="font-size:20px;">{{ $this->tabelasAtivas }}/{{ count($this->listas) }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-box"></i></div><div><span class="metric-label">Produtos</span><span class="metric-value" style="font-size:20px;">{{ number_format($this->totalItens,0,',','.') }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-percentage"></i></div><div><span class="metric-label">Margem Média</span><span class="metric-value" style="font-size:20px;">{{ number_format($this->margemMedia,1,',','.') }}%</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:40px;height:40px;font-size:16px;"><i class="fas fa-table"></i></div><div><span class="metric-label">Tabela</span><span class="metric-value" style="font-size:18px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $this->tabelaAtual['nome'] ?? 'Nenhuma' }}</span></div></div>
        </div>
        {{-- LISTA DE TABELAS --}}
        <div class="form-card" style="margin-bottom:16px;" x-data="{ open: false }">
            <div @click="open = !open" style="display:flex;justify-content:space-between;align-items:center;padding:10px 16px;cursor:pointer;">
                <div style="display:flex;align-items:center;gap:8px;"><i class="fas fa-list"></i><span style="font-size:12px;font-weight:700;color:var(--text);">Tabelas ({{ count($this->listas) }})</span><span style="font-size:10px;color:var(--muted);" x-text="open ? '▲' : '▼'"></span></div>
                <form wire:submit="criarTabela" style="display:flex;gap:6px;" @click.stop><input wire:model.blur="novaTabelaNome" placeholder="Nova tabela..." style="width:200px;height:34px;padding:0 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;"><button type="submit" class="btn-sm btn-primary" style="height:34px;font-size:11px;">Criar</button></form>
            </div>
            <div x-show="open" x-collapse style="max-height:300px;overflow-y:auto;">
                @forelse ($this->listas as $t)
                    <div wire:click="selecionar({{ $t['id'] }})" style="display:flex;align-items:center;gap:10px;padding:8px 16px;border-top:1px solid var(--border);cursor:pointer;{{ $this->tabelaId === $t['id'] ? 'background:color-mix(in srgb,#6366f1 6%,var(--surface));' : '' }}" onmouseover="this.style.background='color-mix(in srgb,var(--text)3%,var(--surface))'" onmouseout="this.style.background='{{ $this->tabelaId === $t['id'] ? 'color-mix(in srgb,#6366f1 6%,var(--surface))' : 'transparent' }}'">
                        <div style="width:28px;height:28px;border-radius:6px;background:{{ $t['is_active'] ? '#6366f1' : '#cbd5e1' }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:10px;">{{ substr($t['nome'],0,2) }}</div>
                        <span style="font-weight:600;font-size:13px;color:var(--text);flex:1;">{{ $t['nome'] }}</span>
                        <span class="badge-sm {{ $t['is_active'] ? 'badge-ativo' : 'badge-inativo' }}" style="font-size:9px;">{{ $t['is_active'] ? 'Ativa' : 'Inativa' }}</span>
                        <button wire:click.stop="duplicarTabela({{ $t['id'] }})" class="btn-tiny" style="font-size:10px;" title="Duplicar">📋</button>
                    </div>
                @empty
                    <div style="padding:20px;text-align:center;color:var(--muted);font-size:12px;">Nenhuma tabela.</div>
                @endforelse
            </div>
        </div>
        {{-- PLANILHA --}}
        @if ($this->tabelaAtual)
            <div class="data-table-wrap">
                <div class="data-table-header"><span style="font-weight:800;font-size:15px;color:var(--text);display:flex;align-items:center;gap:8px;"><i class="fas fa-tags" style="color:#6366f1;"></i> {{ $this->tabelaAtual['nome'] }} @if (count($this->tabelaAtual['lojas']) > 0) <span style="font-size:11px;color:var(--muted);font-weight:400;">— {{ implode(', ',array_column($this->tabelaAtual['lojas'],'nome')) }}</span> @endif</span><button wire:click="duplicarTabela({{ $this->tabelaId }})" class="btn-sm btn-secondary" style="font-size:11px;height:32px;">📋 Duplicar</button></div>
                @if ($mensagem)<div style="padding:8px 14px;font-size:12px;color:var(--success);background:color-mix(in srgb,var(--success)8%,transparent);border-bottom:1px solid var(--border);">{{ $mensagem }}</div>@endif
                <div style="display:flex;gap:8px;padding:10px 16px;border-bottom:1px solid var(--border);background:color-mix(in srgb,var(--text)2%,var(--surface));align-items:center;flex-wrap:wrap;">
                    <div style="flex:1;min-width:200px;position:relative;"><i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;"></i><input wire:model.live.debounce.300ms="buscaProduto" placeholder="Buscar produto..." style="width:100%;height:36px;padding:0 10px 0 30px;border:1px solid var(--border);border-radius:8px;font-size:12px;outline:none;"></div>
                    <input wire:model.blur="loteMargem" type="number" step="0.1" placeholder="Margem %" style="width:100px;height:36px;padding:0 8px;border:1px solid var(--border);border-radius:8px;font-size:12px;">
                    <input wire:model.blur="loteAcrescimo" type="number" step="0.01" placeholder="Acréscimo R$" style="width:110px;height:36px;padding:0 8px;border:1px solid var(--border);border-radius:8px;font-size:12px;">
                    <button wire:click="aplicarEmLote" style="background:#6366f1;color:#fff;height:36px;padding:0 14px;border:0;border-radius:8px;font-weight:700;font-size:11px;cursor:pointer;"><i class="fas fa-check"></i> Aplicar</button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="data-table" style="font-size:12px;">
                        <thead><tr><th class="data-table-th text-center" style="width:40px;"></th><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">Código</th><th class="data-table-th text-right">Custo</th><th class="data-table-th text-center" style="width:90px;">Margem %</th><th class="data-table-th text-right">Venda</th><th class="data-table-th text-right">Atacado</th></tr></thead>
                        <tbody>
                            @php $itensTabela = $this->itensTabela(); @endphp
                            @forelse ($itensTabela as $item)
                                @php $fotoUrl = $item['foto_url'] ?? ''; $nomeCompleto = $item['variacao']['nome_completo'] ?? $item['nome_completo'] ?? '—'; $sku = $item['variacao']['sku'] ?? $item['sku'] ?? '—'; $marca = $item['variacao']['marca']['nome'] ?? ''; @endphp
                                <tr class="data-table-tr">
                                    <td class="data-table-td text-center">@if($fotoUrl)<img src="{{ asset('storage/'.$fotoUrl) }}" style="width:36px;height:36px;border-radius:6px;object-fit:cover;" onerror="this.style.display='none'">@endif<div style="width:36px;height:36px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;{{ $fotoUrl ? 'display:none;' : '' }}"><i class="fas fa-box"></i></div></td>
                                    <td class="data-table-td font-semibold">{{ $nomeCompleto }}@if($marca)<span style="display:block;font-size:10px;color:var(--muted);">{{ $marca }}</span>@endif</td>
                                    <td class="data-table-td font-mono text-muted" style="font-size:11px;">{{ $sku }}</td>
                                    <td class="data-table-td text-right font-semibold">R$ {{ number_format($item['preco_custo'],2,',','.') }}</td>
                                    <td class="data-table-td text-center"><input type="number" step="0.1" x-data x-init="$el.value = '{{ $item['margem_percentual'] }}'" @change="$wire.atualizarPreco({{ $item['id'] }}, 'margem_percentual', $el.value)" style="width:75px;padding:4px 6px;border:1px solid var(--border);border-radius:6px;font-size:12px;text-align:right;background:transparent;"></td>
                                    <td class="data-table-td text-right font-bold" style="color:var(--success);">R$ {{ number_format($item['preco_venda'],2,',','.') }}</td>
                                    <td class="data-table-td text-right font-semibold" style="color:var(--primary-600);">R$ {{ number_format($item['preco_atacado'] ?? 0,2,',','.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="data-table-empty">Nenhum item. Selecione uma tabela.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($itensTabela->hasPages())
                    <div style="padding:12px 16px;border-top:1px solid var(--border);">
                        {{ $itensTabela->links('livewire.pagination-custom') }}
                    </div>
                @endif
            </div>
        @else
            <div class="empty-state"><div class="empty-state-icon"><i class="fas fa-tags"></i></div><span class="empty-state-title">Nenhuma tabela selecionada</span><span class="empty-state-desc">Selecione ou crie uma tabela de preços acima.</span></div>
        @endif
    </div>
</div>

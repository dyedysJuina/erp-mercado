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
        <div class="header-row">
            <div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Marcas</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Marcas</span></div></div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-trademark"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber"><i class="fas fa-circle-minus"></i></div><div><span class="metric-label">Inativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInativos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-image"></i></div><div><span class="metric-label">Com Logo</span><span class="metric-value" style="font-size:20px;">{{ $this->totalComLogo }}</span></div></div>
        </div>
        <div class="grid-2col-custom" style="margin-bottom:20px;">
            <div class="form-card">
                <div class="form-card-header"><span class="form-card-title"><i class="fas {{ $this->editandoId ? 'fa-edit' : 'fa-plus' }}" style="color:var(--primary-600);"></i> {{ $this->editandoId ? 'Editando Marca' : 'Nova Marca' }}</span><span class="form-card-subtitle">{{ $this->editandoId ? 'Altere os dados.' : 'Cadastre marcas e fabricantes.' }}</span>@if ($this->editandoId)<button wire:click="resetForm" class="btn-sm btn-secondary" style="margin-left:auto;">Cancelar</button>@endif</div>
                <form wire:submit="cadastrar" class="form-body">
                    <div class="grid-2" style="margin-bottom:12px;">
                        <div class="field">
                            <label>Nome *</label>
                            <input wire:model.live.debounce.450ms="nome" placeholder="Ex: Coca-Cola, Nestlé, Unilever" autocomplete="off">
                            @error('nome')<div class="err">{{ $message }}</div>@enderror
                            @if (count($this->sugestoesNome) > 0)
                                <div class="sub-card" style="margin-top:6px;padding:0;overflow:hidden;">
                                    <div style="padding:7px 10px;font-size:10px;font-weight:800;text-transform:uppercase;color:var(--muted);background:color-mix(in srgb,var(--text)3%,var(--surface));">Marcas já cadastradas</div>
                                    @foreach ($this->sugestoesNome as $sugestao)
                                        <div wire:key="marca-sugestao-{{ $sugestao['id'] }}" style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;border-top:1px solid var(--border);">
                                            <div style="min-width:0;"><strong style="display:block;color:var(--text);font-size:13px;">{{ $sugestao['nome'] }}</strong><span style="font-size:11px;color:var(--muted);">{{ $sugestao['ativo'] ? 'Ativa' : 'Inativa' }} · {{ $sugestao['slug'] }}</span></div>
                                            <button type="button" wire:click="selecionar({{ $sugestao['id'] }})" class="btn-sm btn-secondary" style="flex-shrink:0;">Editar</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if ($this->duplicata)<div style="color:var(--warning);font-size:12px;margin-top:4px;">Esta marca já existe. Use "Editar".</div>@endif
                        </div>
                        <div class="field"><label>Slug</label><input wire:model="slug" placeholder="Gerado automaticamente" readonly style="background:color-mix(in srgb,var(--text)3%,var(--surface));color:var(--muted);">@error('slug')<div class="err">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="field" style="margin-bottom:12px;"><label>URL do Logo</label><input wire:model="logo_url" placeholder="https://exemplo.com/logo.png"></div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;"><label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--text);"><input wire:model="ativo" type="checkbox" checked style="width:18px;height:18px;"> Marca ativa</label></div>
                    <div class="form-actions" style="margin-top:0;"><button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Cadastrar' }}</span><span wire:loading>Salvando...</span></button></div>
                </form>
            </div>
            <div class="sidebar-card"><div class="sidebar-card-header"><i class="fas fa-circle-info"></i> Sobre Marcas</div><div class="sidebar-card-body" style="font-size:13px;color:var(--muted);line-height:1.6;"><p style="margin-bottom:12px;">Marcas identificam os fabricantes dos produtos.</p><div style="font-weight:700;color:var(--text);margin-bottom:8px;">Dicas:</div><ul style="list-style:none;padding:0;"><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Slug gerado automaticamente</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Adicione URL do logo</li><li style="padding:4px 0;display:flex;gap:8px;"><i class="fas fa-check" style="color:var(--success);margin-top:3px;"></i> Desative sem excluir</li></ul></div></div>
        </div>
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Marcas ({{ $this->totalGeral }})</span><input wire:model.live.debounce.400ms="busca" placeholder="Buscar marca..." class="data-table-filter"></div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th class="data-table-th text-left">Marca</th><th class="data-table-th text-left">Slug</th><th class="data-table-th text-center">Logo</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:90px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($this->estrutura() as $m)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="display:flex;align-items:center;gap:10px;"><span style="width:32px;height:32px;border-radius:8px;background:color-mix(in srgb,#6366f1 10%,transparent);display:inline-flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;"><i class="fas fa-trademark"></i></span>{{ $m['nome'] }}</td>
                                <td class="data-table-td font-mono text-muted">{{ $m['slug'] }}</td>
                                <td class="data-table-td text-center">@if ($m['logo_url'])<a href="{{ $m['logo_url'] }}" target="_blank" style="color:var(--primary-600);font-size:12px;"><i class="fas fa-external-link-alt"></i></a>@else<span style="color:var(--muted);font-size:12px;">—</span>@endif</td>
                                <td class="data-table-td text-center"><span class="badge-sm {{ $m['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $m['ativo'] ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="data-table-td text-center"><div class="table-actions"><button wire:click="selecionar({{ $m['id'] }})" class="table-action-btn" style="color:var(--primary-600);" title="Editar"><i class="fas fa-pen"></i></button><button wire:click="excluir({{ $m['id'] }})" wire:confirm="Excluir marca?" class="table-action-btn" style="color:var(--danger);" title="Excluir"><i class="fas fa-trash-alt"></i></button></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="data-table-empty">Nenhuma marca encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

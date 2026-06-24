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
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Produtos Base</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Cadastros</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Produtos Base</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-boxes"></i></div><div><span class="metric-label">Total</span><span class="metric-value" style="font-size:20px;">{{ $this->totalGeral }}</span></div></div>
            <div class="metric-card"><div class="metric-icon green" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalAtivos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon amber" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-circle-minus"></i></div><div><span class="metric-label">Inativos</span><span class="metric-value" style="font-size:20px;">{{ $this->totalInativos }}</span></div></div>
            <div class="metric-card"><div class="metric-icon blue" style="width:42px;height:42px;font-size:18px;"><i class="fas fa-file-invoice"></i></div><div><span class="metric-label">Com NCM</span><span class="metric-value" style="font-size:20px;">{{ $this->totalComNcm }}</span></div></div>
        </div>
        {{-- FORMULÁRIO --}}
        <div class="form-card" style="margin-bottom:20px;">
            <div class="form-card-header"><span class="form-card-title"><i class="fas fa-plus form-card-icon"></i> Novo Produto Base</span><span class="form-card-subtitle">Cadastre produtos base por categoria.</span></div>
            <div class="form-body">
                {{-- Cascade + Nome --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 2fr;gap:10px;align-items:end;margin-bottom:12px;">
                    <div class="field"><label>Nível 1</label><select wire:model.live="nivel1"><option value="">Selecione...</option>@foreach ($this->optsNivel1 as $opt)<option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>@endforeach</select></div>
                    <div class="field"><label>Nível 2</label><select wire:model.live="nivel2" {{ !$this->nivel1 ? 'disabled' : '' }}><option value="">Selecione...</option>@foreach ($this->optsNivel2 as $opt)<option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>@endforeach</select></div>
                    <div class="field"><label>Nível 3</label><select wire:model.live="nivel3" {{ !$this->nivel2 ? 'disabled' : '' }}><option value="">Selecione...</option>@foreach ($this->optsNivel3 as $opt)<option value="{{ $opt['id'] }}">{{ $opt['nome'] }}</option>@endforeach</select>@error('nivel1')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>Nome do Produto *</label><input wire:model.blur="nome" placeholder="Ex: Arroz Tipo 1...">@error('nome')<div class="err">{{ $message }}</div>@enderror@if (count($this->duplicatas) > 0 && strlen(trim($nome)) >= 2)<div style="color:var(--warning);font-size:11px;margin-top:4px;">Já existe{{ count($this->duplicatas) > 1 ? 'm' : '' }}:@foreach ($this->duplicatas as $dup)<div style="padding:1px 0;">· {{ $dup }}</div>@endforeach</div>@endif</div>
                </div>
                {{-- NCM + CEST + Origem --}}
                <div style="display:grid;grid-template-columns:1.5fr 1fr 1fr;gap:10px;margin-bottom:12px;">
                    <div class="field" style="position:relative;"><label>NCM</label>
                        <input wire:model.live.debounce.300ms="ncm_busca" placeholder="Buscar por código ou descrição...">
                        @if ($this->ncm_id)@php $ncmSel = \App\Models\Ncm::find((int)$this->ncm_id); @endphp@if ($ncmSel)<div style="display:flex;align-items:center;gap:6px;margin-top:4px;"><span style="font-size:12px;color:var(--text);"><b>{{ $ncmSel->codigo }}</b> — {{ $ncmSel->descricao }}</span><button type="button" wire:click="$set('ncm_id', null)" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:14px;padding:0;">&times;</button></div>@endif@endif
                        @if (strlen(trim($this->ncm_busca)) >= 1 && count($this->ncmOpts) > 0)<div style="position:absolute;top:100%;left:0;right:0;z-index:20;background:var(--surface);border:1px solid var(--border);border-radius:8px;margin-top:2px;max-height:240px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1);">@foreach ($this->ncmOpts as $n)<button type="button" wire:click="selecionarNcm({{ $n['id'] }})" style="display:block;width:100%;text-align:left;padding:8px 10px;font-size:13px;background:var(--surface);border:0;cursor:pointer;color:var(--text);"><b>{{ $n['codigo'] }}</b> — {{ $n['descricao'] }}</button>@endforeach</div>@endif
                    </div>
                    <div class="field"><label>CEST</label><select wire:model.live="cest_id"><option value="">N/A</option>@foreach (\App\Models\Cest::where('ativo', true)->orderBy('codigo')->get() as $c)<option value="{{ $c->id }}">{{ $c->codigo }} — {{ $c->descricao }}</option>@endforeach</select></div>
                    <div class="field"><label>Origem</label><select wire:model.live="origem_mercadoria"><option value="">Selecione...</option><option value="0">Nacional</option><option value="1">Estrangeira</option><option value="2">Estrangeira (mercado interno)</option></select></div>
                </div>
                {{-- CFOP + CSOSN --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
                    <div class="field"><label>CFOP</label><select wire:model.live="cfop_id"><option value="">Selecione...</option>@foreach ($this->cfopOpts as $c)<option value="{{ $c['id'] }}">{{ $c['codigo'] }} — {{ $c['descricao'] }}</option>@endforeach</select></div>
                    <div class="field"><label>CSOSN</label><select wire:model.live="cst_icms"><option value="">Selecione...</option>@foreach ($this->csosnOpts as $cs)<option value="{{ $cs['codigo'] }}">{{ $cs['codigo'] }} — {{ $cs['descricao'] }}</option>@endforeach</select></div>
                </div>
                {{-- Alíquotas --}}
                <div style="margin-bottom:12px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:var(--text);"><input type="checkbox" wire:model.live="usar_aliquotas" style="width:16px;height:16px;"> Usar alíquotas manuais (ICMS, PIS, COFINS)</label>
                    @if ($this->usar_aliquotas)<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:8px;"><div class="field"><label>ICMS (%)</label><input type="number" step="0.01" min="0" max="100" wire:model.blur="aliquota_icms" placeholder="Ex: 18"></div><div class="field"><label>PIS (%)</label><input type="number" step="0.01" min="0" max="100" wire:model.blur="aliquota_pis" placeholder="Ex: 1.65"></div><div class="field"><label>COFINS (%)</label><input type="number" step="0.01" min="0" max="100" wire:model.blur="aliquota_cofins" placeholder="Ex: 7.6"></div></div>@endif
                </div>
                <div class="form-actions" style="margin-top:0;"><button wire:click="cadastrar" wire:loading.attr="disabled" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> Cadastrar</span><span wire:loading>Salvando...</span></button></div>
            </div>
        </div>
        {{-- LISTAGEM --}}
        <div class="data-table-wrap">
            <div class="data-table-header"><span class="data-table-title"><i class="fas fa-list"></i> Produtos Base ({{ $this->totalGeral }})</span><input wire:model.live.debounce.150ms="busca" placeholder="Buscar produto..." class="data-table-filter"></div>
            <div style="overflow-x:auto;">
                <table class="data-table" style="font-size:13px;">
                    <thead><tr><th class="data-table-th text-left">Produto</th><th class="data-table-th text-left">Categoria</th><th class="data-table-th text-center">NCM</th><th class="data-table-th text-center">CSOSN</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:80px;">Ações</th></tr></thead>
                    <tbody>
                        @forelse ($this->listagem() as $p)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold" style="display:flex;align-items:center;gap:10px;"><span style="width:32px;height:32px;border-radius:8px;background:color-mix(in srgb,#6366f1 10%,transparent);display:inline-flex;align-items:center;justify-content:center;color:#6366f1;font-size:13px;"><i class="fas fa-box"></i></span>{{ $p->nome }}</td>
                                <td class="data-table-td text-muted" style="font-size:12px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $p->categoria?->caminho ?? '' }}">{{ $p->categoria?->caminho ?? '—' }}</td>
                                <td class="data-table-td text-center font-mono" style="font-size:12px;">{{ $p->ncm?->codigo ?? '—' }}</td>
                                <td class="data-table-td text-center">@if ($p->cst_icms)<span style="padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;background:color-mix(in srgb,#6366f1 10%,transparent);color:#6366f1;">{{ $p->cst_icms }}</span>@else<span style="color:var(--muted);">—</span>@endif</td>
                                <td class="data-table-td text-center"><span class="badge-sm {{ $p->ativo ? 'badge-ativo' : 'badge-inativo' }}">{{ $p->ativo ? 'Ativo' : 'Inativo' }}</span></td>
                                <td class="data-table-td text-center"><button wire:click="desativar({{ $p->id }})" class="table-action-btn" style="font-size:11px;color:{{ $p->ativo ? 'var(--warning)' : 'var(--success)' }};" title="{{ $p->ativo ? 'Desativar' : 'Ativar' }}"><i class="fas {{ $p->ativo ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="data-table-empty">Nenhum produto base encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:8px 12px;border-top:1px solid var(--border);">
                {{ $this->listagem()->links('livewire.tailwind-pagination') }}
            </div>
        </div>
    </div>
</div>

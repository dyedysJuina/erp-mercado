<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" x-transition:enter="..." class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Usuários</h1>
                <div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Config</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Usuários</span></div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="metrics-grid">
            <div class="metric-card"><div class="metric-icon purple"><i class="fas fa-users"></i></div><div><span class="metric-label">Total</span><span class="metric-value">{{ $this->totalGeral }}</span><span class="metric-sub">Usuários</span></div></div>
            <div class="metric-card"><div class="metric-icon green"><i class="fas fa-circle-check"></i></div><div><span class="metric-label">Ativos</span><span class="metric-value">{{ $this->totalAtivos }}</span><span class="metric-sub green">Atualmente</span></div></div>
            <div class="metric-card"><div class="metric-icon blue"><i class="fas fa-shield"></i></div><div><span class="metric-label">Admins</span><span class="metric-value">{{ $this->totalAdmins }}</span><span class="metric-sub">Com acesso total</span></div></div>
        </div>

        <div class="grid-2col-custom">
            <div>
                <div class="form-card" x-data="{ tab: 'dados' }">
                    <div class="form-card-header">
                        <span class="form-card-title"><i class="fas fa-user form-card-icon"></i> {{ $this->editandoId ? 'Editando' : 'Novo' }} Usuário</span>
                    </div>
                    <form wire:submit="salvar" class="form-body">
                        <div class="tab-nav">
                            <button @click="tab = 'dados'" :class="tab === 'dados' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-info-circle"></i> Dados</button>
                            <button @click="tab = 'papeis'" :class="tab === 'papeis' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-shield"></i> Papéis</button>
                            <button @click="tab = 'permissoes'" :class="tab === 'permissoes' ? 'tab-btn tab-btn-active' : 'tab-btn'"><i class="fas fa-key"></i> Permissões</button>
                        </div>
                        <div x-show="tab === 'dados'" style="margin-top:14px;">
                            <div class="grid-2" style="margin-bottom:12px;">
                                <div class="field"><label>Nome *</label><input wire:model="name" placeholder="Nome completo">@error('name')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Email *</label><input wire:model="email" type="email">@error('email')<div class="err">{{ $message }}</div>@enderror</div>
                            </div>
                            <div class="grid-2" style="margin-bottom:12px;">
                                <div class="field"><label>Senha {{ $this->editandoId ? '(deixe em branco para manter)' : '*' }}</label><input wire:model="password" type="password">@error('password')<div class="err">{{ $message }}</div>@enderror</div>
                                <div class="field"><label>Loja</label><select wire:model="loja_id"><option value="">Nenhuma</option>@foreach ($this->lojasDisponiveis as $l)<option value="{{ $l['id'] }}">{{ $l['nome'] }}</option>@endforeach</select></div>
                            </div>
                            <div class="field" style="display:flex;align-items:center;gap:12px;">
                                <label style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);margin-bottom:0;"><input wire:model="ativo" type="checkbox" checked style="width:18px;height:18px;"> Usuário ativo</label>
                            </div>
                        </div>
                        <div x-show="tab === 'papeis'" style="margin-top:14px;">
                            <div class="sub-card">
                                @forelse ($this->rolesDisponiveis as $r)
                                    <label style="display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;cursor:pointer;color:var(--text);height:40px;border-bottom:1px solid var(--border);">
                                        <input wire:model="rolesSelecionados" type="checkbox" value="{{ $r['id'] }}" style="width:18px;height:18px;"> {{ $r['name'] }}
                                    </label>
                                @empty
                                    <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum papel disponível.</div>
                                @endforelse
                            </div>
                        </div>
                        <div x-show="tab === 'permissoes'" style="margin-top:14px;">
                            @if ($this->editandoId)
                                @php $perms = $this->permissoesEfetivas; @endphp
                                <div style="font-size:12px;color:var(--muted);font-weight:600;margin-bottom:10px;">
                                    <i class="fas fa-info-circle"></i> Permissões efetivas (união de todos os papéis atribuídos)
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                    @forelse ($perms as $id => $name)
                                        <span style="padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);border:1px solid color-mix(in srgb,var(--success)20%,transparent);">
                                            <i class="fas fa-check-circle" style="margin-right:4px;"></i> {{ $name }}
                                        </span>
                                    @empty
                                        <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhuma permissão — atribua papéis ao usuário.</div>
                                    @endforelse
                                </div>
                            @else
                                <div style="text-align:center;padding:30px;color:var(--muted);font-size:13px;">Selecione um usuário para ver as permissões efetivas.</div>
                            @endif
                        </div>
                        @error('exclusao')<div class="err" style="margin-bottom:8px;">{{ $message }}</div>@enderror
                        <div class="form-actions">
                            <button type="submit" class="btn-primary btn-lg"><span wire:loading.remove><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Salvar' }}</span><span wire:loading>Salvando...</span></button>
                            <button type="button" wire:click="resetForm" class="btn-secondary btn-lg">Limpar</button>
                            @if ($this->editandoId)<button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir?" class="btn-danger btn-lg" style="margin-left:auto;">Excluir</button>@endif
                        </div>
                    </form>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-receipt"></i> Resumo</div>
                    <div class="sidebar-card-body">
                        <div class="resumo-list">
                            <div class="resumo-row"><span class="resumo-label">Nome</span><span class="resumo-value">{{ $this->name ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Email</span><span class="resumo-value">{{ $this->email ?: '—' }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Papéis</span><span class="resumo-value">{{ count($this->rolesSelecionados) }}</span></div>
                            <div class="resumo-row"><span class="resumo-label">Status</span><span class="resumo-badge {{ $this->ativo ? 'resumo-badge-ativo' : 'resumo-badge-inativo' }}">{{ $this->ativo ? 'Ativo' : 'Inativo' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="data-table-wrap" style="margin-top:20px;">
            <div class="data-table-header">
                <span class="data-table-title"><i class="fas fa-table-list"></i> Usuários <span class="data-table-count">({{ $this->totalGeral }})</span></span>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar..." class="data-table-filter">
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr><th class="data-table-th text-left">Nome</th><th class="data-table-th text-left">Email</th><th class="data-table-th text-center">Papéis</th><th class="data-table-th text-center">Status</th><th class="data-table-th text-center" style="width:130px;">Ações</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($this->lista as $u)
                            <tr class="data-table-tr">
                                <td class="data-table-td font-bold cursor-pointer" wire:click="selecionar({{ $u['id'] }})">{{ $u['name'] }}</td>
                                <td class="data-table-td">{{ $u['email'] }}</td>
                                <td class="data-table-td text-center">
                                    @foreach ($u['roles'] as $role)
                                        <span class="badge-sm badge-ativo" style="margin:2px;">{{ $role['name'] }}</span>
                                    @endforeach
                                </td>
                                <td class="data-table-td text-center">
                                    <span class="badge-sm {{ $u['ativo'] ? 'badge-ativo' : 'badge-inativo' }}">{{ $u['ativo'] ? 'Ativo' : 'Inativo' }}</span>
                                </td>
                                <td class="data-table-td text-center">
                                    <div class="table-actions">
                                        <button wire:click="selecionar({{ $u['id'] }})" class="table-action-btn" style="color:var(--primary-600);"><i class="fas fa-eye"></i></button>
                                        <button wire:click="desativar({{ $u['id'] }})" class="table-action-btn" style="color:{{ $u['ativo'] ? 'var(--warning)' : 'var(--success)' }};"><i class="fas {{ $u['ativo'] ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                                        @if ($u['id'] !== auth()->id())
                                        <button wire:click="excluir({{ $u['id'] }})" wire:confirm="Excluir?" class="table-action-btn" style="color:var(--danger);"><i class="fas fa-trash-alt"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="data-table-empty">Nenhum usuário.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

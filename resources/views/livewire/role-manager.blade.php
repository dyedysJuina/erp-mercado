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
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Papéis de Acesso</h1>
                <div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Config</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Papéis</span></div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        <div class="grid-2col-custom">
            {{-- LEFT: Form --}}
            <div>
                <div class="form-card">
                    <div class="form-card-header">
                        <span class="form-card-title"><i class="fas fa-shield form-card-icon"></i> {{ $this->editandoId ? 'Editando: ' . $this->nome : 'Novo Papel' }}</span>
                    </div>
                    <form wire:submit="salvar" class="form-body">
                        <div class="field" style="margin-bottom:16px;">
                            <label>Nome do Papel *</label>
                            <input wire:model="nome" placeholder="Ex: Supervisor, Operador, Gerente">
                            @error('nome')<div class="err">{{ $message }}</div>@enderror
                        </div>

                        <div class="section-border" style="margin-bottom:12px;">Permissões por Módulo</div>

                        @foreach ($this->gruposPermissoes as $chave => $grupo)
                            @php
                                $idsGrupo = collect($grupo['perms'])
                                    ->map(fn($name) => \Spatie\Permission\Models\Permission::where('name', $name)->value('id'))
                                    ->filter()->map(fn($id) => (string)$id)->values()->toArray();
                                $todos = collect($idsGrupo)->every(fn($id) => in_array($id, $this->permissoesSelecionadas));
                                $parcial = collect($idsGrupo)->some(fn($id) => in_array($id, $this->permissoesSelecionadas)) && !$todos;
                            @endphp
                            @if (!empty($idsGrupo))
                            <div class="sub-card" style="margin-bottom:10px;padding:12px;">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                    <span style="font-weight:800;font-size:13px;color:var(--text);">{{ $grupo['label'] }}</span>
                                    <button type="button" wire:click="alternarGrupo('{{ $chave }}')" style="background:none;border:1px solid var(--border);border-radius:6px;padding:4px 10px;font-size:11px;font-weight:600;cursor:pointer;color:var(--muted);">
                                        {{ $todos ? 'Desmarcar todos' : 'Marcar todos' }}
                                    </button>
                                </div>
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach ($grupo['perms'] as $permName)
                                        @php
                                            $perm = \Spatie\Permission\Models\Permission::where('name', $permName)->first();
                                        @endphp
                                        @if ($perm)
                                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;cursor:pointer;color:var(--text);padding:4px 8px;border:1px solid var(--border);border-radius:6px;{{ in_array((string)$perm->id, $this->permissoesSelecionadas) ? 'background:color-mix(in srgb,var(--primary-500)10%,transparent);border-color:color-mix(in srgb,var(--primary-500)30%,transparent);' : '' }}">
                                                <input wire:model="permissoesSelecionadas" type="checkbox" value="{{ $perm->id }}" style="width:14px;height:14px;">
                                                {{ ucfirst($perm->name) }}
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @endforeach

                        @error('exclusao')<div class="err" style="margin-bottom:8px;">{{ $message }}</div>@enderror
                        <div class="form-actions">
                            <button type="submit" class="btn-primary btn-lg"><i class="fas fa-save"></i> {{ $this->editandoId ? 'Atualizar' : 'Criar' }}</button>
                            <button type="button" wire:click="resetForm" class="btn-secondary btn-lg">Limpar</button>
                            @if ($this->editandoId)<button type="button" wire:click="excluir({{ $this->editandoId }})" wire:confirm="Excluir papel?" class="btn-danger btn-lg" style="margin-left:auto;">Excluir</button>@endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- RIGHT: Sidebar --}}
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div class="sidebar-card">
                    <div class="sidebar-card-header"><i class="fas fa-shield"></i> Papéis ({{ count($this->lista) }})</div>
                    <div class="sidebar-card-body" style="padding:8px;max-height:400px;overflow-y:auto;">
                        @php $totalPorPapel = $this->totalUsuariosPorPapel; @endphp
                        @forelse ($this->lista as $r)
                            <div style="cursor:pointer;padding:12px;border-radius:10px;margin-bottom:4px;{{ $this->editandoId === $r['id'] ? 'background:color-mix(in srgb,var(--primary-500)10%,transparent);border:1px solid color-mix(in srgb,var(--primary-500)30%,transparent);' : 'border:1px solid transparent;' }}" wire:click="selecionar({{ $r['id'] }})" onmouseover="this.style.background='color-mix(in srgb,var(--primary-500)5%,transparent)'" onmouseout="this.style.background='{{ $this->editandoId === $r['id'] ? 'color-mix(in srgb,var(--primary-500)10%,transparent)' : 'transparent' }}'">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-weight:700;font-size:14px;color:var(--text);">{{ $r['name'] }}</span>
                                    <span class="badge-sm badge-ativo" style="font-size:10px;">{{ $totalPorPapel[$r['id']] ?? 0 }} usuários</span>
                                </div>
                                <span style="font-size:11px;color:var(--muted);display:block;margin-top:4px;">{{ count($r['permissions']) }} permissões</span>
                                <div style="display:flex;flex-wrap:wrap;gap:2px;margin-top:4px;">
                                    @foreach ($r['permissions'] as $p)
                                        <span style="font-size:8px;padding:1px 5px;border-radius:3px;background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);">{{ $p['name'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center;padding:20px;color:var(--muted);font-size:12px;">Nenhum papel.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

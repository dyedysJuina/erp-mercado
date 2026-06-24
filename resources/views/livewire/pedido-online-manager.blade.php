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
                <h1 class="page-title">Central de Pedidos Online</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span>Operacional</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Pedidos Online</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        @php $t = $this->totais; @endphp
        <div class="metrics-grid" style="grid-template-columns:repeat(6,1fr);">
            <div class="metric-card" style="padding:10px;"><div><span class="metric-label" style="font-size:9px;">Pedidos Hoje</span><span class="metric-value" style="font-size:18px;">{{ $t['hoje'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--primary-500);"><div><span class="metric-label" style="font-size:9px;">Novos</span><span class="metric-value" style="font-size:18px;color:var(--primary-600);">{{ $t['novos'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--warning);"><div><span class="metric-label" style="font-size:9px;">Em Separacao</span><span class="metric-value" style="font-size:18px;color:var(--warning);">{{ $t['separacao'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--success);"><div><span class="metric-label" style="font-size:9px;">Prontos</span><span class="metric-value" style="font-size:18px;color:var(--success);">{{ $t['prontos'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;border-left:3px solid var(--danger);"><div><span class="metric-label" style="font-size:9px;">Atrasados</span><span class="metric-value" style="font-size:18px;color:var(--danger);">{{ $t['atrasados'] }}</span></div></div>
            <div class="metric-card" style="padding:10px;"><div><span class="metric-label" style="font-size:9px;">Faturamento</span><span class="metric-value" style="font-size:18px;">R$ {{ number_format($t['faturamento'], 0, ',', '.') }}</span></div></div>
        </div>

        <div class="sub-card" style="margin-bottom:16px;display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
            <div class="field" style="flex:1;min-width:180px;margin:0;"><label>Buscar</label><input wire:model.live.debounce.300ms="busca" placeholder="Codigo ou cliente..." style="height:34px;font-size:12px;"></div>
            <div class="field" style="width:130px;margin:0;"><label>Status</label><select wire:model.live="filtroStatus" style="height:34px;font-size:11px;"><option value="">Todos</option><option value="recebido">Recebido</option><option value="confirmado">Confirmado</option><option value="em_separacao">Em Separacao</option><option value="pronto_retirada">Pronto Retirada</option><option value="pronto_entrega">Pronto Entrega</option><option value="entregue">Entregue</option><option value="cancelado">Cancelado</option></select></div>
            <div class="field" style="width:110px;margin:0;"><label>Periodo</label><select wire:model.live="periodo" style="height:34px;font-size:11px;"><option value="hoje">Hoje</option><option value="semana">Semana</option><option value="personalizado">Personalizado</option></select></div>
            @if ($periodo === 'personalizado')
                <div class="field" style="width:100px;margin:0;"><input wire:model="dataInicio" type="date" style="height:34px;font-size:11px;"></div>
                <div class="field" style="width:100px;margin:0;"><input wire:model="dataFim" type="date" style="height:34px;font-size:11px;"></div>
            @endif
            <div class="field" style="width:110px;margin:0;"><label>Entrega</label><select wire:model.live="filtroEntrega" style="height:34px;font-size:11px;"><option value="">Todas</option><option value="retirada">Retirada</option><option value="entrega">Entrega</option></select></div>
        </div>

        @php $lista = $this->listagem(); @endphp
        <div style="display:grid;grid-template-columns:1fr {{ $this->detalheId ? '440px' : '0' }};gap:16px;align-items:start;transition:0.3s;">
            {{-- LISTA DE PEDIDOS --}}
            <div class="data-table-wrap">
                @if (count($lista) > 0)
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;padding:12px;">
                        @foreach ($lista as $p)
                            @php $st = $this->statusDoPedido($p); @endphp
                            <div wire:click="verDetalhe({{ $p->id }})" style="cursor:pointer;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:14px;transition:all 0.15s;{{ $st['atrasado'] ? 'border-left:4px solid var(--danger);' : '' }}{{ $this->detalheId === $p->id ? 'box-shadow:0 0 0 2px var(--primary-500);' : '' }}" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow='none'">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                                    <div>
                                        <span style="font-weight:800;font-size:14px;color:var(--text);">#{{ $p->id }}</span>
                                        <span style="font-size:11px;color:var(--muted);font-family:monospace;margin-left:4px;">{{ $p->codigo }}</span>
                                    </div>
                                    @php
                                        $badgeClass = match($p->status) {
                                            'recebido' => 'badge-ativo',
                                            'em_separacao' => 'badge-warning',
                                            'pronto_retirada','pronto_entrega','entregue' => 'badge-ativo',
                                            'cancelado' => 'badge-inativo',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge-sm {{ $badgeClass }}" style="font-size:8px;">{{ $p->status }}</span>
                                </div>
                                <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px;">{{ $p->cliente?->nome ?? '—' }}</div>
                                <div style="display:flex;gap:12px;font-size:11px;color:var(--muted);margin-bottom:6px;">
                                    <span><i class="fas fa-box"></i> {{ $p->itens->count() }} itens</span>
                                    <span><i class="fas fa-dollar-sign"></i> R$ {{ number_format($p->total, 2, ',', '.') }}</span>
                                    <span><i class="fas {{ $p->tipo_entrega === 'entrega' ? 'fa-truck' : 'fa-store' }}"></i> {{ $p->tipo_entrega }}</span>
                                </div>
                                @if ($st['atrasado'])
                                    <div style="font-size:11px;font-weight:700;color:var(--danger);display:flex;align-items:center;gap:4px;">
                                        <i class="fas fa-exclamation-circle"></i> ATRASADO {{ $st['minutos_atraso'] }}min
                                    </div>
                                @endif
                                @if ($p->separador)
                                    <div style="font-size:10px;color:var(--muted);margin-top:4px;">
                                        <i class="fas fa-user-cog"></i> Sep: {{ $p->separador->name }}
                                        @if ($p->entregador) | <i class="fas fa-motorcycle"></i> Ent: {{ $p->entregador->name }} @endif
                                    </div>
                                @endif
                                <div style="font-size:9px;color:var(--muted);margin-top:4px;">{{ $p->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center;padding:40px;color:var(--muted);">
                        <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                        <p style="font-size:16px;font-weight:600;color:var(--text);margin:0 0 4px;">Nenhum pedido encontrado</p>
                        <p style="font-size:13px;margin:0;">Os pedidos feitos no site aparecerao aqui.</p>
                    </div>
                @endif
                @if ($lista->hasPages())
                    <div style="padding:12px 16px;border-top:1px solid var(--border);">{{ $lista->links('livewire.pagination-custom') }}</div>
                @endif
            </div>

            {{-- PAINEL LATERAL DE DETALHE --}}
            @if ($this->detalheId)
                @php $d = $this->detalhe; @endphp
                @if ($d)
                    <div style="position:sticky;top:16px;">
                        <div style="border:1px solid var(--border);border-radius:16px;overflow:hidden;background:var(--surface);max-height:90vh;overflow-y:auto;">
                            <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                                <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text);">Pedido #{{ $d['id'] }}</h3>
                                <button wire:click="fecharDetalhe" style="background:none;border:0;color:var(--muted);cursor:pointer;font-size:20px;padding:0;">&times;</button>
                            </div>

                            <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Cliente</strong>{{ $d['cliente']['nome'] ?? '—' }}</div>
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Total</strong><span style="font-weight:800;color:var(--success);">R$ {{ number_format($d['total'], 2, ',', '.') }}</span></div>
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Data</strong>{{ \Carbon\Carbon::parse($d['created_at'])->format('d/m/Y H:i') }}</div>
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Codigo</strong><span style="font-family:monospace;">{{ $d['codigo'] }}</span></div>
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Tipo</strong>{{ $d['tipo_entrega'] }}</div>
                                    <div><strong style="color:var(--muted);display:block;font-size:10px;">Status</strong>
                                        @php $badgeClass = match($d['status']) { 'recebido'=>'badge-ativo','em_separacao'=>'badge-warning','pronto_retirada'=>'badge-ativo','entregue'=>'badge-ativo','cancelado'=>'badge-inativo', default => 'badge-warning' }; @endphp
                                        <span class="badge-sm {{ $badgeClass }}">{{ $d['status'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Atribuir operadores --}}
                            <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                    <div>
                                        <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:2px;">Separador</label>
                                        <select wire:model="separadorId" style="width:100%;height:30px;font-size:11px;padding:0 4px;border:1px solid var(--border);border-radius:5px;">
                                            <option value="">—</option>
                                            @foreach ($this->operadores as $op)
                                                <option value="{{ $op['id'] }}">{{ $op['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size:10px;color:var(--muted);display:block;margin-bottom:2px;">Entregador</label>
                                        <select wire:model="entregadorId" style="width:100%;height:30px;font-size:11px;padding:0 4px;border:1px solid var(--border);border-radius:5px;">
                                            <option value="">—</option>
                                            @foreach ($this->operadores as $op)
                                                <option value="{{ $op['id'] }}">{{ $op['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <button wire:click="salvarOperadores" class="btn-sm btn-primary" style="margin-top:6px;height:28px;font-size:10px;width:100%;"><i class="fas fa-save"></i> Salvar Operadores</button>
                            </div>

                            {{-- Itens do pedido --}}
                            <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                                <h4 style="margin:0 0 8px;font-size:13px;font-weight:700;color:var(--text);">Itens ({{ count($d['itens']) }})</h4>
                                @foreach ($d['itens'] as $item)
                                    @php $v = $item['variacao'] ?? []; @endphp
                                    <div style="padding:8px 0;border-bottom:1px solid color-mix(in srgb,var(--border)50%,transparent);">
                                        <div style="display:flex;justify-content:space-between;align-items:start;gap:6px;">
                                            <div style="flex:1;">
                                                <div style="font-size:12px;font-weight:600;color:var(--text);text-transform:uppercase;">{{ $v['nome_completo'] ?? '#' . $item['produto_variacao_id'] }}</div>
                                                <div style="font-size:10px;color:var(--muted);">{{ number_format($item['quantidade_solicitada'], 3, ',', '.') }} x R$ {{ number_format($item['preco_unitario'], 2, ',', '.') }}</div>
                                            </div>
                                            <div style="text-align:right;flex-shrink:0;">
                                                <span style="font-size:11px;font-weight:700;color:var(--text);">R$ {{ number_format($item['total_item'], 2, ',', '.') }}</span>
                                                <div style="margin-top:2px;">
                                                    @php $ic = match($item['status_item']) { 'pendente'=>'badge-warning', 'separado'=>'badge-ativo', 'faltou'=>'badge-inativo', 'substituido'=>'badge-ativo', 'cancelado'=>'badge-inativo', default=>'badge-warning' }; @endphp
                                                    <span class="badge-sm {{ $ic }}" style="font-size:8px;">{{ $item['status_item'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="display:flex;gap:4px;margin-top:4px;flex-wrap:wrap;">
                                            @if ($item['status_item'] === 'pendente')
                                                <button wire:click="alterarStatusItem({{ $item['id'] }}, 'separado')" class="btn-sm" style="padding:2px 6px;font-size:8px;height:22px;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);border:0;border-radius:4px;cursor:pointer;"><i class="fas fa-check"></i> Separar</button>
                                                <button wire:click="alterarStatusItem({{ $item['id'] }}, 'faltou')" class="btn-sm" style="padding:2px 6px;font-size:8px;height:22px;background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);border:0;border-radius:4px;cursor:pointer;"><i class="fas fa-times"></i> Faltou</button>
                                            @endif
                                            @if ($item['status_item'] === 'faltou' || $item['status_item'] === 'pendente')
                                                <button wire:click="sugerirSubstituto({{ $item['id'] }})" class="btn-sm" style="padding:2px 6px;font-size:8px;height:22px;background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);border:0;border-radius:4px;cursor:pointer;"><i class="fas fa-exchange-alt"></i> Substituto</button>
                                            @endif
                                            @if ($item['substituto_produto_variacao_id'] && $item['substituto'])
                                                <div style="font-size:10px;color:var(--muted);margin-top:2px;width:100%;">
                                                    Subst: <strong>{{ $item['substituto']['nome_completo'] ?? '#' . $item['substituto_produto_variacao_id'] }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Substitution UI --}}
                            @if ($this->itemSubstituir)
                                <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
                                    <h4 style="margin:0 0 8px;font-size:13px;font-weight:700;color:var(--text);">Buscar Substituto</h4>
                                    <input wire:model.live.debounce.300ms="buscaSubstituto" wire:input="buscarSubstituto" placeholder="Nome do produto..." style="width:100%;height:34px;padding:0 8px;border:1px solid var(--border);border-radius:6px;font-size:12px;margin-bottom:6px;">
                                    @if (count($this->resultadosSubstituto) > 0)
                                        @foreach ($this->resultadosSubstituto as $pr)
                                            <button wire:click="$set('substitutoId', {{ $pr['id'] }})" style="display:block;width:100%;text-align:left;padding:6px 8px;border:0;border-bottom:1px solid var(--border);background:{{ $this->substitutoId === $pr['id'] ? 'color-mix(in srgb,var(--primary-500)10%,transparent)' : 'transparent' }};cursor:pointer;font-size:11px;color:var(--text);">
                                                <strong>{{ $pr['nome_completo'] }}</strong>
                                                @if (isset($pr['marca']['nome'])) <span style="color:var(--muted);">— {{ $pr['marca']['nome'] }}</span> @endif
                                            </button>
                                        @endforeach
                                    @endif
                                    <div style="display:flex;gap:4px;margin-top:6px;">
                                        <button wire:click="confirmarSubstituicao" class="btn-sm btn-primary" style="height:28px;font-size:10px;">Confirmar</button>
                                        <button wire:click="cancelarSubstituicao" class="btn-sm btn-secondary" style="height:28px;font-size:10px;">Cancelar</button>
                                    </div>
                                </div>
                            @endif

                            {{-- Acoes --}}
                            <div style="padding:14px 20px;">
                                <h4 style="margin:0 0 8px;font-size:13px;font-weight:700;color:var(--text);">Acoes</h4>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                                    @php $acoes = ['recebido'=>'Recebido','confirmado'=>'Confirmar','em_separacao'=>'Separar','pronto_retirada'=>'Pronto Retirada','pronto_entrega'=>'Pronto Entrega','entregue'=>'Entregue','cancelado'=>'Cancelar']; @endphp
                                    @foreach ($acoes as $key => $label)
                                        @if ($key !== $d['status'])
                                            <button wire:click="alterarStatus({{ $d['id'] }}, '{{ $key }}')" class="btn-sm {{ $key === 'cancelado' ? 'btn-remove' : 'btn-secondary' }}" style="height:30px;font-size:10px;padding:0 6px;">{{ $label }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

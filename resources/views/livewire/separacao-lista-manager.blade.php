<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div style="max-width:480px;margin:0 auto;background:var(--surface);min-height:100vh;">
        {{-- HEADER --}}
        <header style="background:var(--primary-500);color:var(--on-primary);padding:14px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10;">
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="fas fa-box" style="color:var(--warning);font-size:18px;"></i>
                <span style="font-weight:800;font-size:16px;letter-spacing:1px;">SEPARACAO</span>
            </div>
            <span style="background:rgba(255,255,255,0.2);font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;">
                <i class="fas fa-bell" style="margin-right:4px;"></i> {{ $this->totalPendentes() }} pend.
            </span>
        </header>

        {{-- BUSCA --}}
        <div style="padding:12px 16px;background:color-mix(in srgb,var(--text)4%,transparent);border-bottom:1px solid var(--border);">
            <div style="position:relative;display:flex;align-items:center;">
                <i class="fas fa-search" style="position:absolute;left:12px;color:var(--muted);font-size:13px;"></i>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar pedido, cliente ou ID..." style="width:100%;padding:10px 12px 10px 36px;border:1px solid var(--border);border-radius:10px;font-size:13px;outline:none;background:var(--surface);">
            </div>
        </div>

        {{-- LISTA --}}
        <div style="padding:12px 16px;">
            @php $dados = $this->pendentes(); @endphp

            @if (!empty($dados['atrasados']))
                <div style="margin-bottom:20px;">
                    <h3 style="font-size:10px;font-weight:800;color:var(--danger);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--danger);"></span> Atrasados ({{ count($dados['atrasados']) }})
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach ($dados['atrasados'] as $p)
                            <div style="background:var(--surface);border-left:4px solid var(--danger);border-radius:0 12px 12px 0;border:1px solid var(--border);border-left-color:var(--danger);padding:14px;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px;">
                                    <div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="font-size:11px;font-weight:700;color:var(--muted);">#{{ $p['id'] }}</span>
                                            <span style="font-size:10px;color:color-mix(in srgb,var(--muted)60%,transparent);">{{ $p['created_at'] }}</span>
                                        </div>
                                        <h4 style="margin:0;font-weight:700;font-size:15px;color:var(--text);">{{ $p['cliente_nome'] }}</h4>
                                    </div>
                                    <span style="font-size:11px;color:var(--danger);font-weight:600;display:flex;align-items:center;gap:4px;background:color-mix(in srgb,var(--danger)8%,transparent);padding:2px 8px;border-radius:20px;white-space:nowrap;">
                                        <i class="far fa-clock"></i> {{ $p['tempo_atraso'] }}
                                    </span>
                                </div>
                                <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">
                                    <i class="fas fa-shopping-cart" style="margin-right:4px;"></i> {{ $p['total_itens'] }} itens · <strong style="color:var(--text);">R$ {{ number_format($p['total'], 2, ',', '.') }}</strong>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <div style="height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,transparent);overflow:hidden;">
                                        <div style="height:100%;border-radius:3px;width:{{ $p['progresso'] }}%;background:var(--danger);transition:width 0.3s;"></div>
                                    </div>
                                    <div style="font-size:10px;color:var(--muted);margin-top:2px;text-align:right;">{{ $p['progresso'] }}%</div>
                                </div>
                                <div style="display:flex;gap:6px;margin-bottom:10px;font-size:10px;">
                                    <span style="background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_separados'] }} OK</span>
                                    @if ($p['total_faltou'] > 0)
                                        <span style="background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_faltou'] }} Faltou</span>
                                    @endif
                                    @if ($p['total_substituidos'] > 0)
                                        <span style="background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_substituidos'] }} Subst.</span>
                                    @endif
                                    @if ($p['total_pendentes'] > 0)
                                        <span style="background:color-mix(in srgb,var(--muted)12%,transparent);color:var(--muted);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_pendentes'] }} Pend.</span>
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px;">
                                    <a href="/separacao/{{ $p['id'] }}" wire:navigate style="display:inline-flex;align-items:center;gap:4px;padding:8px 16px;border:0;border-radius:8px;background:var(--primary-500);color:var(--on-primary);font-weight:700;font-size:12px;cursor:pointer;text-decoration:none;">
                                        <i class="fas {{ $p['ja_iniciou'] ? 'fa-forward' : 'fa-play' }}" style="font-size:10px;"></i>
                                        {{ $p['ja_iniciou'] ? 'Continuar' : 'Iniciar' }}
                                    </a>
                                    <button wire:click.stop="cancelarPedido({{ $p['id'] }})" onclick="return confirm('Cancelar pedido #{{ $p['id'] }}?')" style="padding:8px 12px;border:1px solid color-mix(in srgb,var(--danger)40%,transparent);border-radius:8px;background:color-mix(in srgb,var(--danger)6%,transparent);color:var(--danger);font-weight:600;font-size:11px;cursor:pointer;">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($dados['normais']))
                <div>
                    <h3 style="font-size:10px;font-weight:800;color:var(--primary-600);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;display:flex;align-items:center;gap:6px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--primary-600);"></span> Pendentes ({{ count($dados['normais']) }})
                    </h3>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach ($dados['normais'] as $p)
                            <div style="background:var(--surface);border-left:4px solid var(--primary-600);border-radius:0 12px 12px 0;border:1px solid var(--border);border-left-color:var(--primary-600);padding:14px;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px;">
                                    <div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="font-size:11px;font-weight:700;color:var(--muted);">#{{ $p['id'] }}</span>
                                            <span style="font-size:10px;color:color-mix(in srgb,var(--muted)60%,transparent);">{{ $p['created_at'] }}</span>
                                        </div>
                                        <h4 style="margin:0;font-weight:700;font-size:15px;color:var(--text);">{{ $p['cliente_nome'] }}</h4>
                                    </div>
                                    <span style="font-size:11px;color:var(--muted);font-weight:500;display:flex;align-items:center;gap:4px;background:color-mix(in srgb,var(--text)6%,transparent);padding:2px 8px;border-radius:20px;white-space:nowrap;">
                                        <i class="far fa-clock"></i> {{ $p['tempo_atraso'] }}
                                    </span>
                                </div>
                                <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">
                                    <i class="fas fa-shopping-cart" style="margin-right:4px;"></i> {{ $p['total_itens'] }} itens · <strong style="color:var(--text);">R$ {{ number_format($p['total'], 2, ',', '.') }}</strong>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <div style="height:6px;border-radius:3px;background:color-mix(in srgb,var(--text)8%,transparent);overflow:hidden;">
                                        <div style="height:100%;border-radius:3px;width:{{ $p['progresso'] }}%;background:var(--primary-600);transition:width 0.3s;"></div>
                                    </div>
                                    <div style="font-size:10px;color:var(--muted);margin-top:2px;text-align:right;">{{ $p['progresso'] }}%</div>
                                </div>
                                <div style="display:flex;gap:6px;margin-bottom:10px;font-size:10px;">
                                    <span style="background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_separados'] }} OK</span>
                                    @if ($p['total_faltou'] > 0)
                                        <span style="background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_faltou'] }} Faltou</span>
                                    @endif
                                    @if ($p['total_substituidos'] > 0)
                                        <span style="background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_substituidos'] }} Subst.</span>
                                    @endif
                                    @if ($p['total_pendentes'] > 0)
                                        <span style="background:color-mix(in srgb,var(--muted)12%,transparent);color:var(--muted);padding:2px 6px;border-radius:4px;font-weight:600;">{{ $p['total_pendentes'] }} Pend.</span>
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px;">
                                    <a href="/separacao/{{ $p['id'] }}" wire:navigate style="display:inline-flex;align-items:center;gap:4px;padding:8px 16px;border:0;border-radius:8px;background:var(--primary-500);color:var(--on-primary);font-weight:700;font-size:12px;cursor:pointer;text-decoration:none;">
                                        <i class="fas {{ $p['ja_iniciou'] ? 'fa-forward' : 'fa-play' }}" style="font-size:10px;"></i>
                                        {{ $p['ja_iniciou'] ? 'Continuar' : 'Iniciar' }}
                                    </a>
                                    <button wire:click.stop="cancelarPedido({{ $p['id'] }})" onclick="return confirm('Cancelar pedido #{{ $p['id'] }}?')" style="padding:8px 12px;border:1px solid color-mix(in srgb,var(--danger)40%,transparent);border-radius:8px;background:color-mix(in srgb,var(--danger)6%,transparent);color:var(--danger);font-weight:600;font-size:11px;cursor:pointer;">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (empty($dados['atrasados']) && empty($dados['normais']))
                <div style="text-align:center;padding:60px 20px;color:var(--muted);">
                    <i class="fas fa-box-open" style="font-size:40px;margin-bottom:12px;opacity:0.3;display:block;"></i>
                    <p style="font-weight:600;color:var(--text);margin:0 0 4px;">Nenhum pedido para separar</p>
                    <p style="font-size:13px;margin:0;">Novos pedidos aparecerão aqui.</p>
                </div>
            @endif

            {{-- PAGINATION --}}
            @if ($dados['paginator']->hasPages())
                <div style="display:flex;justify-content:center;gap:4px;padding:16px 0;">
                    @if ($dados['paginator']->onFirstPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;color:var(--muted);font-size:11px;font-weight:600;">&laquo; Anterior</span>
                    @else
                        <button wire:click="previousPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">&laquo; Anterior</button>
                    @endif
                    @foreach ($dados['paginator']->getUrlRange(1, $dados['paginator']->lastPage()) as $page => $url)
                        @if ($page == $dados['paginator']->currentPage())
                            <span style="padding:6px 12px;border:0;border-radius:6px;background:var(--primary-500);color:var(--on-primary);font-size:11px;font-weight:700;">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">{{ $page }}</button>
                        @endif
                    @endforeach
                    @if ($dados['paginator']->onLastPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;color:var(--muted);font-size:11px;font-weight:600;">Próximo &raquo;</span>
                    @else
                        <button wire:click="nextPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">Próximo &raquo;</button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

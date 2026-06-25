<div x-data="{
    show: @entangle('toastShow'),
    msg: @entangle('toastMsg'),
    confirmando: false,
    destacado: false,
    handleKey(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === '1') { e.preventDefault(); $wire.definirStatus('ok'); }
        else if (e.key === '2') { e.preventDefault(); $wire.definirStatus('parcial'); }
        else if (e.key === '3') { e.preventDefault(); $wire.definirStatus('faltou'); }
        else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.confirmar(); }
        else if (e.key.toLowerCase() === 'p') { e.preventDefault(); $wire.pularItem(); }
    },
    confirmar() {
        this.confirmando = true;
        this.destacado = true;
        setTimeout(() => { this.destacado = false; }, 300);
        $wire.confirmarProximo().then(() => { this.confirmando = false; });
    }
}"
@keydown.window="handleKey"
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })">

    {{-- TOAST --}}
    <div x-show="show" x-cloak
         x-transition:enter="toast-enter" x-transition:leave="toast-leave"
         class="toast-fixed" style="z-index:999;">
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    @php $p = $this->pedido(); $item = $this->item; $prog = $this->progresso(); @endphp

    <div style="max-width:480px;margin:0 auto;background:linear-gradient(180deg,color-mix(in srgb,var(--warning)4%,transparent) 0%,color-mix(in srgb,var(--text)3%,transparent) 100%);min-height:100vh;display:flex;flex-direction:column;">

        {{-- ===== HEADER ===== --}}
        <header style="background:linear-gradient(135deg,color-mix(in srgb,var(--warning)85%,#000),color-mix(in srgb,var(--warning)60%,var(--text)));color:var(--on-primary);padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:20;box-shadow:0 2px 12px rgba(0,0,0,0.15);">
            <a href="/separacao" wire:navigate style="color:inherit;text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:4px;opacity:0.9;">
                <i class="fas fa-chevron-left"></i> Voltar
            </a>
            <span style="font-weight:800;font-size:14px;letter-spacing:0.5px;text-shadow:0 1px 4px rgba(0,0,0,0.2);">Pedido #{{ $this->pedidoId }}</span>
            @if ($p && $p->cliente && $p->cliente->whatsapp)
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $p->cliente->whatsapp) }}" target="_blank" style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;text-decoration:none;display:flex;align-items:center;gap:4px;backdrop-filter:blur(4px);">
                    <i class="fab fa-whatsapp"></i> Cliente
                </a>
            @else
                <span style="width:72px;"></span>
            @endif
        </header>

        @if (!$item && count($this->processados) === 0)
            {{-- NO ITEMS TO PROCESS --}}
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center;">
                <div style="width:80px;height:80px;border-radius:50%;background:color-mix(in srgb,var(--success)12%,transparent);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <i class="fas fa-check-circle" style="font-size:36px;color:var(--success);"></i>
                </div>
                <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 4px;">Nada pendente</h2>
                <p style="font-size:13px;color:var(--muted);margin:0 0 20px;">Todos os itens já foram processados.</p>
                <button wire:click="finalizarSeparacao" style="padding:12px 32px;border:0;border-radius:10px;background:var(--success);color:var(--on-success);font-weight:800;font-size:14px;cursor:pointer;">
                    <i class="fas fa-check-double" style="margin-right:6px;"></i> Finalizar
                </button>
                <a href="/separacao" wire:navigate style="display:block;margin-top:12px;font-size:13px;color:var(--muted);text-decoration:none;">Voltar para lista</a>
            </div>

        @elseif (!$item && count($this->itens) === 0)
            {{-- RESUME / FINAL --}}
            <div style="flex:1;padding:16px;display:flex;flex-direction:column;">
                <div style="flex:1;">
                    <div style="text-align:center;margin-bottom:16px;">
                        <div style="width:64px;height:64px;border-radius:50%;background:color-mix(in srgb,var(--success)12%,transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-check-circle" style="font-size:32px;color:var(--success);"></i>
                        </div>
                        <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0;">Lista completa!</h2>
                        <p style="font-size:13px;color:var(--muted);margin:4px 0 0;">Pedido #{{ $this->pedidoId }} — {{ $p?->cliente?->nome ?? '' }}</p>
                    </div>

                    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:4px 12px;margin-bottom:14px;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
                        @forelse ($this->processados as $pr)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid color-mix(in srgb,var(--text)6%,transparent);font-size:12px;">
                                <div style="flex:1;min-width:0;">
                                    <span style="font-weight:600;font-size:11px;text-transform:uppercase;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pr['nome'] }}</span>
                                    <span style="font-size:10px;color:var(--muted);">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }} un</span>
                                </div>
                                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap;
                                    {{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);' : ($pr['qtd_separada'] > 0 ? 'background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);' : 'background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);') }}">
                                    {{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'OK' : ($pr['qtd_separada'] > 0 ? 'PARCIAL' : 'FALTOU') }}
                                </span>
                            </div>
                        @endforeach
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:8px 0;">
                            <div style="background:color-mix(in srgb,var(--success)6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">OK</span>
                                <span style="font-size:18px;font-weight:900;color:var(--success);">{{ $prog['separados'] }}</span>
                            </div>
                            <div style="background:color-mix(in srgb,var(--danger)6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Faltou</span>
                                <span style="font-size:18px;font-weight:900;color:var(--danger);">{{ $prog['faltou'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button wire:click="finalizarSeparacao" style="width:100%;padding:14px;border:0;border-radius:12px;background:linear-gradient(135deg,var(--success),color-mix(in srgb,var(--success)80%,#000));color:var(--on-success);font-weight:800;font-size:15px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
                    <i class="fas fa-check-double" style="margin-right:6px;"></i> Confirmar e Finalizar
                </button>
                <a href="/separacao" wire:navigate style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--muted);text-decoration:none;padding:6px;">Voltar para lista</a>
            </div>

        @else
            {{-- ===== ITEM ATUAL ===== --}}
            <div style="flex:1;padding:16px 16px 100px;display:flex;flex-direction:column;">

                {{-- Progress bar segmentada --}}
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px;">
                        <span>Item <strong style="color:var(--warning);">{{ $prog['feitos'] + 1 }}</strong> de {{ $prog['total'] }}</span>
                        <span>{{ $prog['pct'] }}%</span>
                    </div>
                    <div style="height:8px;border-radius:4px;display:flex;gap:2px;overflow:hidden;">
                        @php
                            $segments = [];
                            if ($prog['pctOk'] > 0) $segments[] = ['w' => $prog['pctOk'], 'c' => 'var(--success)'];
                            if ($prog['pctSubst'] > 0) $segments[] = ['w' => $prog['pctSubst'], 'c' => 'var(--warning)'];
                            if ($prog['pctFaltou'] > 0) $segments[] = ['w' => $prog['pctFaltou'], 'c' => 'var(--danger)'];
                            $restante = 100 - $prog['pct'];
                            if ($restante > 0) $segments[] = ['w' => $restante, 'c' => 'color-mix(in srgb,var(--text)10%,transparent)'];
                        @endphp
                        @foreach ($segments as $seg)
                            <div style="height:100%;width:{{ $seg['w'] }}%;background:{{ $seg['c'] }};border-radius:4px;transition:width 0.4s ease;"></div>
                        @endforeach
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;font-size:9px;font-weight:600;color:var(--muted);">
                        <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--success);vertical-align:middle;margin-right:2px;"></span> {{ $prog['separados'] }} OK</span>
                        @if ($prog['faltou'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--danger);vertical-align:middle;margin-right:2px;"></span> {{ $prog['faltou'] }} Faltou</span>
                        @endif
                        @if ($prog['substituidos'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--warning);vertical-align:middle;margin-right:2px;"></span> {{ $prog['substituidos'] }} Subst.</span>
                        @endif
                    </div>
                </div>

                {{-- Step dots --}}
                @if (count($this->itens) > 1)
                    <div style="display:flex;gap:4px;justify-content:center;margin-bottom:12px;">
                        @foreach ($this->itens as $idx => $i)
                            <span style="width:6px;height:6px;border-radius:50%;
                                {{ $idx < $this->itemAtual ? 'background:var(--success);' : ($idx == $this->itemAtual ? 'background:var(--warning);width:10px;height:10px;border:2px solid color-mix(in srgb,var(--warning)40%,transparent);' : 'background:color-mix(in srgb,var(--text)12%,transparent);') }}">
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- ITEM CARD --}}
                <div :class="{ 'ring-2 ring-offset-2': destacado }"
                     style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 4px 20px rgba(0,0,0,0.04);transition:box-shadow 0.2s, border-color 0.2s;flex-shrink:0;">

                    {{-- Localização --}}
                    <div style="display:flex;align-items:center;gap:6px;background:color-mix(in srgb,var(--warning)8%,transparent);color:var(--warning);padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:12px;">
                        <i class="fas fa-map-pin"></i>
                        <span>Corredor —</span>
                    </div>

                    {{-- Nome + SKU --}}
                    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:14px;">
                        <div style="flex:1;min-width:0;">
                            <h2 style="font-size:16px;font-weight:800;color:var(--text);margin:0 0 2px;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item['nome'] }}</h2>
                            <p style="font-size:11px;color:var(--muted);margin:0;">SKU: {{ $item['sku'] ?: '—' }}</p>
                        </div>
                        <span style="font-size:10px;font-weight:800;padding:4px 10px;border-radius:20px;background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);white-space:nowrap;">
                            {{ number_format($item['qtd_pedido'], 0, ',', '.') }} un
                        </span>
                    </div>

                    {{-- QTD selector --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;background:color-mix(in srgb,var(--text)3%,transparent);border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-bottom:12px;">
                        <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Separando</span>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <button wire:click="decrementar" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)4%,transparent)'" @mouseleave="$el.style.background='var(--surface)'">−</button>
                            <input type="number" wire:model.blur="itens.{{ $this->itemAtual }}.qtd_separada" style="width:52px;height:32px;text-align:center;border:2px solid var(--warning);border-radius:8px;font-size:16px;font-weight:800;outline:none;background:var(--surface);">
                            <button wire:click="incrementar" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)4%,transparent)'" @mouseleave="$el.style.background='var(--surface)'">+</button>
                        </div>
                    </div>

                    {{-- Ações rápidas: OK / Parcial / Faltou --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:12px;">
                        <button wire:click="definirStatus('ok')"
                                style="padding:10px 6px;border:2px solid var(--success);border-radius:10px;background:color-mix(in srgb,var(--success)6%,transparent);color:var(--success);font-weight:700;font-size:11px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,var(--success)14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,var(--success)6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-check-circle" style="display:block;font-size:18px;margin-bottom:2px;"></i> OK <span style="font-size:9px;opacity:0.6;">[1]</span>
                        </button>
                        <button wire:click="definirStatus('parcial')"
                                style="padding:10px 6px;border:2px solid var(--warning);border-radius:10px;background:color-mix(in srgb,var(--warning)6%,transparent);color:var(--warning);font-weight:700;font-size:11px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,var(--warning)14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,var(--warning)6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-exclamation-triangle" style="display:block;font-size:18px;margin-bottom:2px;"></i> Parcial <span style="font-size:9px;opacity:0.6;">[2]</span>
                        </button>
                        <button wire:click="definirStatus('faltou')"
                                style="padding:10px 6px;border:2px solid var(--danger);border-radius:10px;background:color-mix(in srgb,var(--danger)6%,transparent);color:var(--danger);font-weight:700;font-size:11px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,var(--danger)14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,var(--danger)6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-times-circle" style="display:block;font-size:18px;margin-bottom:2px;"></i> Faltou <span style="font-size:9px;opacity:0.6;">[3]</span>
                        </button>
                    </div>

                    {{-- Observação --}}
                    <div>
                        <label style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:4px;">Observacao</label>
                        <input wire:model="itens.{{ $this->itemAtual }}.observacao" type="text" placeholder="Motivo (opcional)..." style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;outline:none;background:var(--surface);">
                    </div>
                </div>

                {{-- Teclas de atalho hint (fixo no canto) --}}
                <div style="text-align:center;margin-top:8px;font-size:10px;color:color-mix(in srgb,var(--muted)50%,transparent);">
                    <i class="fas fa-keyboard" style="margin-right:4px;"></i>
                    1=OK 2=Parcial 3=Faltou Espaço=Confirmar P=Pular
                </div>
            </div>

            {{-- ===== ACTIONS FIXAS ===== --}}
            <div style="position:sticky;bottom:0;z-index:20;padding:12px 16px;background:var(--surface);border-top:1px solid var(--border);display:flex;gap:8px;box-shadow:0 -4px 20px rgba(0,0,0,0.04);">
                <button wire:click="pularItem" style="flex:1;padding:14px 12px;border:1px solid var(--border);border-radius:12px;background:color-mix(in srgb,var(--text)3%,transparent);cursor:pointer;font-weight:700;font-size:14px;color:var(--text);transition:all 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)6%,transparent)'" @mouseleave="$el.style.background='color-mix(in srgb,var(--text)3%,transparent)'">
                    <i class="fas fa-forward" style="margin-right:6px;"></i> Pular <span style="font-size:10px;opacity:0.5;">[P]</span>
                </button>
                <button @click="confirmar()"
                        x-bind:disabled="confirmando"
                        style="flex:2;padding:14px 12px;border:0;border-radius:12px;background:linear-gradient(135deg,var(--success),color-mix(in srgb,var(--success)80%,#000));color:var(--on-success);cursor:pointer;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity 0.2s,transform 0.1s;box-shadow:0 4px 14px color-mix(in srgb,var(--success)30%,transparent);"
                        @mouseenter="if(!confirmando)$el.style.transform='translateY(-1px)'"
                        @mouseleave="$el.style.transform='translateY(0)'">
                    <span x-show="!confirmando">Confirmar <i class="fas fa-arrow-right"></i> <span style="font-size:10px;opacity:0.6;">[Espaço]</span></span>
                    <span x-show="confirmando" x-cloak><i class="fas fa-spinner fa-pulse"></i> Salvando...</span>
                </button>
            </div>

            {{-- ===== ITENS PROCESSADOS ===== --}}
            @if (count($this->processados) > 0)
                <div style="border-top:1px solid var(--border);background:color-mix(in srgb,var(--text)2%,transparent);">
                    <div style="padding:7px 16px;font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;">
                        <span>Itens processados</span>
                        <span style="background:color-mix(in srgb,var(--text)6%,transparent);padding:1px 8px;border-radius:10px;">{{ count($this->processados) }}</span>
                    </div>
                    @foreach ($this->processados as $pr)
                        <div style="display:flex;justify-content:space-between;padding:5px 16px;border-top:1px solid color-mix(in srgb,var(--text)4%,transparent);font-size:11px;">
                            <span style="font-weight:600;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px;">{{ $pr['nome'] }}</span>
                            <span style="white-space:nowrap;{{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'color:var(--success)' : ($pr['qtd_separada'] > 0 ? 'color:var(--warning)' : 'color:var(--danger)') }};">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>

    {{-- CSS scope --}}
    <style>
        .ring-2 { border-color: var(--warning) !important; box-shadow: 0 0 0 3px color-mix(in srgb,var(--warning)30%,transparent) !important; }
        .toast-fixed { position:fixed;top:16px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;z-index:999;display:flex;align-items:center;gap:8px;box-shadow:0 8px 30px rgba(0,0,0,0.15);max-width:90%; }
        .toast-icon { width:20px;height:20px;border-radius:50%;background:var(--success);display:flex;align-items:center;justify-content:center;font-size:10px; }
        .toast-enter { transition:all 0.3s ease-out; }
        .toast-leave { transition:all 0.2s ease-in; }
        .toast-enter-start { opacity:0;transform:translateX(-50%) translateY(-16px) !important; }
        .toast-leave-end { opacity:0;transform:translateX(-50%) translateY(-16px) !important; }
        [x-cloak] { display:none !important; }
    </style>
</div>

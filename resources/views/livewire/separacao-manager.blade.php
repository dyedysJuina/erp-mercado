@push('head')
<meta name="theme-color" content="#f59e0b">
<script src="/js/html5-qrcode.min.js"></script>
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
@endpush

<div x-data="{
    show: @entangle('toastShow'),
    msg: @entangle('toastMsg'),
    confirmando: false,
    destacado: false,
    showScanner: false,
    scanError: null,
    qr: null,
    handleKey(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'Escape') { this.closeScanner(); return; }
        if (e.key === '1') { e.preventDefault(); $wire.definirStatus('ok'); }
        else if (e.key === '2') { e.preventDefault(); $wire.definirStatus('parcial'); }
        else if (e.key === '3') { e.preventDefault(); $wire.definirStatus('faltou'); }
        else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.confirmar(); }
        else if (e.key.toLowerCase() === 'p') { e.preventDefault(); $wire.pularItem(); }
    },
    openScanner() {
        this.showScanner = true; this.scanError = null;
        this.$nextTick(() => {
            const el = document.getElementById('scanner-elem');
            if (!el || typeof Html5Qrcode === 'undefined') { this.scanError = 'Câmera não disponível'; return; }
            if (!window.isSecureContext && location.protocol !== 'https:') { this.scanError = 'Scanner exige HTTPS'; return; }
            this.qr = new Html5Qrcode('scanner-elem');
            this.qr.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 150 } },
                (texto) => {
                    $wire.buscarPorCodigoBarras(texto);
                    this.closeScanner();
                }
            ).catch(e => { this.scanError = e.message; });
        });
    },
    closeScanner() {
        if (this.qr) { this.qr.stop().catch(() => {}); this.qr = null; }
        this.showScanner = false;
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

    {{-- BLOQUEIO --}}
    @if ($this->bloqueioErro)
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center;">
            <div style="width:80px;height:80px;border-radius:50%;background:color-mix(in srgb,#ef4444,12%,transparent);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                <i class="fas fa-lock" style="font-size:32px;color:#ef4444;"></i>
            </div>
            <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 8px;">Pedido em uso</h2>
            <p style="font-size:13px;color:var(--muted);margin:0 0 20px;">{{ $this->bloqueioErro }}</p>
            <a href="/separacao" wire:navigate style="padding:12px 24px;border:0;border-radius:10px;background:#f59e0b;color:#fff;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;">
                <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Voltar para lista
            </a>
        </div>
    @else

    <div style="max-width:480px;margin:0 auto;background:linear-gradient(180deg,color-mix(in srgb,#f59e0b,4%,transparent) 0%,color-mix(in srgb,var(--text)3%,transparent) 100%);min-height:100vh;display:flex;flex-direction:column;">

        {{-- ===== HEADER ===== --}}
        <header style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:20;box-shadow:0 2px 12px rgba(0,0,0,0.15);">
            <a href="/separacao" wire:navigate style="color:#fff;text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:4px;opacity:0.9;">
                <i class="fas fa-chevron-left"></i> Voltar
            </a>
            <span style="font-weight:800;font-size:14px;letter-spacing:0.5px;text-shadow:0 1px 4px rgba(0,0,0,0.2);">Pedido #{{ $this->pedidoId }}</span>
            @if ($p && $p->cliente && $p->cliente->whatsapp)
                <div style="display:flex;gap:4px;">
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $p->cliente->whatsapp) }}" target="_blank" style="background:rgba(255,255,255,0.2);color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;text-decoration:none;display:flex;align-items:center;gap:4px;backdrop-filter:blur(4px);">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <div style="position:relative;" x-data="{ open: false }">
                        <button @click="open = !open" style="background:rgba(255,255,255,0.2);color:#fff;font-size:10px;font-weight:700;padding:4px 8px;border:0;border-radius:20px;cursor:pointer;backdrop-filter:blur(4px);">&#9660;</button>
                        <div x-show="open" @click.away="open = false" x-cloak style="position:absolute;top:100%;right:0;margin-top:4px;background:var(--surface);border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.1);z-index:30;min-width:200px;overflow:hidden;">
                            <a href="{{ $this->msgWhatsApp('iniciando') }}" target="_blank" style="display:block;padding:8px 12px;font-size:11px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border);">Iniciando separação</a>
                            <a href="{{ $this->msgWhatsApp('faltou') }}" target="_blank" style="display:block;padding:8px 12px;font-size:11px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border);">Item em falta</a>
                            <a href="{{ $this->msgWhatsApp('substituicao') }}" target="_blank" style="display:block;padding:8px 12px;font-size:11px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border);">Precisa substituir</a>
                            <a href="{{ $this->msgWhatsApp('pronto') }}" target="_blank" style="display:block;padding:8px 12px;font-size:11px;color:var(--text);text-decoration:none;">Pedido pronto</a>
                        </div>
                    </div>
                </div>
            @else
                <span style="width:72px;"></span>
            @endif
        </header>

        @if (!$item && count($this->itens) === 0 && count($this->processados) === 0 && count($this->cancelados) === 0)
            {{-- NO ITEMS AT ALL --}}
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;text-align:center;">
                <div style="width:80px;height:80px;border-radius:50%;background:color-mix(in srgb,#22c55e,12%,transparent);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <i class="fas fa-check-circle" style="font-size:36px;color:#22c55e;"></i>
                </div>
                <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 4px;">Nada pendente</h2>
                <p style="font-size:13px;color:var(--muted);margin:0 0 20px;">Todos os itens já foram processados.</p>
                <button wire:click="finalizarSeparacao" style="padding:12px 32px;border:0;border-radius:10px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-weight:800;font-size:14px;cursor:pointer;">
                    <i class="fas fa-check-double" style="margin-right:6px;"></i> Finalizar
                </button>
                <a href="/separacao" wire:navigate style="display:block;margin-top:12px;font-size:13px;color:var(--muted);text-decoration:none;">Voltar para lista</a>
            </div>

        @elseif (!$item && count($this->itens) === 0)
            {{-- RESUME / FINAL --}}
            <div style="flex:1;padding:16px;display:flex;flex-direction:column;">
                <div style="flex:1;">
                    <div style="text-align:center;margin-bottom:16px;">
                        <div style="width:64px;height:64px;border-radius:50%;background:color-mix(in srgb,#22c55e,12%,transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                            <i class="fas fa-check-circle" style="font-size:32px;color:#22c55e;"></i>
                        </div>
                        <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0;">Lista completa!</h2>
                        <p style="font-size:13px;color:var(--muted);margin:4px 0 0;">Pedido #{{ $this->pedidoId }} — {{ $p?->cliente?->nome ?? '' }}</p>
                        <a href="/pedidos-online/{{ $this->pedidoId }}" wire:navigate style="display:inline-block;margin-top:6px;font-size:12px;color:#2563eb;text-decoration:none;font-weight:600;">
                            <i class="fas fa-external-link-alt" style="margin-right:4px;"></i> Ver pedido completo
                        </a>
                    </div>

                    <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:4px 12px;margin-bottom:14px;box-shadow:0 1px 6px rgba(0,0,0,0.04);">
                        @forelse ($this->processados as $pr)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid color-mix(in srgb,var(--text)6%,transparent);font-size:12px;">
                                <div style="flex:1;min-width:0;">
                                    <span style="font-weight:600;font-size:11px;text-transform:uppercase;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pr['nome'] }}</span>
                                    <span style="font-size:10px;color:var(--muted);">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }} un</span>
                                </div>
                                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap;
                                    {{ $pr['status'] === 'quantidade_alterada' ? 'background:color-mix(in srgb,#3b82f6,12%,transparent);color:#3b82f6;' : ($pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'background:color-mix(in srgb,#22c55e,12%,transparent);color:#22c55e;' : ($pr['qtd_separada'] > 0 ? 'background:color-mix(in srgb,#f59e0b,12%,transparent);color:#f59e0b;' : 'background:color-mix(in srgb,#ef4444,12%,transparent);color:#ef4444;')) }}">
                                    {{ $pr['status'] === 'quantidade_alterada' ? 'QTD ALT' : ($pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'OK' : ($pr['qtd_separada'] > 0 ? 'PARCIAL' : 'FALTOU')) }}
                                </span>
                            </div>
                        @endforeach
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:8px 0;">
                            <div style="background:color-mix(in srgb,#22c55e,6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">OK</span>
                                <span style="font-size:18px;font-weight:900;color:#22c55e;">{{ $prog['separados'] }}</span>
                            </div>
                            <div style="background:color-mix(in srgb,#ef4444,6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Faltou</span>
                                <span style="font-size:18px;font-weight:900;color:#ef4444;">{{ $prog['faltou'] }}</span>
                            </div>
                            @if ($prog['altQtd'] > 0)
                            <div style="background:color-mix(in srgb,#3b82f6,6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Qtd Alt.</span>
                                <span style="font-size:18px;font-weight:900;color:#3b82f6;">{{ $prog['altQtd'] }}</span>
                            </div>
                            @endif
                            @if ($prog['substituidos'] > 0)
                            <div style="background:color-mix(in srgb,#f59e0b,6%,transparent);border-radius:8px;padding:8px;text-align:center;">
                                <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Subst.</span>
                                <span style="font-size:18px;font-weight:900;color:#f59e0b;">{{ $prog['substituidos'] }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Cancelados --}}
                        @if (count($this->cancelados) > 0)
                            <div style="border-top:1px solid color-mix(in srgb,var(--text)8%,transparent);padding:8px 0;">
                                <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">
                                    <i class="fas fa-ban" style="color:#ef4444;margin-right:4px;"></i> Cancelados ({{ count($this->cancelados) }})
                                </div>
                                @foreach ($this->cancelados as $c)
                                    <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:color-mix(in srgb,var(--muted)50%,transparent);">
                                        <span style="text-decoration:line-through;">{{ $c['nome'] }}</span>
                                        <span style="background:color-mix(in srgb,#6b7280,12%,transparent);color:#6b7280;padding:1px 6px;border-radius:4px;font-size:9px;font-weight:600;">Cancelado</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <button wire:click="finalizarSeparacao" style="width:100%;padding:14px;border:0;border-radius:12px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-weight:800;font-size:15px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
                    <i class="fas fa-check-double" style="margin-right:6px;"></i> Confirmar e Finalizar
                </button>
                <a href="/separacao" wire:navigate style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--muted);text-decoration:none;padding:6px;">Voltar para lista</a>
            </div>

        @else
            {{-- ===== ITEM ATUAL ===== --}}
            <div style="flex:1;padding:16px 16px 100px;display:flex;flex-direction:column;">

                {{-- Header info + link pedido --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:11px;font-weight:600;color:var(--muted);">
                        Pedido #{{ $this->pedidoId }}
                        @if ($p && $p->cliente)
                            · {{ $p->cliente->nome }}
                        @endif
                    </span>
                    <a href="/pedidos-online/{{ $this->pedidoId }}" wire:navigate style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:600;">
                        <i class="fas fa-external-link-alt"></i> Ver pedido
                    </a>
                </div>

                {{-- Progress bar segmentada --}}
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px;">
                        <span>Item <strong style="color:#f59e0b;">{{ $prog['feitos'] + 1 }}</strong> de {{ $prog['total'] }}</span>
                        <span>{{ $prog['pct'] }}%</span>
                    </div>
                    <div style="height:8px;border-radius:4px;display:flex;gap:2px;overflow:hidden;">
                        @php
                            $segments = [];
                            if ($prog['pctOk'] > 0) $segments[] = ['w' => $prog['pctOk'], 'c' => '#22c55e'];
                            if ($prog['pctSubst'] > 0) $segments[] = ['w' => $prog['pctSubst'], 'c' => '#f59e0b'];
                            if ($prog['pctFaltou'] > 0) $segments[] = ['w' => $prog['pctFaltou'], 'c' => '#ef4444'];
                            $restante = 100 - $prog['pct'];
                            if ($restante > 0) $segments[] = ['w' => $restante, 'c' => 'color-mix(in srgb,var(--text)10%,transparent)'];
                        @endphp
                        @foreach ($segments as $seg)
                            <div style="height:100%;width:{{ $seg['w'] }}%;background:{{ $seg['c'] }};border-radius:4px;transition:width 0.4s ease;"></div>
                        @endforeach
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;font-size:9px;font-weight:600;color:var(--muted);">
                        <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#22c55e;vertical-align:middle;margin-right:2px;"></span> {{ $prog['separados'] }} OK</span>
                        @if ($prog['faltou'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#ef4444;vertical-align:middle;margin-right:2px;"></span> {{ $prog['faltou'] }} Faltou</span>
                        @endif
                        @if ($prog['substituidos'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#f59e0b;vertical-align:middle;margin-right:2px;"></span> {{ $prog['substituidos'] }} Subst.</span>
                        @endif
                        @if ($prog['altQtd'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#3b82f6;vertical-align:middle;margin-right:2px;"></span> {{ $prog['altQtd'] }} Qtd Alt.</span>
                        @endif
                        @if ($prog['cancelados'] > 0)
                            <span><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#6b7280;vertical-align:middle;margin-right:2px;"></span> {{ $prog['cancelados'] }} Cancel.</span>
                        @endif
                    </div>
                </div>

                {{-- Step dots --}}
                @if (count($this->itens) > 1)
                    <div style="display:flex;gap:4px;justify-content:center;margin-bottom:12px;">
                        @foreach ($this->itens as $idx => $i)
                            <span style="width:6px;height:6px;border-radius:50%;
                                {{ $idx < $this->itemAtual ? 'background:#22c55e;' : ($idx == $this->itemAtual ? 'background:#f59e0b;width:10px;height:10px;border:2px solid color-mix(in srgb,#f59e0b,40%,transparent);' : 'background:color-mix(in srgb,var(--text)12%,transparent);') }}">
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- ITEM CARD --}}
                <div :class="{ 'ring-2 ring-offset-2': destacado }"
                     style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px;box-shadow:0 4px 20px rgba(0,0,0,0.04);transition:box-shadow 0.2s, border-color 0.2s;flex-shrink:0;">

                    {{-- Localização --}}
                    <div style="display:flex;align-items:center;gap:6px;{{ !empty($item['localizacao']) ? 'background:color-mix(in srgb,#f59e0b,8%,transparent);color:#f59e0b;' : 'background:color-mix(in srgb,var(--text)4%,transparent);color:var(--muted);' }}padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:12px;">
                        <i class="fas fa-map-pin"></i>
                        <span>{{ $item['localizacao'] ?: 'Sem localização' }}</span>
                    </div>

                    {{-- Nome + SKU + Foto --}}
                    <div style="display:flex;gap:12px;margin-bottom:14px;">
                        @if (!empty($item['foto']))
                            <img src="{{ $item['foto'] }}" alt="" style="width:56px;height:56px;border-radius:10px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;" onerror="this.style.display='none'">
                        @endif
                        <div style="flex:1;min-width:0;">
                            <h2 style="font-size:16px;font-weight:800;color:var(--text);margin:0 0 2px;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item['nome'] }}</h2>
                            <p style="font-size:11px;color:var(--muted);margin:0;">SKU: {{ $item['sku'] ?: '—' }}</p>
                            @if (!empty($item['categoria']) && $item['categoria'] !== 'Geral')
                                <p style="font-size:10px;color:color-mix(in srgb,var(--muted)60%,transparent);margin:2px 0 0;"><i class="fas fa-tag" style="font-size:8px;"></i> {{ $item['categoria'] }}</p>
                            @endif
                        </div>
                        <span style="font-size:10px;font-weight:800;padding:4px 10px;border-radius:20px;background:color-mix(in srgb,#f59e0b,12%,transparent);color:#f59e0b;white-space:nowrap;height:fit-content;">
                            {{ number_format($item['qtd_pedido'], 0, ',', '.') }} un
                        </span>
                    </div>

                    {{-- QTD selector + Scanner --}}
                    <div style="display:flex;align-items:center;gap:6px;background:color-mix(in srgb,var(--text)3%,transparent);border:1px solid var(--border);border-radius:12px;padding:10px 12px;margin-bottom:12px;">
                        <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-right:auto;">Separando</span>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <button wire:click="decrementar" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)4%,transparent)'" @mouseleave="$el.style.background='var(--surface)'">−</button>
                            <input type="number" wire:model.blur="itens.{{ $this->itemAtual }}.qtd_separada" style="width:52px;height:32px;text-align:center;border:2px solid #f59e0b;border-radius:8px;font-size:16px;font-weight:800;outline:none;background:var(--surface);">
                            <button wire:click="incrementar" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;font-weight:700;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)4%,transparent)'" @mouseleave="$el.style.background='var(--surface)'">+</button>
                        </div>
                        <button @click="openScanner()" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,#3b82f6,8%,transparent)';$el.style.color='#3b82f6';$el.style.borderColor='#3b82f6'" @mouseleave="$el.style.background='var(--surface)';$el.style.color='var(--muted)';$el.style.borderColor='var(--border)'" title="Ler código de barras">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>

                    {{-- Ações rápidas + Substituir --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:4px;margin-bottom:12px;">
                        <button wire:click="definirStatus('ok')"
                                style="padding:8px 4px;border:2px solid #22c55e;border-radius:10px;background:color-mix(in srgb,#22c55e,6%,transparent);color:#22c55e;font-weight:700;font-size:10px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,#22c55e,14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,#22c55e,6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-check-circle" style="display:block;font-size:16px;margin-bottom:2px;"></i> OK <span style="font-size:8px;opacity:0.6;">[1]</span>
                        </button>
                        <button wire:click="definirStatus('parcial')"
                                style="padding:8px 4px;border:2px solid #f59e0b;border-radius:10px;background:color-mix(in srgb,#f59e0b,6%,transparent);color:#f59e0b;font-weight:700;font-size:10px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,#f59e0b,14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,#f59e0b,6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-exclamation-triangle" style="display:block;font-size:16px;margin-bottom:2px;"></i> Parcial <span style="font-size:8px;opacity:0.6;">[2]</span>
                        </button>
                        <button wire:click="definirStatus('faltou')"
                                style="padding:8px 4px;border:2px solid #ef4444;border-radius:10px;background:color-mix(in srgb,#ef4444,6%,transparent);color:#ef4444;font-weight:700;font-size:10px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,#ef4444,14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,#ef4444,6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-times-circle" style="display:block;font-size:16px;margin-bottom:2px;"></i> Faltou <span style="font-size:8px;opacity:0.6;">[3]</span>
                        </button>
                        <button wire:click="abrirSubstituto"
                                style="padding:8px 4px;border:2px solid #3b82f6;border-radius:10px;background:color-mix(in srgb,#3b82f6,6%,transparent);color:#3b82f6;font-weight:700;font-size:10px;cursor:pointer;text-align:center;transition:all 0.15s;"
                                @mouseenter="$el.style.background='color-mix(in srgb,#3b82f6,14%,transparent)';$el.style.transform='scale(1.02)'"
                                @mouseleave="$el.style.background='color-mix(in srgb,#3b82f6,6%,transparent)';$el.style.transform='scale(1)'">
                            <i class="fas fa-exchange-alt" style="display:block;font-size:16px;margin-bottom:2px;"></i> Subst. <span style="font-size:8px;opacity:0.6;">[4]</span>
                        </button>
                    </div>

                    {{-- Observação --}}
                    <div>
                        <label style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:4px;">Observacao</label>
                        <input wire:model="itens.{{ $this->itemAtual }}.observacao" type="text" placeholder="Motivo (opcional)..." style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;outline:none;background:var(--surface);">
                    </div>
                </div>

                {{-- Atalhos hint --}}
                <div style="text-align:center;margin-top:8px;font-size:10px;color:color-mix(in srgb,var(--muted)50%,transparent);">
                    <i class="fas fa-keyboard" style="margin-right:4px;"></i>
                    1=OK 2=Parcial 3=Faltou 4=Subst. Espaço=Confirmar P=Pular
                </div>
            </div>

            {{-- ===== ACTIONS FIXAS ===== --}}
            <div style="position:sticky;bottom:0;z-index:20;padding:12px 16px;background:var(--surface);border-top:1px solid var(--border);display:flex;gap:8px;box-shadow:0 -4px 20px rgba(0,0,0,0.04);">
                <button wire:click="pularItem" style="flex:1;padding:14px 12px;border:1px solid var(--border);border-radius:12px;background:color-mix(in srgb,var(--text)3%,transparent);cursor:pointer;font-weight:700;font-size:14px;color:var(--text);transition:all 0.15s;" @mouseenter="$el.style.background='color-mix(in srgb,var(--text)6%,transparent)'" @mouseleave="$el.style.background='color-mix(in srgb,var(--text)3%,transparent)'">
                    <i class="fas fa-forward" style="margin-right:6px;"></i> Pular <span style="font-size:10px;opacity:0.5;">[P]</span>
                </button>
                <button @click="confirmar()"
                        x-bind:disabled="confirmando"
                        style="flex:2;padding:14px 12px;border:0;border-radius:12px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;cursor:pointer;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;gap:6px;transition:opacity 0.2s,transform 0.1s;box-shadow:0 4px 14px color-mix(in srgb,#22c55e,30%,transparent);"
                        @mouseenter="if(!confirmando)$el.style.transform='translateY(-1px)'"
                        @mouseleave="$el.style.transform='translateY(0)'">
                    <span x-show="!confirmando">Confirmar <i class="fas fa-arrow-right"></i> <span style="font-size:10px;opacity:0.6;">[Espaço]</span></span>
                    <span x-show="confirmando" x-cloak><i class="fas fa-spinner fa-pulse"></i> Salvando...</span>
                </button>
            </div>

            {{-- ===== ITENS PROCESSADOS + CANCELADOS ===== --}}
            @if (count($this->processados) > 0 || count($this->cancelados) > 0)
                <div style="border-top:1px solid var(--border);background:color-mix(in srgb,var(--text)2%,transparent);">
                    @if (count($this->processados) > 0)
                        <div style="padding:7px 16px;font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid color-mix(in srgb,var(--text)4%,transparent);">
                            <span><i class="fas fa-check" style="color:#22c55e;margin-right:4px;"></i> Processados</span>
                            <span style="background:color-mix(in srgb,var(--text)6%,transparent);padding:1px 8px;border-radius:10px;">{{ count($this->processados) }}</span>
                        </div>
                        @php $catAtual = null; @endphp
                        @foreach ($this->processados as $pr)
                            @if ($pr['categoria'] ?? 'Geral' !== $catAtual)
                                @php $catAtual = $pr['categoria'] ?? 'Geral'; @endphp
                                <div style="padding:3px 16px;font-size:9px;font-weight:700;color:color-mix(in srgb,var(--muted)50%,transparent);text-transform:uppercase;letter-spacing:1px;background:color-mix(in srgb,var(--text)2%,transparent);border-bottom:1px solid color-mix(in srgb,var(--text)4%,transparent);">{{ $catAtual }}</div>
                            @endif
                            <div style="display:flex;justify-content:space-between;padding:5px 16px;border-bottom:1px solid color-mix(in srgb,var(--text)3%,transparent);font-size:11px;">
                                <span style="font-weight:600;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px;">{{ $pr['nome'] }}</span>
                                <span style="white-space:nowrap;{{ $pr['status'] === 'quantidade_alterada' ? 'color:#3b82f6;' : ($pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'color:#22c55e;' : ($pr['qtd_separada'] > 0 ? 'color:#f59e0b;' : 'color:#ef4444;')) }};">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }}</span>
                            </div>
                        @endforeach
                    @endif

                    @if (count($this->cancelados) > 0)
                        <div style="padding:7px 16px;font-size:9px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid color-mix(in srgb,var(--text)4%,transparent);">
                            <span><i class="fas fa-ban" style="color:#ef4444;margin-right:4px;"></i> Cancelados</span>
                            <span style="background:color-mix(in srgb,#ef4444,10%,transparent);padding:1px 8px;border-radius:10px;color:#ef4444;">{{ count($this->cancelados) }}</span>
                        </div>
                        @foreach ($this->cancelados as $c)
                            <div style="display:flex;justify-content:space-between;padding:5px 16px;font-size:11px;color:color-mix(in srgb,var(--muted)40%,transparent);">
                                <span style="text-decoration:line-through;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px;">{{ $c['nome'] }}</span>
                                <span style="font-size:9px;font-weight:600;color:#6b7280;white-space:nowrap;">Cancelado</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif
        @endif

        {{-- SUBSTITUTO MODAL --}}
        <div x-show="$wire.showSubstituto" x-cloak
             style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:998;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:20px;">
            <div style="width:100%;max-width:400px;background:var(--surface);border-radius:16px;padding:20px;box-shadow:0 8px 40px rgba(0,0,0,0.15);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-weight:800;font-size:14px;color:var(--text);"><i class="fas fa-exchange-alt" style="color:#3b82f6;"></i> Substituir produto</span>
                    <button wire:click="fecharSubstituto" style="background:none;border:0;font-size:18px;color:var(--muted);cursor:pointer;">&times;</button>
                </div>
                <input wire:model.live="buscaSubstituto" wire:input="buscarSubstituto" placeholder="Buscar produto..." style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font-size:13px;outline:none;margin-bottom:12px;">
                @if (count($this->resultadosSubstituto) > 0)
                    <div style="max-height:300px;overflow-y:auto;">
                        @foreach ($this->resultadosSubstituto as $r)
                            <button wire:click="selecionarSubstituto({{ $r['id'] }})" style="display:flex;justify-content:space-between;align-items:center;width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);cursor:pointer;margin-bottom:4px;text-align:left;font-size:12px;">
                                <div style="flex:1;min-width:0;">
                                    <span style="font-weight:600;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $r['nome'] }}</span>
                                    <span style="font-size:10px;color:var(--muted);">SKU: {{ $r['sku'] ?: '—' }}</span>
                                </div>
                                @if (!empty($r['preco']))
                                    <span style="font-weight:700;color:var(--text);white-space:nowrap;margin-left:8px;">R$ {{ number_format($r['preco'], 2, ',', '.') }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @elseif (strlen(trim($this->buscaSubstituto)) >= 2)
                    <p style="text-align:center;font-size:12px;color:var(--muted);padding:20px 0;">Nenhum produto encontrado.</p>
                @endif
            </div>
        </div>

        {{-- SCANNER MODAL --}}
        <div x-show="showScanner" x-cloak
             style="position:fixed;top:0;left:0;right:0;bottom:0;z-index:999;background:rgba(0,0,0,0.85);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:20px;">
            <div style="width:100%;max-width:360px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="color:#fff;font-weight:700;font-size:14px;"><i class="fas fa-camera"></i> Escaneie o código</span>
                    <button @click="closeScanner()" style="background:none;border:0;color:#fff;font-size:20px;cursor:pointer;">&times;</button>
                </div>
                <div id="scanner-elem" style="width:100%;aspect-ratio:1;border-radius:12px;overflow:hidden;background:#000;"></div>
                <p x-show="scanError" x-text="scanError" style="color:#ef4444;font-size:12px;margin-top:8px;text-align:center;"></p>
                <p style="color:rgba(255,255,255,0.5);font-size:11px;text-align:center;margin-top:8px;">Aponte a câmera para o código de barras</p>
            </div>
        </div>
    </div>
</div>

@push('head')
<meta name="theme-color" content="#f59e0b">
<style>
/* ======================================
   TOAST
   ====================================== */
.toast-fixed{position:fixed;top:16px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 24px;border-radius:999px;font-size:14px;font-weight:700;z-index:999;display:flex;align-items:center;gap:10px;box-shadow:0 12px 48px rgba(0,0,0,0.12);max-width:90%}
.toast-icon{width:22px;height:22px;border-radius:50%;background:var(--success);display:flex;align-items:center;justify-content:center;font-size:11px}
.toast-enter{transition:all 0.3s ease-out}
.toast-leave{transition:all 0.2s ease-in}
.toast-enter-start{opacity:0;transform:translateX(-50%) translateY(-20px) scale(0.95)!important}
.toast-leave-end{opacity:0;transform:translateX(-50%) translateY(-20px)!important}
[x-cloak]{display:none!important}
.ring-2{border-color:#f59e0b!important;box-shadow:0 0 0 3px color-mix(in srgb,#f59e0b 30%,transparent)!important}

/* ======================================
   MODAL
   ====================================== */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:flex-end;justify-content:center;padding:16px;animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{transform:translateY(40px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-sheet{max-width:480px;width:100%;background:var(--surface);border-radius:16px 16px 0 0;padding:24px 20px 32px;max-height:80vh;overflow-y:auto;animation:slideUp .3s ease}
.modal-sheet h3{font-size:18px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.modal-sheet .field{margin-bottom:14px}
.modal-sheet .field label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin-bottom:4px}
.modal-sheet .field input,.modal-sheet .field textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:10px;font-size:14px;outline:none;font-family:inherit;background:var(--surface)}
.modal-sheet .field input:focus,.modal-sheet .field textarea:focus{border-color:#f59e0b;box-shadow:0 0 0 3px color-mix(in srgb,#f59e0b 15%,transparent)}
.modal-actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.modal-actions .btn{flex:1;min-width:80px;border:none;padding:11px 20px;border-radius:10px;font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:all .2s}
.modal-actions .btn:active{transform:scale(.96)}
.sub-card{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;transition:all .2s;cursor:pointer}
.sub-card:hover{border-color:#f59e0b;background:color-mix(in srgb,#f59e0b 6%,transparent)}
.sub-card .name{font-weight:700;font-size:13px}
.sub-card .price{color:var(--success);font-weight:700}
.sub-card .detail{font-size:12px;color:var(--muted)}
.summary-item{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)}
.summary-item:last-child{border-bottom:none}
.summary-item .item-name{font-weight:600;font-size:13px}
.summary-item .item-name small{display:block;font-weight:400;font-size:11px;color:var(--muted)}
.badge-tag{font-size:11px;font-weight:700;padding:3px 10px;border-radius:999px;white-space:nowrap}
.badge-tag.ok{background:color-mix(in srgb,#22c55e 12%,transparent);color:#22c55e}
.badge-tag.partial{background:color-mix(in srgb,#f59e0b 12%,transparent);color:#d97706}
.badge-tag.missing{background:color-mix(in srgb,#ef4444 12%,transparent);color:#ef4444}
.badge-tag.substituted{background:color-mix(in srgb,#3b82f6 12%,transparent);color:#2563eb}
.badge-tag.canceled{background:color-mix(in srgb,#6b7280 12%,transparent);color:#6b7280}
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
    modalHtml: '',
    showModal: false,

    openModal(html) { this.modalHtml = html; this.showModal = true; },
    closeModal() { this.showModal = false; this.modalHtml = ''; },

    abrirFalta(id) {
        this.openModal(`
            <h3 style='color:#ef4444;'><i class='fas fa-triangle-exclamation'></i> Registrar falta</h3>
            <p style='font-weight:600;margin-bottom:12px;font-size:14px;'>Produto sem estoque?</p>
            <div class='field'><label>Motivo</label><textarea id='motivoFalta' class='w-full'>Produto indispon\u00edvel no estoque.</textarea></div>
            <div class='modal-actions'>
                <button class='btn' style='background:#ef4444;color:#fff;' @click=\\\"closeModal();\\$wire.definirStatus('faltou');\\$wire.confirmarProximo();\\\"><i class='fas fa-check'></i> Confirmar falta</button>
                <button class='btn' style='background:var(--background);color:var(--text);border:1px solid var(--border);' @click='closeModal()'>Cancelar</button>
            </div>
        `);
    },
    abrirQtd(id) {
        this.openModal(`
            <h3><i class='fas fa-scale-balanced' style='color:#f59e0b;'></i> Alterar quantidade</h3>
            <div class='field'><label>Quantidade separada</label>
                <input id='qtdSep' type='number' step='0.1' value=\\\"${$wire.itens[$wire.itemAtual]?.qtd_pedido || 1}\\\" />
            </div>
            <div class='modal-actions'>
                <button class='btn' style='background:var(--sep-gradient);color:#fff;' @click='closeModal();\\$wire.confirmarProximo();'><i class='fas fa-check'></i> Confirmar</button>
                <button class='btn' style='background:var(--background);color:var(--text);border:1px solid var(--border);' @click='closeModal()'>Cancelar</button>
            </div>
        `);
    },
    abrirObs(id) {
        this.openModal(`
            <h3><i class='fas fa-note-sticky' style='color:var(--muted);'></i> Observa\u00e7\u00e3o</h3>
            <div class='field'><label>Observa\u00e7\u00e3o do separador</label>
                <textarea id='obsSep'>\u0024{ $wire.itens[$wire.itemAtual]?.observacao || '' }</textarea>
            </div>
            <div class='modal-actions'>
                <button class='btn' style='background:var(--warning);color:#fff;' @click='closeModal()'><i class='fas fa-check'></i> Salvar</button>
                <button class='btn' style='background:var(--background);color:var(--text);border:1px solid var(--border);' @click='closeModal()'>Cancelar</button>
            </div>
        `);
    },

    handleKey(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        if (e.key === 'Escape') { this.closeModal(); this.closeScanner(); return; }
        if (e.key === '1') { e.preventDefault(); $wire.definirStatus('ok'); }
        else if (e.key === '2') { e.preventDefault(); $wire.definirStatus('parcial'); }
        else if (e.key === '3') { e.preventDefault(); $wire.definirStatus('faltou'); }
        else if (e.key === '4') { e.preventDefault(); $wire.abrirSubstituto(); }
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
            this.qr.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width:250,height:150 } },
                (t) => { $wire.buscarPorCodigoBarras(t); this.closeScanner(); }
            ).catch(e => { this.scanError = e.message; });
        });
    },
    closeScanner() { if(this.qr){this.qr.stop().catch(()=>{});this.qr=null} this.showScanner=false; },
    confirmar() {
        this.confirmando = true; this.destacado = true;
        setTimeout(() => { this.destacado = false; }, 300);
        $wire.confirmarProximo().then(() => { this.confirmando = false; });
    }
}"
@keydown.window="handleKey"
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 3500) })">

    {{-- TOAST --}}
    <div x-show="show" x-cloak x-transition:enter="toast-enter" x-transition:leave="toast-leave"
         class="toast-fixed" style="z-index:999;">
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    @php $p = $this->pedido(); $item = $this->item; $prog = $this->progresso(); @endphp

    {{-- BLOQUEIO --}}
    @if ($this->bloqueioErro)
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:40px 20px;text-align:center;">
            <div style="width:72px;height:72px;border-radius:50%;background:color-mix(in srgb,#ef4444 12%,transparent);display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                <i class="fas fa-lock" style="font-size:28px;color:#ef4444;"></i>
            </div>
            <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0 0 6px;">Pedido em uso</h2>
            <p style="font-size:14px;color:var(--muted);margin:0 0 20px;">{{ $this->bloqueioErro }}</p>
            <a href="/separacao" wire:navigate style="padding:12px 28px;border:0;border-radius:10px;background:#f59e0b;color:#fff;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;">
                <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Voltar
            </a>
        </div>
    @else

    <div style="max-width:480px;margin:0 auto;background:var(--background);min-height:100vh;display:flex;flex-direction:column;">

        {{-- ===== HEADER ===== --}}
        <div style="background:var(--sep-gradient);color:#fff;padding:14px 20px 18px;flex-shrink:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <button wire:navigate href="/separacao" style="background:rgba(255,255,255,0.13);border:none;color:#fff;padding:9px 13px;border-radius:10px;cursor:pointer;font-size:18px;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div style="text-align:center;flex:1;">
                    <h2 style="font-size:17px;font-weight:800;margin:0;">Pedido #{{ $this->pedidoId }}</h2>
                    <small style="opacity:0.8;font-size:13px;">{{ $p?->cliente?->nome ?? '' }} · {{ $p?->tipo_entrega === 'retirada' ? 'Retirada' : 'Entrega' }}</small>
                </div>
                @if ($p && $p->cliente && $p->cliente->whatsapp)
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $p->cliente->whatsapp) }}" target="_blank" style="background:#25d366;border:none;color:#fff;padding:9px 13px;border-radius:10px;text-decoration:none;font-size:20px;">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                @else
                    <span style="width:42px;"></span>
                @endif
            </div>

            {{-- Stats + Progress --}}
            @if ($item || count($this->processados) > 0 || count($this->cancelados) > 0)
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-top:14px;">
                <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:8px;text-align:center;border:1px solid rgba(255,255,255,0.05);">
                    <b style="display:block;font-size:18px;font-weight:800;">{{ $prog['feitos'] }}/{{ $prog['total'] }}</b>
                    <small style="font-size:10px;opacity:0.7;text-transform:uppercase;letter-spacing:0.04em;">Itens</small>
                </div>
                <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:8px;text-align:center;border:1px solid rgba(255,255,255,0.05);">
                    <b style="display:block;font-size:18px;font-weight:800;">{{ $prog['pct'] }}%</b>
                    <small style="font-size:10px;opacity:0.7;text-transform:uppercase;letter-spacing:0.04em;">Progresso</small>
                </div>
                <div style="background:rgba(255,255,255,0.1);border-radius:10px;padding:8px;text-align:center;border:1px solid rgba(255,255,255,0.05);">
                    <b style="display:block;font-size:18px;font-weight:800;">{{ $p?->total ? number_format((float)$p->total, 2, ',', '.') : '—' }}</b>
                    <small style="font-size:10px;opacity:0.7;text-transform:uppercase;letter-spacing:0.04em;">Total</small>
                </div>
            </div>
            <div style="height:5px;background:rgba(255,255,255,0.15);border-radius:999px;overflow:hidden;margin-top:10px;">
                <div style="height:100%;border-radius:999px;width:{{ $prog['pct'] }}%;background:#fff;transition:width 0.6s ease;"></div>
            </div>
            @endif
        </div>

        {{-- ===== RESUME / FINAL ===== --}}
        @if ($item || count($this->itens) > 0)
            {{-- ITEM ATUAL --}}
            <div style="flex:1;padding:16px 16px 90px;overflow-y:auto;">

                {{-- Link pedido --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <span style="font-size:12px;color:var(--muted);font-weight:600;">
                        Item <strong style="color:#f59e0b;">{{ $prog['feitos'] + 1 }}</strong> de {{ $prog['total'] }}
                    </span>
                    <a href="/pedidos-online/{{ $this->pedidoId }}" wire:navigate style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:600;">
                        <i class="fas fa-external-link-alt"></i> Ver pedido
                    </a>
                </div>

                {{-- Step dots --}}
                @if (count($this->itens) > 1)
                <div style="display:flex;gap:3px;justify-content:center;margin-bottom:12px;">
                    @foreach ($this->itens as $idx => $i)
                        <span style="width:6px;height:6px;border-radius:50%;
                            {{ $idx < $this->itemAtual ? 'background:#22c55e;' : ($idx == $this->itemAtual ? 'background:#f59e0b;width:10px;height:10px;border:2px solid color-mix(in srgb,#f59e0b 40%,transparent);' : 'background:color-mix(in srgb,var(--text) 12%,transparent);') }}">
                        </span>
                    @endforeach
                </div>
                @endif

                {{-- ITEM CARD --}}
                <div :class="{ 'ring-2': destacado }"
                     style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:14px;box-shadow:0 4px 20px rgba(0,0,0,0.04);transition:box-shadow .2s,border-color .2s;margin-bottom:10px;">

                    {{-- Location --}}
                    <div style="display:flex;align-items:center;gap:6px;{{ !empty($item['localizacao']) ? 'background:color-mix(in srgb,#f59e0b 8%,transparent);color:#f59e0b;' : 'background:color-mix(in srgb,var(--text) 4%,transparent);color:var(--muted);' }}padding:6px 10px;border-radius:8px;font-size:12px;font-weight:700;margin-bottom:12px;">
                        <i class="fas fa-location-dot"></i>
                        <span>{{ $item['localizacao'] ?: 'Sem localização' }}</span>
                    </div>

                    {{-- Nome + Foto --}}
                    <div style="display:flex;gap:10px;margin-bottom:12px;">
                        @if (!empty($item['foto']))
                            <img src="{{ $item['foto'] }}" alt="" style="width:52px;height:52px;border-radius:10px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;" onerror="this.style.display='none'">
                        @else
                            <div style="width:52px;height:52px;border-radius:10px;background:var(--background);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0;">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:15px;color:var(--text);text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $item['nome'] }}</div>
                            <div style="font-size:12px;color:var(--muted);margin-top:2px;display:flex;flex-wrap:wrap;gap:4px 12px;">
                                <span><i class="fas fa-tag" style="font-size:11px;"></i> {{ $item['sku'] ?: '—' }}</span>
                                @if (!empty($item['categoria']) && $item['categoria'] !== 'Geral')
                                    <span><i class="fas fa-layer-group" style="font-size:11px;"></i> {{ $item['categoria'] }}</span>
                                @endif
                            </div>
                        </div>
                        <span style="background:color-mix(in srgb,#f59e0b 12%,transparent);color:#f59e0b;font-size:12px;font-weight:800;padding:4px 10px;border-radius:999px;white-space:nowrap;height:fit-content;">
                            {{ number_format($item['qtd_pedido'], 0, ',', '.') }} un
                        </span>
                    </div>

                    {{-- QTD Selector + Scanner --}}
                    <div style="display:flex;align-items:center;gap:6px;background:color-mix(in srgb,var(--text) 3%,transparent);border:1px solid var(--border);border-radius:12px;padding:8px 12px;margin-bottom:12px;">
                        <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-right:auto;">Separando</span>
                        <div style="display:flex;align-items:center;gap:4px;">
                            <button wire:click="decrementar" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;">−</button>
                            <input type="number" wire:model.blur="itens.{{ $this->itemAtual }}.qtd_separada" style="width:48px;height:30px;text-align:center;border:2px solid #f59e0b;border-radius:8px;font-size:15px;font-weight:800;outline:none;background:var(--surface);">
                            <button wire:click="incrementar" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;">+</button>
                        </div>
                        <button @click="openScanner()" style="width:30px;height:30px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;color:var(--muted);" title="Código de barras">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>

                    {{-- Ações rápidas --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:4px;margin-bottom:10px;">
                        <button wire:click="definirStatus('ok')" style="padding:8px 4px;border:2px solid #22c55e;border-radius:10px;background:color-mix(in srgb,#22c55e 6%,transparent);color:#22c55e;font-weight:700;font-size:10px;cursor:pointer;text-align:center;">
                            <i class="fas fa-check-circle" style="display:block;font-size:15px;margin-bottom:1px;"></i> OK <span style="font-size:8px;opacity:0.6;">[1]</span>
                        </button>
                        <button wire:click="definirStatus('parcial')" style="padding:8px 4px;border:2px solid #f59e0b;border-radius:10px;background:color-mix(in srgb,#f59e0b 6%,transparent);color:#f59e0b;font-weight:700;font-size:10px;cursor:pointer;text-align:center;">
                            <i class="fas fa-exclamation-triangle" style="display:block;font-size:15px;margin-bottom:1px;"></i> Parcial <span style="font-size:8px;opacity:0.6;">[2]</span>
                        </button>
                        <button wire:click="definirStatus('faltou')" style="padding:8px 4px;border:2px solid #ef4444;border-radius:10px;background:color-mix(in srgb,#ef4444 6%,transparent);color:#ef4444;font-weight:700;font-size:10px;cursor:pointer;text-align:center;">
                            <i class="fas fa-times-circle" style="display:block;font-size:15px;margin-bottom:1px;"></i> Faltou <span style="font-size:8px;opacity:0.6;">[3]</span>
                        </button>
                        <button wire:click="abrirSubstituto" style="padding:8px 4px;border:2px solid #3b82f6;border-radius:10px;background:color-mix(in srgb,#3b82f6 6%,transparent);color:#3b82f6;font-weight:700;font-size:10px;cursor:pointer;text-align:center;">
                            <i class="fas fa-exchange-alt" style="display:block;font-size:15px;margin-bottom:1px;"></i> Subst. <span style="font-size:8px;opacity:0.6;">[4]</span>
                        </button>
                    </div>

                    {{-- Observação --}}
                    <div>
                        <label style="font-size:11px;font-weight:700;color:var(--muted);display:block;margin-bottom:4px;">
                            <i class="fas fa-note-sticky" style="font-size:10px;"></i> Observação
                        </label>
                        <input wire:model="itens.{{ $this->itemAtual }}.observacao" type="text" placeholder="Motivo (opcional)..." style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:13px;outline:none;background:var(--surface);">
                    </div>
                </div>

                {{-- Atalhos hint --}}
                <div style="text-align:center;font-size:10px;color:color-mix(in srgb,var(--muted) 50%,transparent);">
                    <i class="fas fa-keyboard"></i> 1=OK 2=Parcial 3=Faltou 4=Subst. Espaço=Confirmar P=Pular
                </div>
            </div>

            {{-- ACTIONS FIXAS --}}
            <div style="position:sticky;bottom:0;z-index:20;padding:12px 16px;background:var(--surface);border-top:1px solid var(--border);display:flex;gap:8px;box-shadow:0 -4px 20px rgba(0,0,0,0.04);flex-shrink:0;">
                <button wire:click="pularItem" style="flex:1;padding:13px 12px;border:1px solid var(--border);border-radius:10px;background:var(--background);cursor:pointer;font-weight:700;font-size:14px;color:var(--text);">
                    <i class="fas fa-forward" style="margin-right:6px;"></i> Pular <span style="font-size:10px;opacity:0.5;">[P]</span>
                </button>
                <button @click="confirmar()"
                        x-bind:disabled="confirmando"
                        style="flex:2;padding:13px 12px;border:0;border-radius:10px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;cursor:pointer;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 14px color-mix(in srgb,#22c55e 30%,transparent);">
                    <span x-show="!confirmando">Confirmar <i class="fas fa-arrow-right"></i> <span style="font-size:10px;opacity:0.6;">[Espaço]</span></span>
                    <span x-show="confirmando" x-cloak><i class="fas fa-spinner fa-pulse"></i> Salvando...</span>
                </button>
            </div>

        @else
            {{-- CONFERÊNCIA / FINAL --}}
            <div style="flex:1;padding:16px;overflow-y:auto;">
                <div style="text-align:center;margin-bottom:16px;">
                    <div style="width:56px;height:56px;border-radius:50%;background:color-mix(in srgb,#22c55e 12%,transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                        <i class="fas fa-check-circle" style="font-size:26px;color:#22c55e;"></i>
                    </div>
                    <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0;">Lista completa!</h2>
                    <p style="font-size:13px;color:var(--muted);margin:4px 0 0;">Pedido #{{ $this->pedidoId }} — {{ $p?->cliente?->nome ?? '' }}</p>
                    <a href="/pedidos-online/{{ $this->pedidoId }}" wire:navigate style="display:inline-block;margin-top:4px;font-size:12px;color:#2563eb;text-decoration:none;font-weight:600;">
                        <i class="fas fa-external-link-alt"></i> Ver pedido completo
                    </a>
                </div>

                {{-- Itens processados --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:4px 14px;margin-bottom:12px;box-shadow:0 1px 6px rgba(0,0,0,0.03);">
                    @forelse ($this->processados as $pr)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid color-mix(in srgb,var(--text) 6%,transparent);font-size:12px;">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:12px;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $pr['nome'] }}</div>
                                <span style="font-size:11px;color:var(--muted);">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }} un</span>
                                @if (!empty($pr['substituto_nome']))
                                    <span style="font-size:10px;color:#2563eb;">→ {{ $pr['substituto_nome'] }}</span>
                                @endif
                            </div>
                            <span class="badge-tag {{ $pr['status'] === 'separado' ? 'ok' : ($pr['status'] === 'substituido' ? 'substituted' : ($pr['status'] === 'quantidade_alterada' ? 'partial' : 'missing')) }}">
                                {{ $pr['status'] === 'separado' ? 'OK' : ($pr['status'] === 'substituido' ? 'Subst.' : ($pr['status'] === 'quantidade_alterada' ? 'Qtd Alt' : 'Faltou')) }}
                            </span>
                        </div>
                    @empty
                        <div style="text-align:center;padding:16px;color:var(--muted);font-size:13px;">Nenhum item processado.</div>
                    @endforelse

                    {{-- Cancelados --}}
                    @if (count($this->cancelados) > 0)
                        <div style="border-top:1px solid color-mix(in srgb,var(--text) 8%,transparent);padding:8px 0;">
                            <div style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:4px;">
                                <i class="fas fa-ban" style="color:#ef4444;"></i> Cancelados ({{ count($this->cancelados) }})
                            </div>
                            @foreach ($this->cancelados as $c)
                                <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;color:color-mix(in srgb,var(--muted) 50%,transparent);">
                                    <span style="text-decoration:line-through;">{{ $c['nome'] }}</span>
                                    <span class="badge-tag canceled">Cancelado</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;padding:8px 0;border-top:1px solid color-mix(in srgb,var(--text) 6%,transparent);">
                        <div style="text-align:center;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">OK</span>
                            <span style="font-size:18px;font-weight:900;color:#22c55e;">{{ $prog['separados'] }}</span>
                        </div>
                        <div style="text-align:center;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Faltou</span>
                            <span style="font-size:18px;font-weight:900;color:#ef4444;">{{ $prog['faltou'] }}</span>
                        </div>
                        <div style="text-align:center;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Qtd Alt</span>
                            <span style="font-size:18px;font-weight:900;color:#3b82f6;">{{ $prog['altQtd'] }}</span>
                        </div>
                        <div style="text-align:center;">
                            <span style="font-size:10px;font-weight:700;color:var(--muted);display:block;">Subst.</span>
                            <span style="font-size:18px;font-weight:900;color:#f59e0b;">{{ $prog['substituidos'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Volumes --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:12px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                        <i class="fas fa-boxes" style="color:var(--muted);"></i>
                        <span style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;">Volumes / Sacolas</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <button wire:click="$set('volumes', Math.max(1, parseInt(volumes || 1) - 1))" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;">−</button>
                        <input type="number" wire:model="volumes" style="width:56px;height:32px;text-align:center;border:2px solid #f59e0b;border-radius:8px;font-size:16px;font-weight:800;outline:none;background:var(--surface);">
                        <button wire:click="$set('volumes', parseInt(volumes || 1) + 1)" style="width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:16px;">+</button>
                        <span style="font-size:12px;color:var(--muted);">saco(s)</span>
                    </div>
                </div>

                {{-- Finalizar --}}
                @if (!$this->conferenciaAprovada)
                    <button wire:click="abrirConferencia" style="width:100%;padding:14px;border:0;border-radius:12px;background:var(--sep-gradient);color:#fff;font-weight:800;font-size:15px;cursor:pointer;box-shadow:0 4px 14px color-mix(in srgb,#f59e0b 25%,transparent);">
                        <i class="fas fa-clipboard-check" style="margin-right:6px;"></i> Iniciar Conferência
                    </button>
                @else
                    <div style="background:color-mix(in srgb,#22c55e 6%,transparent);border:1px solid #22c55e;border-radius:12px;padding:12px;margin-bottom:12px;text-align:center;">
                        <i class="fas fa-check-circle" style="color:#22c55e;font-size:22px;display:block;margin-bottom:4px;"></i>
                        <span style="font-size:14px;font-weight:700;color:#22c55e;">Conferência aprovada</span>
                        <p style="font-size:12px;color:var(--muted);margin:2px 0 0;">
                            {{ $prog['total'] }} itens · {{ $prog['separados'] }} OK · {{ $prog['faltou'] }} faltou · {{ $prog['altQtd'] }} qtd alt. · {{ $prog['substituidos'] }} subst.
                        </p>
                    </div>
                    <button wire:click="finalizarSeparacao" style="width:100%;padding:14px;border:0;border-radius:12px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-weight:800;font-size:15px;cursor:pointer;box-shadow:0 4px 14px color-mix(in srgb,#22c55e 25%,transparent);">
                        <i class="fas fa-check-double" style="margin-right:6px;"></i> Confirmar e Finalizar ({{ $this->volumes }} {{ $this->volumes > 1 ? 'volumes' : 'volume' }})
                    </button>
                @endif

                <a href="/separacao" wire:navigate style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--muted);text-decoration:none;padding:8px;">Voltar para lista</a>
            </div>
        @endif

        {{-- ===== PROCESSADOS + CANCELADOS (durante a separacao) ===== --}}
        @if ($item && (count($this->processados) > 0 || count($this->cancelados) > 0))
            <div style="border-top:1px solid var(--border);background:color-mix(in srgb,var(--text) 2%,transparent);flex-shrink:0;">
                @if (count($this->processados) > 0)
                    <div style="padding:6px 16px;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;border-bottom:1px solid color-mix(in srgb,var(--text) 4%,transparent);">
                        <span><i class="fas fa-check" style="color:#22c55e;"></i> Processados</span>
                        <span>{{ count($this->processados) }}</span>
                    </div>
                    @php $catAtual = null; @endphp
                    @foreach ($this->processados as $pr)
                        @if (($pr['categoria'] ?? 'Geral') !== $catAtual) @php $catAtual = $pr['categoria'] ?? 'Geral'; @endphp
                            <div style="padding:2px 16px;font-size:9px;font-weight:700;color:color-mix(in srgb,var(--muted) 50%,transparent);text-transform:uppercase;background:color-mix(in srgb,var(--text) 2%,transparent);border-bottom:1px solid color-mix(in srgb,var(--text) 3%,transparent);">{{ $catAtual }}</div>
                        @endif
                        <div style="display:flex;justify-content:space-between;padding:4px 16px;border-bottom:1px solid color-mix(in srgb,var(--text) 3%,transparent);font-size:12px;">
                            <span style="font-weight:600;text-transform:uppercase;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px;">{{ $pr['nome'] }}</span>
                            <span style="white-space:nowrap;font-weight:600;{{ $pr['status'] === 'quantidade_alterada' ? 'color:#3b82f6;' : ($pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'color:#22c55e;' : ($pr['qtd_separada'] > 0 ? 'color:#f59e0b;' : 'color:#ef4444;')) }}">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }}</span>
                        </div>
                    @endforeach
                @endif
                @if (count($this->cancelados) > 0)
                    <div style="padding:6px 16px;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;border-bottom:1px solid color-mix(in srgb,var(--text) 4%,transparent);">
                        <span><i class="fas fa-ban" style="color:#ef4444;"></i> Cancelados</span>
                        <span style="color:#ef4444;">{{ count($this->cancelados) }}</span>
                    </div>
                    @foreach ($this->cancelados as $c)
                        <div style="display:flex;justify-content:space-between;padding:4px 16px;font-size:12px;color:color-mix(in srgb,var(--muted) 40%,transparent);">
                            <span style="text-decoration:line-through;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;margin-right:8px;">{{ $c['nome'] }}</span>
                            <span class="badge-tag canceled" style="font-size:10px;">Cancelado</span>
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
    </div>

    {{-- ===== SUBSTITUTO MODAL ===== --}}
    <div x-show="$wire.showSubstituto" x-cloak class="modal-overlay" @click.self="$wire.fecharSubstituto()">
        <div class="modal-sheet">
            <h3><i class="fas fa-exchange-alt" style="color:#3b82f6;"></i> Substituir produto</h3>
            <div class="field"><label>Buscar produto</label>
                <input wire:model.live="buscaSubstituto" wire:input="buscarSubstituto" placeholder="Digite o nome do produto...">
            </div>
            @if (count($this->resultadosSubstituto) > 0)
                <div style="max-height:280px;overflow-y:auto;">
                    @foreach ($this->resultadosSubstituto as $r)
                        <button wire:click="selecionarSubstituto({{ $r['id'] }})" class="sub-card" style="width:100%;border:1px solid var(--border);border-radius:10px;margin-bottom:6px;padding:10px 14px;display:flex;justify-content:space-between;background:var(--surface);text-align:left;cursor:pointer;">
                            <div>
                                <div class="name">{{ $r['nome'] }}</div>
                                <div class="detail">SKU: {{ $r['sku'] ?: '—' }}</div>
                            </div>
                            @if (!empty($r['preco']))
                                <span class="price" style="white-space:nowrap;">R$ {{ number_format($r['preco'], 2, ',', '.') }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif (strlen(trim($this->buscaSubstituto)) >= 2)
                <p style="text-align:center;font-size:13px;color:var(--muted);padding:16px 0;">Nenhum produto encontrado.</p>
            @endif
            <div class="modal-actions">
                <button class="btn" style="background:var(--background);color:var(--text);border:1px solid var(--border);" wire:click="fecharSubstituto">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- ===== SCANNER MODAL ===== --}}
    <div x-show="showScanner" x-cloak class="modal-overlay" style="background:rgba(0,0,0,0.85);align-items:center;" @click.self="closeScanner()">
        <div style="width:100%;max-width:360px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="color:#fff;font-weight:700;font-size:15px;"><i class="fas fa-camera"></i> Escaneie o código</span>
                <button @click="closeScanner()" style="background:rgba(255,255,255,0.15);border:0;color:#fff;font-size:16px;border-radius:50%;width:32px;height:32px;cursor:pointer;">&times;</button>
            </div>
            <div id="scanner-elem" style="width:100%;aspect-ratio:1;border-radius:12px;overflow:hidden;background:#000;"></div>
            <p x-show="scanError" x-text="scanError" style="color:#ef4444;font-size:12px;margin-top:8px;text-align:center;"></p>
            <p style="color:rgba(255,255,255,0.5);font-size:12px;text-align:center;margin-top:8px;">Aponte a câmera para o código de barras</p>
        </div>
    </div>

    {{-- ===== MODAL GENERICO (Alpine) ===== --}}
    <div x-show="showModal" x-cloak class="modal-overlay" @click.self="closeModal()">
        <div class="modal-sheet" x-html="modalHtml"></div>
    </div>

    @endif {{-- end bloqueio --}}
</div>

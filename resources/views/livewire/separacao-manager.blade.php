<div>
<script src="/js/html5-qrcode.min.js"></script>
<meta name="theme-color" content="#ffffff">
<style>
/* ======================================
   RESET SPACING
   ====================================== */
@media (max-width: 768px) {
    .main-content {
        padding-top: 0 !important;
    }
}
.content-body {
    padding: 0 !important;
}

/* ======================================
   VARIABLES
   ====================================== */
:root {
    --brand-500: #10b981;
    --brand-600: #059669;
    --bg-gray-50: #f9fafb;
    --bg-gray-100: #f3f4f6;
    --text-gray-800: #1f2937;
    --border-gray-100: #f3f4f6;
    --radius-xl: 12px;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* TOAST */
.toast-fixed {
    position:fixed;top:16px;left:50%;transform:translateX(-50%);
    background:#1f2937;color:#fff;padding:12px 24px;border-radius:9999px;
    font-size:14px;font-weight:700;z-index:999;
    display:flex;align-items:center;gap:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);max-width:90%;
}
.toast-icon { width:22px;height:22px;border-radius:50%;background:var(--brand-500);
    display:flex;align-items:center;justify-content:center;font-size:11px; }

/* BOTTOM SHEET DRAWER MODAL */
.drawer-overlay {
    position:fixed;inset:0;background:rgba(31, 41, 55, 0.6);backdrop-filter:blur(6px);
    z-index:100;display:flex;align-items:flex-end;justify-content:center;
    transition: opacity 0.3s ease;
}
.drawer-sheet {
    max-width:480px;width:100%;background:#fff;
    border-radius:24px 24px 0 0;
    padding:24px 20px 32px;max-height:85vh;overflow-y:auto;
    box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
    transition: transform 0.3s ease-out;
}

/* SUB CARDS & BADGES */
.sub-card {
    display:flex;justify-content:space-between;align-items:center;
    padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;
    margin-bottom:8px;transition:all 0.2s;cursor:pointer;background:#fff;
}
.sub-card:hover { border-color:var(--brand-500);background:#f0fdf4; }
.sub-card .name { font-weight:700;font-size:13px; }
.sub-card .price { color:var(--brand-600);font-weight:700; }
.sub-card .detail { font-size:12px;color:#9ca3af; }

.badge-tag { font-size:11px;font-weight:700;padding:3px 10px;border-radius:9999px;white-space:nowrap; }
.badge-tag.ok { background:#dcfce7;color:#15803d; }
.badge-tag.partial { background:#fef3c7;color:#d97706; }
.badge-tag.missing { background:#fee2e2;color:#b91c1c; }
.badge-tag.substituted { background:#dbeafe;color:#2563eb; }
.badge-tag.canceled { background:#e5e7eb;color:#6b7280; }
</style>

<div x-data="{
    show: @entangle('toastShow'),
    msg: @entangle('toastMsg'),
    confirmando: false,
    destacado: false,
    showScanner: false,
    scanError: null,
    qr: null,
    showDrawer: false,

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
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 3500) })">

    {{-- TOAST --}}
    <div x-show="show" x-cloak class="toast-fixed" style="z-index:999;">
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    @php $p = $this->pedido(); $item = $this->item; $prog = $this->progresso(); @endphp

    {{-- BLOQUEIO --}}
    @if ($this->bloqueioErro)
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;padding:40px 20px;text-align:center;background:#f9fafb;">
            <div style="width:72px;height:72px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                <i class="fas fa-lock" style="font-size:28px;color:#ef4444;"></i>
            </div>
            <h2 style="font-size:18px;font-weight:800;color:#1f2937;margin:0 0 6px;">Pedido em uso</h2>
            <p style="font-size:14px;color:#6b7280;margin:0 0 20px;">{{ $this->bloqueioErro }}</p>
            <a href="/separacao" wire:navigate style="padding:12px 28px;border:0;border-radius:10px;background:#1f2937;color:#fff;font-weight:700;font-size:14px;cursor:pointer;text-decoration:none;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Voltar
            </a>
            <button wire:click="forcarLiberacao" style="margin-top:10px;padding:12px 28px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;color:#4b5563;font-weight:700;font-size:14px;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <i class="fas fa-unlock" style="margin-right:6px;"></i> Tomar Posse (forçar liberação)
            </button>
        </div>
    @else
        <div style="max-width:800px;margin:0 auto;background:#f9fafb;min-height:100vh;display:flex;flex-direction:column;position:relative;">

        {{-- ===== HEADER ===== --}}
        @if ($item)
        <header style="background:#fff;padding:16px 20px 12px;border-bottom:1px solid #f3f4f6;flex-shrink:0;box-shadow:0 1px 2px rgba(0,0,0,0.03);position:sticky;top:0;z-index:50;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:8px;">
                <button wire:navigate href="/separacao" style="background:none;border:none;color:#9ca3af;font-size:18px;cursor:pointer;outline:none;padding:4px;">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <span style="font-weight:800;color:#1f2937;font-size:15px;">Pedido #{{ $this->pedidoId }}</span>
                
                {{-- WhatsApp Link --}}
                @if ($p && $p->cliente && $p->cliente->whatsapp)
                    <a href="https://wa.me/55{{ preg_replace('/\D/', '', $p->cliente->whatsapp) }}" target="_blank" style="background:#25d366;border:none;color:#fff;width:30px;height:30px;border-radius:50%;text-decoration:none;font-size:15px;display:flex;align-items:center;justify-content:center;">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                @else
                    <div style="width:30px;"></div>
                @endif
            </div>

            {{-- Progresso --}}
            <div>
                <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:4px;">
                    <span>Progresso</span>
                    <span>{{ $prog['feitos'] }}/{{ $prog['total'] }}</span>
                </div>
                <div style="width:100%;background:#e5e7eb;border-radius:9999px;height:6px;overflow:hidden;">
                    <div style="background:var(--brand-500);height:100%;border-radius:9999px;width:{{ $prog['pct'] }}%;transition:width 0.3s ease;"></div>
                </div>
            </div>
            
            {{-- Localização --}}
            <div style="background:#eff6ff;color:#1e40af;font-size:12px;font-weight:800;padding:8px;border-radius:8px;margin-top:10px;display:flex;justify-content:center;align-items:center;gap:6px;border:1px solid #dbeafe;">
                <i class="fas fa-map-location-dot"></i>
                <span>{{ $item['localizacao'] ?: 'Sem corredor cadastrado' }}</span>
            </div>
        </header>

        {{-- ===== CONTEUDO FOCADO ===== --}}
        <main style="flex:1;padding:24px 20px;display:flex;flex-direction:column;align-items:center;justify-content:center;" class="no-scrollbar">
            
            {{-- Imagem do produto --}}
            <div style="width:192px;height:192px;background:#fff;border-radius:16px;padding:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);border:1px solid #f3f4f6;margin-bottom:24px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                @if (!empty($item['foto']))
                    <img src="{{ $item['foto'] }}" alt="Produto" style="max-height:100%;max-width:100%;object-fit:contain;" onerror="this.style.display='none'">
                @else
                    <i class="fas fa-box" style="font-size:64px;color:#d1d5db;"></i>
                @endif
            </div>

            {{-- Textos do Produto --}}
            <div style="text-align:center;margin-bottom:32px;width:100%;">
                <h2 style="font-size:22px;font-weight:900;color:#1f2937;line-height:1.2;margin-bottom:8px;text-transform:uppercase;word-wrap:break-word;padding:0 10px;">
                    {{ $item['nome'] }}
                </h2>
                <p style="color:#9ca3af;font-size:15px;margin-bottom:16px;font-weight:600;">
                    SKU: {{ $item['sku'] ?: '—' }} · {{ $item['categoria'] }}
                </p>

                @if (!empty($item['observacao']))
                    <div style="background:#fffbeb;border:1px solid #fde68a;color:#b45309;font-size:13px;padding:8px 12px;border-radius:8px;margin-bottom:16px;display:inline-block;max-width:90%;text-align:left;">
                        <b><i class="far fa-note-sticky"></i> Obs. cliente:</b> {{ $item['observacao'] }}
                    </div>
                @endif

                @if (!empty($item['substituto_nome']))
                    <div style="color:var(--brand-600);font-size:13px;font-weight:700;margin-bottom:16px;">
                        <i class="fa-solid fa-repeat"></i> Substituído por: {{ $item['substituto_nome'] }}
                    </div>
                @endif

                {{-- Qtd a pegar --}}
                <div style="display:inline-flex;align-items:center;gap:10px;background:#1f2937;color:#fff;font-size:18px;font-weight:800;padding:8px 20px;border-radius:12px;box-shadow:0 4px 6px rgba(0,0,0,0.08);">
                    <span>Pegar:</span>
                    <div style="display:flex;align-items:center;gap:4px;">
                        <button wire:click="decrementar" style="width:28px;height:28px;border-radius:6px;border:none;background:rgba(255,255,255,0.15);color:#fff;cursor:pointer;font-size:16px;font-weight:bold;display:flex;align-items:center;justify-content:center;">−</button>
                        <input type="number" wire:model.blur="itens.{{ $this->itemAtual }}.qtd_separada" style="width:65px;height:30px;text-align:center;border:none;background:rgba(255,255,255,0.1);color:#10b981;border-radius:6px;font-size:20px;font-weight:900;outline:none;-moz-appearance:textfield;" onfocus="this.select()">
                        <button wire:click="incrementar" style="width:28px;height:28px;border-radius:6px;border:none;background:rgba(255,255,255,0.15);color:#fff;cursor:pointer;font-size:16px;font-weight:bold;display:flex;align-items:center;justify-content:center;">+</button>
                    </div>
                    <span style="font-size:15px;opacity:0.8;">/ {{ $item['qtd_pedido'] }} un</span>
                </div>
            </div>

            {{-- Ações no Rodapé da Coleta --}}
            <div style="width:100%;margin-top:auto;display:flex;flex-direction:column;gap:10px;">
                <button @click="confirmar()" class="btn"
                        style="width:100%;background:var(--brand-500);color:#fff;padding:16px;border-radius:12px;font-weight:800;font-size:16px;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(16, 185, 129, 0.2);display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fa-solid fa-barcode"></i> Bipar e Confirmar
                </button>
                
                <div style="display:grid;grid-template-columns:{{ $item['qtd_pedido'] > 1 ? '1fr 1fr' : '1fr' }};gap:8px;">
                    @if ($item['qtd_pedido'] > 1)
                        <button wire:click="definirStatus('parcial')"
                                style="background:#fff;color:#d97706;border:2px solid #fef3c7;padding:12px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;text-align:center;">
                            <i class="fas fa-pen mr-1"></i> Parcial
                        </button>
                    @endif
                    <button @click="showDrawer = true"
                            style="background:#fff;color:#ef4444;border:2px solid #fee2e2;padding:12px;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;text-align:center;width:100%;">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Reportar Falta
                    </button>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;padding:0 6px;">
                    <button wire:click="pularItem" style="background:none;border:none;color:#9ca3af;font-size:13px;font-weight:700;cursor:pointer;">
                        Pular Item <i class="fas fa-forward ml-1"></i>
                    </button>
                    <span style="font-size:10px;color:#9ca3af;font-weight:600;">Use Atalhos do Teclado: 1=OK · 3=Falta · P=Pular</span>
                </div>
            </div>
        </main>

        {{-- ===== BOTTOM DRAWER DE TRATAMENTO DE FALTA ===== --}}
        <div x-show="showDrawer" x-cloak class="drawer-overlay" @click.self="showDrawer = false">
            <div class="drawer-sheet" x-show="showDrawer" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                    <h3 style="font-size:18px;font-weight:800;color:#1f2937;margin:0;">Tratar Falta</h3>
                    <button @click="showDrawer = false" style="background:none;border:none;color:#9ca3af;font-size:22px;cursor:pointer;"><i class="fas fa-times"></i></button>
                </div>
                
                <p style="color:#4b5563;font-size:14px;margin-bottom:16px;">Como você deseja tratar a falta deste item?</p>
                <div style="font-weight:700;color:#1f2937;margin-bottom:16px;text-transform:uppercase;font-size:13px;">{{ $item['nome'] }}</div>
                
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button @click="showDrawer = false; $wire.abrirSubstituto()" class="sub-card" style="width:100%;display:flex;align-items:center;justify-content:space-between;text-align:left;border:1px solid #e5e7eb;border-radius:12px;padding:14px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:16px;"><i class="fa-solid fa-repeat"></i></div>
                            <div>
                                <span style="display:block;font-weight:800;color:#1f2937;font-size:14px;">Sugerir Substituto</span>
                                <span style="display:block;font-size:11px;color:#9ca3af;">Buscar outro produto no estoque</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#d1d5db;"></i>
                    </button>

                    <button @click="showDrawer = false; $wire.definirStatus('faltou')" style="width:100%;display:flex;align-items:center;justify-content:space-between;text-align:left;border:1px solid #fee2e2;background:#fef2f2;border-radius:12px;padding:14px;cursor:pointer;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#fee2e2;color:#ef4444;display:flex;align-items:center;justify-content:center;font-size:16px;"><i class="fa-solid fa-ban"></i></div>
                            <div>
                                <span style="display:block;font-weight:800;color:#b91c1c;font-size:14px;">Falta Definitiva</span>
                                <span style="display:block;font-size:11px;color:#f87171;">Remover do pedido (não cobrar cliente)</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#fca5a5;"></i>
                    </button>
                </div>
            </div>
        </div>

        @else
            {{-- ===== TELA DE RESUMO E EXPEDIÇÃO ===== --}}
            <main style="flex:1;background:var(--brand-500);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 20px;text-align:center;">
                <div style="width:80px;height:80px;background:#fff;color:var(--brand-500);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;margin-bottom:20px;box-shadow:0 10px 25px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h1 style="font-size:28px;font-weight:900;margin-bottom:6px;line-height:1.2;">Separação Concluída!</h1>
                <p style="color:#ecfdf5;font-size:15px;margin-bottom:32px;">O pedido #{{ $this->pedidoId }} está pronto para expedição.</p>

                <div style="background:rgba(255,255,255,0.1);backdrop-filter:blur(6px);border-radius:16px;padding:16px;width:100%;margin-bottom:32px;text-align:left;border:1px solid rgba(255,255,255,0.15);">
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:10px;margin-bottom:10px;font-size:14px;">
                        <span>Total de Itens</span>
                        <span style="font-weight:700;">{{ $prog['total'] }} itens</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:10px;margin-bottom:10px;font-size:14px;">
                        <span>Coletados (OK)</span>
                        <span style="font-weight:700;color:#a7f3d0;">{{ $prog['separados'] }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.2);padding-bottom:10px;margin-bottom:10px;font-size:14px;">
                        <span>Faltou / Não coletado</span>
                        <span style="font-weight:700;color:#fca5a5;">{{ $prog['faltou'] }}</span>
                    </div>
                    
                    {{-- Volumes (Sacolas) --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;">
                        <span>Volumes (Sacolas)</span>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <button @click="$wire.volumes = Math.max(1, parseInt($wire.volumes || 1) - 1)" style="width:24px;height:24px;border-radius:4px;border:none;background:rgba(255,255,255,0.2);color:#fff;cursor:pointer;font-size:12px;font-weight:bold;">−</button>
                            <input type="number" wire:model="volumes" style="width:36px;height:24px;text-align:center;border:none;background:transparent;color:#fff;font-size:14px;font-weight:800;outline:none;">
                            <button @click="$wire.volumes = parseInt($wire.volumes || 1) + 1" style="width:24px;height:24px;border-radius:4px;border:none;background:rgba(255,255,255,0.2);color:#fff;cursor:pointer;font-size:12px;font-weight:bold;">+</button>
                        </div>
                    </div>
                </div>

                <div style="width:100%;display:flex;flex-direction:column;gap:10px;">
                    @if (!$this->conferenciaAprovada)
                        <button wire:click="abrirConferencia" style="width:100%;background:#fff;color:#1f2937;padding:16px;border-radius:12px;font-weight:800;font-size:16px;border:none;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                            <i class="fas fa-clipboard-check mr-2"></i> Iniciar Conferência
                        </button>
                    @else
                        <button wire:click="finalizarSeparacao" style="width:100%;background:#fff;color:#059669;padding:16px;border-radius:12px;font-weight:800;font-size:16px;border:none;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                            <i class="fa-solid fa-check-double mr-2"></i> Confirmar e Finalizar ({{ $this->volumes }} vol)
                        </button>
                    @endif
                    
                    <a href="/separacao" wire:navigate style="background:transparent;border:1px solid rgba(255,255,255,0.4);color:#fff;padding:14px;border-radius:12px;font-weight:700;font-size:15px;text-decoration:none;display:inline-block;width:100%;">
                        Voltar para Fila
                    </a>
                </div>
            </main>
        @endif

    </div>

    {{-- ===== SUBSTITUTO MODAL ===== --}}
    <div x-show="$wire.showSubstituto" x-cloak class="drawer-overlay" style="align-items:center;" @click.self="$wire.fecharSubstituto()">
        <div class="drawer-sheet" style="border-radius:24px;width:95%;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
                <h3 style="font-size:18px;font-weight:800;color:#1f2937;margin:0;"><i class="fas fa-exchange-alt" style="color:var(--brand-500);"></i> Substituir produto</h3>
                <button @click="$wire.fecharSubstituto()" style="background:none;border:none;color:#9ca3af;font-size:22px;cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="field" style="margin-bottom:12px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#4b5563;margin-bottom:4px;">Buscar produto</label>
                <input wire:model="buscaSubstituto" wire:input.live="buscarSubstituto" placeholder="Digite o nome do produto..." style="width:100%;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;outline:none;">
            </div>
            
            @if (count($this->resultadosSubstituto) > 0)
                <div style="max-height:240px;overflow-y:auto;" class="no-scrollbar">
                    @foreach ($this->resultadosSubstituto as $r)
                        <button wire:click="selecionarSubstituto({{ $r['id'] }})" class="sub-card" style="width:100%;">
                            <div>
                                <div class="name">{{ $r['nome'] }}</div>
                                <div class="detail">SKU: {{ $r['sku'] ?: '—' }}</div>
                            </div>
                            @if (!empty($r['preco']))
                                <span class="price">R$ {{ number_format($r['preco'], 2, ',', '.') }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            @elseif (strlen(trim($this->buscaSubstituto)) >= 2)
                <p style="text-align:center;font-size:13px;color:#9ca3af;padding:16px 0;">Nenhum produto encontrado.</p>
            @endif
            <div style="margin-top:12px;display:flex;justify-content:flex-end;">
                <button class="btn" style="background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;padding:10px 20px;border-radius:8px;font-weight:700;cursor:pointer;" wire:click="fecharSubstituto">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- ===== SCANNER MODAL ===== --}}
    <div x-show="showScanner" x-cloak class="drawer-overlay" style="background:rgba(0,0,0,0.85);align-items:center;" @click.self="closeScanner()">
        <div style="width:100%;max-width:360px;padding:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <span style="color:#fff;font-weight:700;font-size:15px;"><i class="fas fa-camera"></i> Escaneie o código</span>
                <button @click="closeScanner()" style="background:rgba(255,255,255,0.15);border:0;color:#fff;font-size:16px;border-radius:50%;width:32px;height:32px;cursor:pointer;">&times;</button>
            </div>
            <div id="scanner-elem" style="width:100%;aspect-ratio:1;border-radius:12px;overflow:hidden;background:#000;"></div>
            <p x-show="scanError" x-text="scanError" style="color:#ef4444;font-size:12px;margin-top:8px;text-align:center;"></p>
            <p style="color:rgba(255,255,255,0.5);font-size:12px;text-align:center;margin-top:8px;">Aponte a câmera para o código de barras</p>
        </div>
    </div>

    @endif {{-- end bloqueio --}}
    </div>
</div>

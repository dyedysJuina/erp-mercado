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
    --bg-gray-100: #f3f4f6;
    --text-gray-800: #1f2937;
    --border-gray-100: #f3f4f6;
    --radius-xl: 12px;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* TABS */
.filtro-tabs {
    display: flex;
    gap: 6px;
    margin-top: 10px;
    overflow-x: auto;
    padding-bottom: 2px;
}
.filtro-tab {
    white-space: nowrap;
    padding: 8px 16px;
    border-radius: 9999px;
    border: 1px solid #e5e7eb;
    background: #fff;
    font-size: 13px;
    font-weight: 600;
    color: #4b5563;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.filtro-tab:hover {
    border-color: var(--brand-500);
    color: var(--brand-500);
}
.filtro-tab.active {
    background: var(--brand-500);
    color: #fff;
    border-color: var(--brand-500);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    transform: scale(1.03);
}
.filtro-tab-badge {
    background: rgba(0,0,0,0.06);
    color: inherit;
    padding: 1px 6px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 800;
}
.filtro-tab.active .filtro-tab-badge {
    background: rgba(255,255,255,0.2);
}

/* CARDS */
.order-card {
    background: #fff;
    border-radius: var(--radius-xl);
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #f3f4f6;
    position: relative;
    overflow: hidden;
    transition: all 0.25s;
}
.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
.order-card .strip {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}
.order-card .strip.red { background: #ef4444; }
.order-card .strip.yellow { background: #facc15; }
.order-card .strip.blue { background: #3b82f6; }

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

.modal-overlay {
    position:fixed;inset:0;background:rgba(31, 41, 55, 0.6);backdrop-filter:blur(6px);
    z-index:100;display:flex;align-items:flex-end;justify-content:center;
    padding:16px;
}
.modal-sheet {
    max-width:480px;width:100%;background:#fff;
    border-radius:16px 16px 0 0;
    padding:24px 20px 32px;max-height:80vh;overflow-y:auto;
}
.pedidos-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
}
@media (min-width: 600px) {
    .pedidos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div x-data="{
    show: @entangle('toastShow'),
    msg: @entangle('toastMsg'),
    showModal: false,
    modalHtml: '',
    openModal(html) { this.modalHtml = html; this.showModal = true; },
    closeModal() { this.showModal = false; this.modalHtml = ''; }
}"
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 3500) })"
@keydown.window.escape="showModal = false">

    {{-- TOAST --}}
    <div x-show="show" x-cloak
         class="toast-fixed" style="z-index:999;">
         <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    @php $dados = $this->pendentes(); @endphp

    <div style="max-width:800px;margin:0 auto;background:#f9fafb;min-height:100vh;display:flex;flex-direction:column;">

        {{-- ===== HEADER ===== --}}
        @php
            $nomeCompleto = auth()->user()?->name ?? 'Usuário';
            $nomes = explode(' ', $nomeCompleto);
            $iniciais = strtoupper(substr($nomes[0], 0, 1) . (isset($nomes[1]) ? substr($nomes[1], 0, 1) : ''));
        @endphp
        <header style="background:#fff;padding:16px 20px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;z-index:50;box-shadow:0 1px 2px rgba(0,0,0,0.03);">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div>
                    <h1 style="font-size:20px;font-weight:800;color:#111827;letter-spacing:-0.4px;">Fila de Separação</h1>
                    <p style="font-size:13px;color:#6b7280;font-weight:500;">Loja Centro</p>
                </div>
                <div style="height:36px;width:36px;border-radius:50%;background:var(--brand-500);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;box-shadow:0 2px 4px rgba(16, 185, 129, 0.2);" title="{{ $nomeCompleto }}">
                    {{ $iniciais }}
                </div>
            </div>

            {{-- Search Bar --}}
            <div style="display:flex;align-items:center;gap:10px;margin-top:14px;background:#f3f4f6;border-radius:10px;padding:4px 4px 4px 16px;border:1px solid #e5e7eb;">
                <i class="fas fa-search" style="color:#9ca3af;font-size:14px;"></i>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar pedido, cliente ou escanear..."
                       style="flex:1;border:none;background:transparent;padding:10px 0;font-size:14px;color:#1f2937;outline:none;min-width:0;">
                <button onclick="alert('📷 Scanner simulado')" style="background:#e5e7eb;border:none;color:#4b5563;padding:8px 14px;border-radius:8px;cursor:pointer;display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;">
                    <i class="fas fa-camera" style="font-size:16px;"></i> <span>Scan</span>
                </button>
            </div>

            {{-- Tabs Filtros --}}
            <div class="filtro-tabs no-scrollbar">
                <button wire:click="$set('filtroStatus', 'todos')" class="filtro-tab {{ $filtroStatus === 'todos' ? 'active' : '' }}">
                    <span>📋 Todos</span>
                    <span class="filtro-tab-badge">{{ $dados['contagem']['todos'] ?? 0 }}</span>
                </button>
                <button wire:click="$set('filtroStatus', 'novos')" class="filtro-tab {{ $filtroStatus === 'novos' ? 'active' : '' }}">
                    <span>🆕 Novos</span>
                    <span class="filtro-tab-badge">{{ $dados['contagem']['novos'] ?? 0 }}</span>
                </button>
                <button wire:click="$set('filtroStatus', 'separando')" class="filtro-tab {{ $filtroStatus === 'separando' ? 'active' : '' }}">
                    <span>⏳ Separando</span>
                    <span class="filtro-tab-badge">{{ $dados['contagem']['separando'] ?? 0 }}</span>
                </button>
                <button wire:click="$set('filtroStatus', 'separados')" class="filtro-tab {{ $filtroStatus === 'separados' ? 'active' : '' }}">
                    <span>✅ Separados</span>
                    <span class="filtro-tab-badge">{{ $dados['contagem']['separados'] ?? 0 }}</span>
                </button>
            </div>
        </header>

        {{-- ===== CONTEUDO ===== --}}
        <div style="flex:1;padding:16px 16px 100px;overflow-y:auto;" class="no-scrollbar">

            {{-- ATRASADOS --}}
            @if (!empty($dados['atrasados']))
                <div class="section-title">
                    <span class="dot danger"></span> Atrasados / Urgentes
                    <span style="font-size:11px;font-weight:700;background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:9999px;margin-left:auto;">{{ count($dados['atrasados']) }}</span>
                </div>
                <div class="pedidos-grid">
                    @foreach ($dados['atrasados'] as $p)
                        @include('livewire.separacao-card', ['p' => $p, 'type' => 'late'])
                    @endforeach
                </div>
            @endif

            {{-- EM ANDAMENTO --}}
            @if (!empty($dados['andamento']))
                <div class="section-title">
                    <span class="dot warning"></span> Em andamento
                    <span style="font-size:11px;font-weight:700;background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:9999px;margin-left:auto;">{{ count($dados['andamento']) }}</span>
                </div>
                <div class="pedidos-grid">
                    @foreach ($dados['andamento'] as $p)
                        @include('livewire.separacao-card', ['p' => $p, 'type' => 'urgent'])
                    @endforeach
                </div>
            @endif

            {{-- RECENTES --}}
            @if (!empty($dados['recentes']))
                <div class="section-title">
                    <span class="dot info"></span> Recentes
                    <span style="font-size:11px;font-weight:700;background:#e0e7ff;color:#4f46e5;padding:2px 8px;border-radius:9999px;margin-left:auto;">{{ count($dados['recentes']) }}</span>
                </div>
                <div class="pedidos-grid">
                    @foreach ($dados['recentes'] as $p)
                        @include('livewire.separacao-card', ['p' => $p, 'type' => 'recent'])
                    @endforeach
                </div>
            @endif

            {{-- EMPTY --}}
            @if (empty($dados['atrasados']) && empty($dados['andamento']) && empty($dados['recentes']))
                <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
                    <i class="fas fa-inbox" style="font-size:40px;display:block;margin-bottom:12px;opacity:0.5;"></i>
                    <p style="font-weight:600;color:#374151;">Nenhum pedido encontrado</p>
                    <p style="font-size:13px;color:#9ca3af;margin-top:2px;">Novos pedidos aparecerão aqui.</p>
                </div>
            @endif

            {{-- PAGINACAO --}}
            @if ($dados['paginator']->hasPages())
                <div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:16px 0;">
                    @if ($dados['paginator']->onFirstPage())
                        <span style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:8px;color:#9ca3af;font-size:12px;font-weight:600;opacity:0.5;">&laquo;</span>
                    @else
                        <button wire:click="previousPage" style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#1f2937;font-size:12px;font-weight:600;cursor:pointer;">&laquo;</button>
                    @endif
                    @php $c = $dados['paginator']->currentPage(); $l = $dados['paginator']->lastPage(); $s = max(1,$c-2); $e = min($l,$c+2); @endphp
                    @if ($s > 1)
                        <button wire:click="gotoPage(1)" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#1f2937;font-size:12px;font-weight:600;cursor:pointer;">1</button>
                        @if ($s > 2) <span style="color:#9ca3af;font-size:12px;">…</span> @endif
                    @endif
                    @for ($page = $s; $page <= $e; $page++)
                        @if ($page == $c)
                            <span style="padding:6px 12px;border:0;border-radius:8px;background:var(--brand-500);color:#fff;font-size:12px;font-weight:800;box-shadow:0 2px 8px rgba(16,185,129,0.3);">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#1f2937;font-size:12px;font-weight:600;cursor:pointer;">{{ $page }}</button>
                        @endif
                    @endfor
                    @if ($e < $l)
                        @if ($e < $l - 1) <span style="color:#9ca3af;font-size:12px;">…</span> @endif
                        <button wire:click="gotoPage({{ $l }})" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#1f2937;font-size:12px;font-weight:600;cursor:pointer;">{{ $l }}</button>
                    @endif
                    @if ($dados['paginator']->onLastPage())
                        <span style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:8px;color:#9ca3af;font-size:12px;font-weight:600;opacity:0.5;">&raquo;</span>
                    @else
                        <button wire:click="nextPage" style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;color:#1f2937;font-size:12px;font-weight:600;cursor:pointer;">&raquo;</button>
                    @endif
                </div>
            @endif
    </div>

    {{-- MODAL SYSTEM --}}
    <div x-show="showModal" x-cloak class="modal-overlay" @click.self="closeModal()">
        <div class="modal-sheet" x-html="modalHtml"></div>
    </div>
</div>
</div>

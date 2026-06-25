<div>
<script src="/js/html5-qrcode.min.js" defer></script>
<style>
/* ======================================
   VARIABLES - TEMA PROPRIO SEPARACAO
   ====================================== */
:root {
    --sep-primary: var(--warning);
    --sep-primary-dark: #d97706;
    --sep-primary-light: #fef3c7;
    --sep-gradient: linear-gradient(135deg,#f59e0b,#d97706);
    --sep-radius: 16px;
    --sep-radius-sm: 10px;
    --sep-shadow: 0 4px 24px rgba(0,0,0,0.06);
    --sep-shadow-lg: 0 12px 48px rgba(0,0,0,0.10);
}

/* ======================================
   TOAST
   ====================================== */
.toast-fixed {
    position:fixed;top:16px;left:50%;transform:translateX(-50%);
    background:var(--text);color:#fff;padding:12px 24px;border-radius:999px;
    font-size:14px;font-weight:700;z-index:999;
    display:flex;align-items:center;gap:10px;
    box-shadow:var(--sep-shadow-lg);max-width:90%;
}
.toast-icon { width:22px;height:22px;border-radius:50%;background:var(--success);
    display:flex;align-items:center;justify-content:center;font-size:11px; }
.toast-enter { transition:all 0.3s ease-out; }
.toast-leave { transition:all 0.2s ease-in; }
.toast-enter-start { opacity:0;transform:translateX(-50%) translateY(-20px) scale(0.95) !important; }
.toast-leave-end { opacity:0;transform:translateX(-50%) translateY(-20px) !important; }
[x-cloak] { display:none !important; }

/* ======================================
   MODAL BOTTOM SHEET
   ====================================== */
.modal-overlay {
    position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);
    z-index:100;display:flex;align-items:flex-end;justify-content:center;
    padding:16px;animation:fadeIn 0.2s ease;
}
@keyframes fadeIn { from{opacity:0} to{opacity:1} }
@keyframes slideUp { from{transform:translateY(40px);opacity:0} to{transform:translateY(0);opacity:1} }
.modal-sheet {
    max-width:480px;width:100%;background:var(--surface);
    border-radius:var(--sep-radius) var(--sep-radius) 0 0;
    padding:24px 20px 32px;max-height:80vh;overflow-y:auto;
    animation:slideUp 0.3s ease;
}
.modal-sheet h3 { font-size:18px;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:8px; }
.modal-sheet .field { margin-bottom:14px; }
.modal-sheet .field label { display:block;font-size:12px;font-weight:700;color:var(--muted);margin-bottom:4px; }
.modal-sheet .field input,
.modal-sheet .field textarea {
    width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:var(--sep-radius-sm);
    font-size:14px;outline:none;font-family:inherit;background:var(--surface);
}
.modal-sheet .field input:focus,
.modal-sheet .field textarea:focus { border-color:#f59e0b;box-shadow:0 0 0 3px color-mix(in srgb,#f59e0b 15%,transparent); }
.modal-sheet .field textarea { min-height:70px;resize:vertical; }
.modal-actions { display:flex;gap:8px;margin-top:12px;flex-wrap:wrap; }
.modal-actions .btn { flex:1;min-width:80px; }

/* ======================================
   SCROLLBAR
   ====================================== */
::-webkit-scrollbar { width:4px;height:4px; }
::-webkit-scrollbar-track { background:transparent; }
::-webkit-scrollbar-thumb { background:var(--border);border-radius:999px; }
</style>

<div x-data="{
    show: @entangle('toastShow'),
    msg: @entangle('toastMsg'),
    showModal: false,
    modalHtml: '',
    openModal(html) { this.modalHtml = html; this.showModal = true; },
    closeModal() { this.showModal = false; this.modalHtml = ''; },
    confirmCancel(id) {
        if (confirm('Cancelar pedido #' + id + '?')) {
            $wire.cancelarPedido(id);
            this.closeModal();
        }
    }
}"
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 3500) })"
@keydown.window.escape="showModal = false">

    {{-- TOAST --}}
    <div x-show="show" x-cloak
         x-transition:enter="toast-enter" x-transition:leave="toast-leave"
         class="toast-fixed" style="z-index:999;">
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    <div style="max-width:480px;margin:0 auto;background:var(--background);min-height:100vh;display:flex;flex-direction:column;">

        {{-- ===== HEADER ===== --}}
        <header style="background:var(--sep-gradient);color:#fff;padding:16px 20px 14px;position:sticky;top:0;z-index:20;box-shadow:0 2px 16px rgba(0,0,0,0.10);">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                        <i class="fas fa-boxes-stacked" style="font-size:16px;"></i>
                    </div>
                    <div>
                        <span style="font-weight:900;font-size:18px;letter-spacing:-0.3px;display:block;line-height:1.2;">SEPARAÇÃO</span>
                        <span style="font-size:10px;opacity:0.7;font-weight:500;">Pedidos pendentes</span>
                    </div>
                </div>
                <span style="background:rgba(255,255,255,0.18);backdrop-filter:blur(4px);font-size:12px;font-weight:700;padding:6px 14px;border-radius:999px;display:flex;align-items:center;gap:6px;border:1px solid rgba(255,255,255,0.08);">
                    <i class="fas fa-bell" style="font-size:12px;"></i> {{ $this->totalPendentes() }} pend.
                </span>
            </div>

            {{-- Search --}}
            <div style="display:flex;align-items:center;gap:8px;margin-top:12px;background:rgba(255,255,255,0.13);backdrop-filter:blur(8px);border-radius:var(--sep-radius-sm);padding:3px 3px 3px 14px;border:1px solid rgba(255,255,255,0.08);">
                <i class="fas fa-search" style="color:rgba(255,255,255,0.6);font-size:14px;"></i>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar pedido, cliente ou ID..."
                       style="flex:1;border:none;background:transparent;padding:10px 0;font-size:14px;color:#fff;outline:none;min-width:0;"">
                <button onclick="alert('📷 Scanner simulado')" style="background:rgba(255,255,255,0.15);border:none;color:#fff;padding:8px 14px;border-radius:var(--sep-radius-sm);cursor:pointer;display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;">
                    <i class="fas fa-camera" style="font-size:16px;"></i> <span>Scan</span>
                </button>
            </div>
        </header>

        {{-- ===== CONTEUDO ===== --}}
        <div style="flex:1;padding:12px 16px 100px;overflow-y:auto;">
            @php $dados = $this->pendentes(); @endphp

            {{-- ATRASADOS --}}
            @if (!empty($dados['atrasados']))
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);margin:12px 0 10px;display:flex;align-items:center;gap:8px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;display:inline-block;"></span> Atrasados
                    <span style="font-size:10px;font-weight:700;background:color-mix(in srgb,#ef4444 10%,transparent);color:#ef4444;padding:2px 8px;border-radius:999px;margin-left:auto;">{{ count($dados['atrasados']) }}</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach ($dados['atrasados'] as $p)
                        @include('livewire.separacao-card', ['p' => $p, 'danger' => true])
                    @endforeach
                </div>
            @endif

            {{-- NORMAIS --}}
            @if (!empty($dados['normais']))
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--muted);margin:16px 0 10px;display:flex;align-items:center;gap:8px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#f59e0b;display:inline-block;"></span> Pendentes
                    <span style="font-size:10px;font-weight:700;background:color-mix(in srgb,#f59e0b 10%,transparent);color:#f59e0b;padding:2px 8px;border-radius:999px;margin-left:auto;">{{ count($dados['normais']) }}</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach ($dados['normais'] as $p)
                        @include('livewire.separacao-card', ['p' => $p, 'danger' => false])
                    @endforeach
                </div>
            @endif

            {{-- EMPTY --}}
            @if (empty($dados['atrasados']) && empty($dados['normais']))
                <div style="text-align:center;padding:60px 20px;">
                    <div style="width:72px;height:72px;border-radius:50%;background:color-mix(in srgb,#f59e0b 8%,transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                        <i class="fas fa-box-open" style="font-size:28px;color:#f59e0b;opacity:0.5;"></i>
                    </div>
                    <p style="font-weight:800;color:var(--text);margin:0 0 4px;font-size:16px;">Nenhum pedido</p>
                    <p style="font-size:13px;color:var(--muted);margin:0;">Novos pedidos aparecerão aqui.</p>
                </div>
            @endif

            {{-- PAGINACAO --}}
            @if ($dados['paginator']->hasPages())
                <div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:16px 0;">
                    @if ($dados['paginator']->onFirstPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:12px;font-weight:600;opacity:0.5;">&laquo;</span>
                    @else
                        <button wire:click="previousPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;">&laquo;</button>
                    @endif
                    @php $c = $dados['paginator']->currentPage(); $l = $dados['paginator']->lastPage(); $s = max(1,$c-2); $e = min($l,$c+2); @endphp
                    @if ($s > 1)
                        <button wire:click="gotoPage(1)" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;">1</button>
                        @if ($s > 2) <span style="color:var(--muted);font-size:12px;">…</span> @endif
                    @endif
                    @for ($page = $s; $page <= $e; $page++)
                        @if ($page == $c)
                            <span style="padding:6px 12px;border:0;border-radius:8px;background:#f59e0b;color:#fff;font-size:12px;font-weight:800;box-shadow:0 2px 8px color-mix(in srgb,#f59e0b 30%,transparent);">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;">{{ $page }}</button>
                        @endif
                    @endfor
                    @if ($e < $l)
                        @if ($e < $l - 1) <span style="color:var(--muted);font-size:12px;">…</span> @endif
                        <button wire:click="gotoPage({{ $l }})" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;">{{ $l }}</button>
                    @endif
                    @if ($dados['paginator']->onLastPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:12px;font-weight:600;opacity:0.5;">&raquo;</span>
                    @else
                        <button wire:click="nextPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:12px;font-weight:600;cursor:pointer;">&raquo;</button>
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

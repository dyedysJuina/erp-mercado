@push('head')
<meta name="theme-color" content="#f59e0b">
<style>
    .toast-fixed { position:fixed;top:16px;left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:12px 20px;border-radius:12px;font-size:13px;font-weight:600;z-index:999;display:flex;align-items:center;gap:8px;box-shadow:0 8px 30px rgba(0,0,0,0.15);max-width:90%; }
    .toast-icon { width:20px;height:20px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;font-size:10px; }
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
}"
x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })">

    {{-- TOAST --}}
    <div x-show="show" x-cloak
         x-transition:enter="toast-enter" x-transition:leave="toast-leave"
         class="toast-fixed" style="z-index:999;">
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    <div style="max-width:480px;margin:0 auto;background:linear-gradient(180deg,color-mix(in srgb,#f59e0b,5%,var(--surface)) 0%,var(--surface) 40%);min-height:100vh;">

        {{-- ===== HEADER ===== --}}
        <header style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;padding:16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:20;box-shadow:0 2px 16px rgba(0,0,0,0.12);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                    <i class="fas fa-box" style="font-size:16px;"></i>
                </div>
                <div>
                    <span style="font-weight:900;font-size:18px;letter-spacing:2px;text-shadow:0 1px 4px rgba(0,0,0,0.2);display:block;line-height:1.2;">SEPARACAO</span>
                    <span style="font-size:10px;opacity:0.7;font-weight:500;">Pedidos pendentes</span>
                </div>
            </div>
            <span style="background:rgba(255,255,255,0.18);backdrop-filter:blur(4px);font-size:11px;font-weight:700;padding:4px 14px;border-radius:20px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-bell" style="font-size:10px;"></i> {{ $this->totalPendentes() }} pend.
            </span>
        </header>

        {{-- ===== BUSCA ===== --}}
        <div style="padding:12px 16px;border-bottom:1px solid var(--border);background:var(--surface);">
            <div style="position:relative;display:flex;align-items:center;">
                <i class="fas fa-search" style="position:absolute;left:12px;color:var(--muted);font-size:13px;z-index:1;"></i>
                <input wire:model.live.debounce.300ms="busca" placeholder="Buscar pedido, cliente ou ID..."
                       style="width:100%;padding:10px 12px 10px 36px;border:1px solid var(--border);border-radius:10px;font-size:13px;outline:none;background:color-mix(in srgb,var(--text)2%,var(--surface));transition:border-color 0.2s;"
                       @focus="$el.style.borderColor='#f59e0b'"
                       @blur="$el.style.borderColor='var(--border)'">
            </div>
        </div>

        {{-- ===== CONTEÚDO ===== --}}
        <div style="padding:12px 16px;">
            @php $dados = $this->pendentes(); @endphp

            {{-- ATRASADOS --}}
            @if (!empty($dados['atrasados']))
                <div style="margin-bottom:20px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>
                        <h3 style="font-size:10px;font-weight:800;color:#ef4444;text-transform:uppercase;letter-spacing:1px;margin:0;flex:1;">Atrasados</h3>
                        <span style="font-size:10px;font-weight:700;background:color-mix(in srgb,#ef4444,10%,transparent);color:#ef4444;padding:2px 8px;border-radius:10px;">{{ count($dados['atrasados']) }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach ($dados['atrasados'] as $p)
                            @include('livewire.separacao-card', ['p' => $p, 'danger' => true])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- NORMAIS --}}
            @if (!empty($dados['normais']))
                <div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;flex-shrink:0;"></span>
                        <h3 style="font-size:10px;font-weight:800;color:#f59e0b;text-transform:uppercase;letter-spacing:1px;margin:0;flex:1;">Pendentes</h3>
                        <span style="font-size:10px;font-weight:700;background:color-mix(in srgb,#f59e0b,10%,transparent);color:#f59e0b;padding:2px 8px;border-radius:10px;">{{ count($dados['normais']) }}</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        @foreach ($dados['normais'] as $p)
                            @include('livewire.separacao-card', ['p' => $p, 'danger' => false])
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- EMPTY STATE --}}
            @if (empty($dados['atrasados']) && empty($dados['normais']))
                <div style="text-align:center;padding:60px 20px;">
                    <div style="width:80px;height:80px;border-radius:50%;background:color-mix(in srgb,#f59e0b,8%,transparent);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <i class="fas fa-box-open" style="font-size:32px;color:#f59e0b;opacity:0.5;"></i>
                    </div>
                    <p style="font-weight:800;color:var(--text);margin:0 0 4px;font-size:16px;">Nenhum pedido para separar</p>
                    <p style="font-size:13px;color:var(--muted);margin:0;">Novos pedidos online aparecerão aqui automaticamente.</p>
                </div>
            @endif

            {{-- ===== PAGINAÇÃO ===== --}}
            @if ($dados['paginator']->hasPages())
                <div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:20px 0;">
                    @if ($dados['paginator']->onFirstPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:11px;font-weight:600;opacity:0.5;">&laquo;</span>
                    @else
                        <button wire:click="previousPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">&laquo;</button>
                    @endif
                    @php
                        $current = $dados['paginator']->currentPage();
                        $last = $dados['paginator']->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp
                    @if ($start > 1)
                        <button wire:click="gotoPage(1)" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">1</button>
                        @if ($start > 2) <span style="color:var(--muted);font-size:11px;">…</span> @endif
                    @endif
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $current)
                            <span style="padding:6px 12px;border:0;border-radius:8px;background:#f59e0b;color:#fff;font-size:11px;font-weight:800;box-shadow:0 2px 8px color-mix(in srgb,#f59e0b,30%,transparent);">{{ $page }}</span>
                        @else
                            <button wire:click="gotoPage({{ $page }})" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">{{ $page }}</button>
                        @endif
                    @endfor
                    @if ($end < $last)
                        @if ($end < $last - 1) <span style="color:var(--muted);font-size:11px;">…</span> @endif
                        <button wire:click="gotoPage({{ $last }})" style="padding:6px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">{{ $last }}</button>
                    @endif
                    @if ($dados['paginator']->onLastPage())
                        <span style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:11px;font-weight:600;opacity:0.5;">&raquo;</span>
                    @else
                        <button wire:click="nextPage" style="padding:6px 12px;border:1px solid var(--border);border-radius:8px;background:var(--surface);color:var(--text);font-size:11px;font-weight:600;cursor:pointer;">&raquo;</button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

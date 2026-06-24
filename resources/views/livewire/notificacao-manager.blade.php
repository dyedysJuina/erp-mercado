<div wire:poll.60s="verificarAlertas" style="font-family:'Inter',system-ui,sans-serif;">

    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>

    <div class="notif-container" style="max-width:1280px;width:100%;background:#ffffff;border-radius:24px;box-shadow:0 10px 25px -5px rgba(15,23,42,0.05),0 8px 16px -6px rgba(15,23,42,0.05);border:1px solid #e2e8f0;padding:2rem;">

        <div class="header-panel" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;gap:1rem;flex-wrap:wrap;">
            <div class="header-title">
                <h1 style="font-size:1.75rem;font-weight:800;color:#0f172a;letter-spacing:-0.03em;display:flex;align-items:center;gap:0.75rem;margin:0;">
                    <i class="fa-solid fa-bell" style="color:#2563eb;"></i> Centro de Notificacoes
                </h1>
            </div>
            <div class="header-actions" style="display:flex;gap:0.75rem;">
                @if ($this->totalNaoLidas > 0)
                    <button wire:click="marcarTodasLidas" class="btn btn-secondary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;border-radius:50px;font-size:0.875rem;font-weight:600;cursor:pointer;background:#ffffff;border:1px solid #cbd5e1;color:#475569;">
                        <i class="fa-solid fa-check-double"></i> Marcar todas como lidas
                    </button>
                @endif
                <button wire:click="verificarAlertas" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;border-radius:50px;font-size:0.875rem;font-weight:600;cursor:pointer;background:#991b1b;color:#ffffff;border:0;">
                    <i class="fa-solid fa-rotate"></i> Verificar Alertas
                </button>
            </div>
        </div>

        @php $t = $this->totais; @endphp
        <div class="metrics-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2rem;">
            <div class="metric-card unreads" style="background:rgba(239,68,68,0.04);border:1px solid rgba(239,68,68,0.15);border-radius:18px;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
                <div class="metric-info">
                    <span class="lbl" style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#991b1b;display:block;margin-bottom:0.25rem;">Nao Lidas</span>
                    <span class="num" style="font-size:2.25rem;font-weight:800;color:#0f172a;line-height:1;">{{ $t['pendentes'] }}</span>
                    <span class="sub-lbl" style="font-size:0.813rem;color:#64748b;margin-top:0.375rem;display:block;">Acoes urgentes pendentes</span>
                </div>
                <div class="metric-icon" style="width:48px;height:48px;border-radius:12px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#ef4444;"><i class="fa-solid fa-bell"></i></div>
            </div>
            <div class="metric-card" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
                <div class="metric-info">
                    <span class="lbl" style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;display:block;margin-bottom:0.25rem;">Pendentes</span>
                    <span class="num" style="font-size:2.25rem;font-weight:800;color:#0f172a;line-height:1;">{{ $t['pendentes'] }}</span>
                    <span class="sub-lbl" style="font-size:0.813rem;color:#64748b;margin-top:0.375rem;display:block;">Aguardando confirmacao</span>
                </div>
                <div class="metric-icon" style="width:48px;height:48px;border-radius:12px;background:#edf2f7;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#475569;"><i class="fa-solid fa-hourglass-half"></i></div>
            </div>
            <div class="metric-card" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
                <div class="metric-info">
                    <span class="lbl" style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;display:block;margin-bottom:0.25rem;">Historico Lido</span>
                    <span class="num" style="font-size:2.25rem;font-weight:800;color:#0f172a;line-height:1;">{{ $t['lidas'] }}</span>
                    <span class="sub-lbl" style="font-size:0.813rem;color:#64748b;margin-top:0.375rem;display:block;">Registros arquivados</span>
                </div>
                <div class="metric-icon" style="width:48px;height:48px;border-radius:12px;background:#edf2f7;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#475569;"><i class="fa-solid fa-circle-check"></i></div>
            </div>
        </div>

        <div class="filter-bar" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:100px;padding:0.5rem 0.5rem 0.5rem 1.5rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
            <div class="filter-chips" style="display:flex;align-items:center;gap:0.5rem;">
                <span class="filter-label" style="font-size:0.813rem;font-weight:700;color:#475569;margin-right:0.5rem;"><i class="fa-solid fa-filter"></i> Filtrar por:</span>
                <button wire:click="$set('filtroTipo', '')" class="chip {{ $filtroTipo === '' ? 'active' : '' }}" style="background:{{ $filtroTipo === '' ? '#0f172a' : '#ffffff' }};border:1px solid {{ $filtroTipo === '' ? '#0f172a' : '#cbd5e1' }};padding:0.4rem 1rem;border-radius:50px;font-size:0.813rem;font-weight:600;color:{{ $filtroTipo === '' ? '#ffffff' : '#475569' }};cursor:pointer;display:inline-flex;align-items:center;gap:0.375rem;">
                    Todas
                </button>
                @foreach ($this->tiposFiltro as $tipo)
                    <button wire:click="$set('filtroTipo', '{{ $tipo }}')" class="chip {{ $filtroTipo === $tipo ? 'active' : '' }}" style="background:{{ $filtroTipo === $tipo ? '#0f172a' : '#ffffff' }};border:1px solid {{ $filtroTipo === $tipo ? '#0f172a' : '#cbd5e1' }};padding:0.4rem 1rem;border-radius:50px;font-size:0.813rem;font-weight:600;color:{{ $filtroTipo === $tipo ? '#ffffff' : '#475569' }};cursor:pointer;display:inline-flex;align-items:center;gap:0.375rem;">
                        @php $icon = match($tipo) { 'Estoque Baixo' => 'fa-boxes-stacked', 'Lote Vencendo' => 'fa-clock', 'Conta a Pagar' => 'fa-file-invoice', 'Novo Pedido Online' => 'fa-bag-shopping', default => 'fa-bell' }; @endphp
                        <i class="fa-solid {{ $icon }}"></i> {{ $tipo }}
                    </button>
                @endforeach
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <div class="search-wrapper" style="position:relative;width:200px;">
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:0.875rem;"></i>
                    <input wire:model.live.debounce.300ms="busca" type="text" class="search-input" placeholder="Buscar nas mensagens..." style="width:100%;background:#ffffff;border:1px solid #cbd5e1;padding:0.5rem 1rem 0.5rem 2.5rem;border-radius:50px;font-size:0.875rem;outline:none;color:#0f172a;">
                </div>
                <input wire:model="dataInicio" type="date" style="height:34px;padding:0 0.5rem;border:1px solid #cbd5e1;border-radius:50px;font-size:0.8rem;background:#fff;color:#0f172a;outline:none;width:120px;">
                <input wire:model="dataFim" type="date" style="height:34px;padding:0 0.5rem;border:1px solid #cbd5e1;border-radius:50px;font-size:0.8rem;background:#fff;color:#0f172a;outline:none;width:120px;">
            </div>
        </div>

        @if ($aba === 'pendentes')
            @php $lista = $this->pendentes(); @endphp
        @else
            @php $lista = $this->todas(); @endphp
        @endif

        <div class="table-container" style="border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;background:#ffffff;">
            <table class="notif-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:0.875rem;">
                <thead>
                    <tr>
                        <th style="width:100px;background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;text-align:center;">Status</th>
                        <th style="width:130px;background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;">Data</th>
                        <th style="width:130px;background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;">Categoria</th>
                        <th style="background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;">Mensagem</th>
                        <th style="width:80px;text-align:center;background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;">Link</th>
                        <th style="width:60px;text-align:center;background:#f8fafc;padding:1rem 1.25rem;font-weight:700;text-transform:uppercase;font-size:0.75rem;letter-spacing:0.05em;color:#64748b;border-bottom:1px solid #e2e8f0;">Acao</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lista as $n)
                        <tr onclick="event.preventDefault(); $wire.marcarLida({{ $n['id'] }})" style="cursor:pointer;transition:background 0.15s;">
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;vertical-align:middle;" onclick="event.stopPropagation()">
                                @php $isLida = $n['status'] === 'lida'; @endphp
                                <div class="switch-container" style="display:inline-flex;align-items:center;gap:0.5rem;">
                                    <label class="switch" style="position:relative;display:inline-block;width:36px;height:20px;flex-shrink:0;">
                                        <input type="checkbox" {{ $isLida ? '' : 'checked' }} onchange="$wire.marcarLida({{ $n['id'] }})" style="opacity:0;width:0;height:0;">
                                        <span class="slider" style="position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;{{ $isLida ? 'background-color:#cbd5e1;' : 'background-color:#10b981;' }}transition:.2s;border-radius:20px;">
                                            <span style="position:absolute;content:'';height:14px;width:14px;left:3px;bottom:3px;background-color:white;transition:.2s;border-radius:50%;{{ $isLida ? 'transform:translateX(0);' : 'transform:translateX(16px);' }}"></span>
                                        </span>
                                    </label>
                                    <span class="badge-status {{ $isLida ? 'read' : 'unread' }}" style="padding:0.25rem 0.5rem;border-radius:50px;font-size:0.7rem;font-weight:700;display:inline-flex;align-items:center;gap:0.3rem;white-space:nowrap;{{ $isLida ? 'background:rgba(16,185,129,0.1);color:#059669;' : 'background:rgba(239,68,68,0.1);color:#dc2626;' }}">
                                        <i class="fa-solid fa-circle" style="font-size:0.5rem;"></i> {{ $isLida ? 'Lida' : 'Nao Lida' }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;color:#334155;vertical-align:middle;font-size:0.8rem;white-space:nowrap;">{{ \Carbon\Carbon::parse($n['created_at'])->format('d/m/Y, H:i') }}</td>
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;color:#334155;vertical-align:middle;white-space:nowrap;">
                                @php
                                    $tag = match($n['titulo']) {
                                        'Estoque Baixo' => ['class' => 'stock', 'icon' => 'fa-triangle-exclamation', 'color' => '#9a3412', 'bg' => '#fff7ed'],
                                        'Lote Vencendo' => ['class' => 'price', 'icon' => 'fa-clock', 'color' => '#1e40af', 'bg' => '#eff6ff'],
                                        'Conta a Pagar' => ['class' => 'price', 'icon' => 'fa-file-invoice', 'color' => '#1e40af', 'bg' => '#eff6ff'],
                                        'Novo Pedido Online' => ['class' => 'order', 'icon' => 'fa-basket-shopping', 'color' => '#065f46', 'bg' => '#ecfdf5'],
                                        default => ['class' => 'order', 'icon' => 'fa-bell', 'color' => '#065f46', 'bg' => '#ecfdf5']
                                    };
                                @endphp
                                <span class="tag-category {{ $tag['class'] }}" style="padding:0.3rem 0.6rem;border-radius:6px;font-size:0.7rem;font-weight:600;display:inline-flex;align-items:center;gap:0.3rem;white-space:nowrap;background:{{ $tag['bg'] }};color:{{ $tag['color'] }};">
                                    <i class="fa-solid {{ $tag['icon'] }}" style="font-size:0.65rem;"></i> {{ $n['titulo'] }}
                                </span>
                            </td>
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;color:#334155;vertical-align:middle;word-break:break-word;line-height:1.4;">
                                <span class="msg-text" style="font-weight:500;color:#0f172a;">{{ $n['mensagem'] }}</span>
                            </td>
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;vertical-align:middle;text-align:center;" onclick="event.stopPropagation()">
                                @php
                                    $linkInfo = match($n['titulo']) {
                                        'Estoque Baixo' => ['url' => $n['link'] ?? '/estoque', 'texto' => 'Gerenciar'],
                                        'Lote Vencendo' => ['url' => $n['link'] ?? '/lotes', 'texto' => 'Ver Lote'],
                                        'Conta a Pagar' => ['url' => $n['link'] ?? '/financeiro', 'texto' => 'Ver Conta'],
                                        'Novo Pedido Online' => ['url' => '/pedidos-online', 'texto' => 'Ver Pedido'],
                                        'Entrada de Mercadoria' => ['url' => '/nfe-entrada', 'texto' => 'Ver Nota'],
                                        default => ['url' => $n['link'] ?? '#', 'texto' => 'Abrir'],
                                    };
                                @endphp
                                <a href="{{ $linkInfo['url'] }}" wire:navigate onclick="$wire.marcarLida({{ $n['id'] }})" class="btn-action-view" style="color:#2563eb;text-decoration:none;font-weight:600;font-size:0.8rem;display:inline-flex;align-items:center;gap:0.25rem;white-space:nowrap;">
                                    <i class="fa-solid fa-external-link-alt"></i> {{ $linkInfo['texto'] }}
                                </a>
                            </td>
                            <td style="padding:1.1rem 1.25rem;border-bottom:1px solid #e2e8f0;text-align:center;vertical-align:middle;" onclick="event.stopPropagation()">
                                <button wire:click="excluir({{ $n['id'] }})" wire:confirm="Excluir?" class="btn-options" style="background:transparent;border:none;color:#94a3b8;cursor:pointer;font-size:1.1rem;width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;" title="Excluir">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:3rem 1.25rem;color:#64748b;">
                                <i class="fa-solid fa-check-circle" style="font-size:2.5rem;display:block;margin-bottom:0.75rem;color:#10b981;opacity:0.4;"></i>
                                <p style="font-size:1rem;font-weight:600;color:#0f172a;margin:0 0 0.25rem;">Tudo em dia!</p>
                                <p style="font-size:0.85rem;margin:0;">Nenhuma notificacao encontrada.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer-bar" style="display:flex;justify-content:space-between;align-items:center;margin-top:1.25rem;font-size:0.813rem;color:#64748b;padding:0 0.5rem;flex-wrap:wrap;gap:0.5rem;">
            <div>Exibindo {{ $lista->firstItem() ?? 0 }} a {{ $lista->lastItem() ?? 0 }} de {{ $lista->total() }} notificacao(oes)</div>
            @if ($lista->hasPages())
                <div class="pagination" style="display:flex;align-items:center;gap:0.5rem;">
                    <button wire:click="previousPage" class="page-btn" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;color:#475569;{{ $lista->onFirstPage() ? 'opacity:0.4;cursor:default;' : '' }}" {{ $lista->onFirstPage() ? 'disabled' : '' }}>
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    @foreach ($lista->getUrlRange(1, $lista->lastPage()) as $page => $url)
                        <button wire:click="gotoPage({{ $page }})" class="page-btn" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;cursor:pointer;font-weight:600;{{ $page === $lista->currentPage() ? 'background:#0f172a;color:#ffffff;border-color:#0f172a;' : 'border:1px solid #cbd5e1;background:#ffffff;color:#475569;' }}">
                            {{ $page }}
                        </button>
                    @endforeach
                    <button wire:click="nextPage" class="page-btn" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;border:1px solid #cbd5e1;background:#ffffff;cursor:pointer;color:#475569;{{ !$lista->hasMorePages() ? 'opacity:0.4;cursor:default;' : '' }}" {{ !$lista->hasMorePages() ? 'disabled' : '' }}>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            @endif
        </div>

    </div>
</div>

@php
$overridesCss = app(\App\Services\ThemeService::class)->getAllOverridesCss();
@endphp
<!doctype html>
<html lang="pt-BR" data-theme="{{ auth()->user()?->theme ?? 'verde' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style id="database-theme-tokens">{!! $overridesCss !!}</style>
</head>
<body>

{{-- Hamburger mobile --}}
@unless ($noSidebar ?? false)
<button class="hamburger-mobile" id="hamburgerBtn" aria-label="Abrir menu">
    <i class="fas fa-bars"></i>
</button>

{{-- Overlay --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>
@endunless

{{-- Sidebar --}}
@unless ($noSidebar ?? false)
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="fas fa-store-alt"></i>
            <span class="brand-text">ERP Mercado</span>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Expandir/recolher">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-menu" x-data="{
        navOpen: true, operOpen: true, finOpen: true,
        checkActive() {
            const cur = '{{ request()->route()?->getName() }}';
            // Auto-expand section if a link inside is active
            @php $navRoutes = ['dashboard','categorias','atributos','unidades-medida','embalagens','marcas','produtos-base','variacoes','clientes']; @endphp
            if ({{ in_array(request()->route()?->getName(), $navRoutes) ? 'true' : 'false' }}) this.navOpen = true;
            @php $operRoutes = ['fornecedores','compras','nfe-entrada','importador','sugestao-compras','pedidos-online','estoque','precos','precos.historico','etiquetas','ofertas','vitrine','lojas']; @endphp
            if ({{ in_array(request()->route()?->getName(), $operRoutes) ? 'true' : 'false' }}) this.operOpen = true;
            @php $finRoutes = ['vendas','financeiro','financeiro.conciliacao','fluxo-caixa','relatorios','curva-abc','dre','margem-departamento','usuarios','papeis','admin.gerencial','dashboard-executivo','admin.themes']; @endphp
            if ({{ in_array(request()->route()?->getName(), $finRoutes) ? 'true' : 'false' }}) this.finOpen = true;
        }
    }" x-init="checkActive()">
        @php $current = request()->route()?->getName(); @endphp

        <span class="menu-label" @@click="navOpen = !navOpen" style="cursor:pointer;">
            <span>Navegação</span>
            <i class="fas fa-chevron-down" style="font-size:9px;transition:transform 0.2s;" x-bind:style="navOpen ? 'transform:rotate(0deg)' : 'transform:rotate(-90deg)'"></i>
        </span>
        <div x-show="navOpen">
            @foreach ([
                'dashboard' => ['fa-th-large', 'Visão geral', 'dashboard'],
                'categorias' => ['fa-sitemap', 'Categorias', 'categorias'],
                'atributos' => ['fa-tags', 'Atributos', 'atributos'],
                'unidades-medida' => ['fa-ruler', 'Unidades', 'unidades'],
                'embalagens' => ['fa-box', 'Embalagens', 'embalagens'],
                'marcas' => ['fa-trademark', 'Marcas', 'marcas'],
                'produtos-base' => ['fa-cube', 'Produtos Base', 'produtos-base'],
                'variacoes' => ['fa-cubes', 'Variações', 'variacoes'],
                'clientes' => ['fa-user', 'Clientes', 'clientes'],
            ] as $rota => [$icon, $label, $perm])
                @can($perm)
                    @if (Route::has($rota))
                        <a href="{{ route($rota) }}" wire:navigate class="{{ $current === $rota ? 'active' : '' }}">
                            <i class="fas {{ $icon }}"></i>
                            <span class="menu-text">{{ $label }}</span>
                        </a>
                    @endif
                @endcan
            @endforeach
        </div>

        <div class="divider"></div>

        <span class="menu-label" @@click="operOpen = !operOpen" style="cursor:pointer;">
            <span>Operacional</span>
            <i class="fas fa-chevron-down" style="font-size:9px;transition:transform 0.2s;" x-bind:style="operOpen ? 'transform:rotate(0deg)' : 'transform:rotate(-90deg)'"></i>
        </span>
        <div x-show="operOpen">
            @foreach ([
                'fornecedores' => ['fa-truck', 'Fornecedores', 'fornecedores'],
                'pedidos' => ['fa-shopping-bag', 'Pedidos Compra', 'pedidos'],
                'pedidos-online' => ['fa-globe', 'Pedidos Online', 'pedidos'],
                'compras' => ['fa-pen', 'Nova Compra', 'compras'],
                'nfe-entrada' => ['fa-file-import', 'NF-e Entrada', 'compras'],
                'importador' => ['fa-file-upload', 'Importador CSV', 'dashboard'],
                'sugestao-compras' => ['fa-lightbulb', 'Sugestão de Compras', 'compras'],
                'estoque' => ['fa-warehouse', 'Estoque', 'estoque'],
                'lotes' => ['fa-calendar-alt', 'Validades', 'lotes'],
                'precos' => ['fa-dollar-sign', 'Precos', 'precos'],
                'precos.historico' => ['fa-clock-rotate-left', 'Historico de Precos', 'precos'],
                'etiquetas' => ['fa-tag', 'Etiquetas', 'precos'],
                'ofertas' => ['fa-bullhorn', 'Ofertas', 'ofertas'],
                'vitrine' => ['fa-store', 'Vitrine', 'dashboard'],
                'lojas' => ['fa-store', 'Lojas', 'lojas'],
            ] as $rota => [$icon, $label, $perm])
                @can($perm)
                    @if (Route::has($rota))
                        <a href="{{ route($rota) }}" wire:navigate class="{{ $current === $rota ? 'active' : '' }}">
                            <i class="fas {{ $icon }}"></i>
                            <span class="menu-text">{{ $label }}</span>
                        </a>
                    @endif
                @endcan
            @endforeach
        </div>

        <div class="divider"></div>

        <span class="menu-label" @@click="finOpen = !finOpen" style="cursor:pointer;">
            <span>Vendas & Financeiro</span>
            <i class="fas fa-chevron-down" style="font-size:9px;transition:transform 0.2s;" x-bind:style="finOpen ? 'transform:rotate(0deg)' : 'transform:rotate(-90deg)'"></i>
        </span>
        <div x-show="finOpen">
            @foreach ([
                'vendas' => ['fa-cash-register', 'PDV', 'pdv'],
                'financeiro' => ['fa-chart-pie', 'Financeiro', 'financeiro'],
                'financeiro.conciliacao' => ['fa-check-double', 'Conciliação', 'financeiro.conciliacao'],
                'fluxo-caixa' => ['fa-scale-balanced', 'Fluxo de Caixa', 'financeiro'],
                'relatorios' => ['fa-chart-bar', 'Relatórios', 'relatorios'],
                'curva-abc' => ['fa-chart-pie', 'Curva ABC', 'relatorios'],
                'dre' => ['fa-file-invoice-dollar', 'DRE', 'relatorios'],
                'margem-departamento' => ['fa-layer-group', 'Margem por Depto', 'relatorios'],
                'usuarios' => ['fa-users-cog', 'Usuários', 'usuarios'],
                'papeis' => ['fa-shield', 'Papéis', 'papeis'],
                'admin.gerencial' => ['fa-chart-pie', 'Gerencial', 'admin.gerencial'],
                'dashboard-executivo' => ['fa-gauge-high', 'Dashboard Executivo', 'admin.gerencial'],
                'admin.themes' => ['fa-palette', 'Temas', 'admin.temas'],
                'admin.fiscal' => ['fa-file-invoice', 'Certificado', 'admin.fiscal'],
                'admin.testar-regras' => ['fa-flask', 'Regras Fiscais', 'admin.temas'],
            ] as $rota => [$icon, $label, $perm])
                @can($perm)
                    @if (Route::has($rota))
                        <a href="{{ route($rota) }}" wire:navigate class="{{ $current === $rota ? 'active' : '' }}">
                            <i class="fas {{ $icon }}"></i>
                            <span class="menu-text">{{ $label }}</span>
                        </a>
                    @endif
                @endcan
            @endforeach
        </div>
    </nav>

    <div class="sidebar-footer">
        @auth
            <livewire:theme-switcher :key="auth()->id()" />
        @endauth
        <form method="POST" action="{{ route('logout') }}" style="margin-top:8px;">
            @csrf
            <button class="logout-btn" type="submit">
                <i class="fas fa-sign-out-alt"></i>
                <span class="logout-text">Sair</span>
            </button>
        </form>
    </div>
</aside>
@endunless

{{-- Main content --}}
<main class="main-content" id="mainContent" style="{{ ($noSidebar ?? false) ? 'margin-left:0;background:transparent;' : '' }}">
    @unless ($noSidebar ?? false)
    <header class="content-header">
        <div>
            <p class="header-subtitle" style="color:var(--muted);">{{ auth()->user()?->name ?? 'Sistema' }}</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <livewire:notification-bell :key="'bell-' . auth()->id()" wire:key="notification-bell" />
            <div class="header-status" style="color:var(--success);background-color:color-mix(in srgb, var(--success) 10%, transparent);">
            <span class="status-dot" style="background-color:var(--success);"></span>
            Conectado
        </div>
    </header>
    @endunless
    <div class="content-body" style="{{ ($noSidebar ?? false) ? 'padding:0;' : '' }}">
        {{ $slot }}
    </div>
</main>

@auth
    <livewire:notification-modal :key="'notif-modal-' . auth()->id()" />
@endauth

<script>
(function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const overlay = document.getElementById('sidebarOverlay');

    let isExpanded = false;
    let isMobileOpen = false;
    let hoverTimeout = null;

    function isMobile() { return window.innerWidth <= 768; }

    function expandSidebar(expand) {
        if (isMobile()) return;
        isExpanded = expand;
        sidebar.classList.toggle('expanded', expand);
        mainContent.classList.toggle('shifted', expand);
        toggleBtn.innerHTML = expand ? '<i class="fas fa-chevron-left"></i>' : '<i class="fas fa-chevron-right"></i>';
    }

    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (isMobile()) return;
        expandSidebar(!isExpanded);
    });

    sidebar.addEventListener('mouseenter', function() {
        if (isMobile()) return;
        clearTimeout(hoverTimeout);
        if (!isExpanded) expandSidebar(true);
    });

    sidebar.addEventListener('mouseleave', function(ev) {
        if (isMobile()) return;
        clearTimeout(hoverTimeout);
        const mouseX = ev.clientX;
        hoverTimeout = setTimeout(() => {
            if (!isExpanded) return;
            const rect = sidebar.getBoundingClientRect();
            if (mouseX > rect.right || mouseX < rect.left) expandSidebar(false);
        }, 200);
    });

    function toggleMobileMenu(open) {
        isMobileOpen = open !== undefined ? open : !isMobileOpen;
        sidebar.classList.toggle('mobile-open', isMobileOpen);
        overlay.classList.toggle('active', isMobileOpen);
        hamburgerBtn.classList.toggle('hidden', isMobileOpen);
        hamburgerBtn.innerHTML = isMobileOpen ? '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        document.body.style.overflow = isMobileOpen ? 'hidden' : '';
    }

    hamburgerBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleMobileMenu();
    });

    overlay.addEventListener('click', function() { toggleMobileMenu(false); });

    window.addEventListener('resize', function() {
        if (!isMobile()) {
            if (isMobileOpen) toggleMobileMenu(false);
            sidebar.classList.remove('mobile-open');
        } else {
            sidebar.classList.remove('expanded');
            mainContent.classList.remove('shifted');
        }
    });

    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function() {
            document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            if (isMobile() && isMobileOpen) toggleMobileMenu(false);
        });
    });

    if (!isMobile()) expandSidebar(false);
})();
</script>

<style>
*{margin:0;padding:0;box-sizing:border-box}

:root{--sidebar-width:260px;--sidebar-collapsed:72px}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-collapsed);background:var(--sidebar-surface);padding:0;display:flex;flex-direction:column;transition:all 0.3s cubic-bezier(0.4,0,0.2,1);z-index:1000;overflow:hidden;border-right:1px solid color-mix(in srgb, var(--sidebar-text) 8%, transparent)}
.sidebar.expanded,.sidebar.mobile-open{width:var(--sidebar-width)}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);backdrop-filter:blur(4px);z-index:999;opacity:0;transition:opacity 0.3s}
.sidebar-overlay.active{display:block;opacity:1}

.sidebar-header{display:flex;align-items:center;justify-content:space-between;padding:16px 14px 16px 16px;border-bottom:1px solid color-mix(in srgb, var(--sidebar-text) 8%, transparent);flex-shrink:0;min-height:60px}
.sidebar-brand{display:flex;align-items:center;gap:10px;overflow:hidden;white-space:nowrap}
.sidebar-brand i{font-size:24px;color:var(--primary-500);flex-shrink:0}
.sidebar-brand .brand-text{font-size:18px;font-weight:700;color:var(--sidebar-text);opacity:0;transition:all 0.3s;transform:translateX(-6px)}
.sidebar.expanded .sidebar-brand .brand-text,.sidebar.mobile-open .sidebar-brand .brand-text{opacity:1;transform:translateX(0)}
.sidebar-toggle{background:none;border:none;color:var(--sidebar-text);font-size:16px;cursor:pointer;padding:4px 6px;border-radius:6px;transition:all 0.2s;flex-shrink:0;opacity:0.6}
.sidebar-toggle:hover{opacity:1}

.sidebar-menu{flex:1;padding:12px 10px;overflow:hidden}
.sidebar-menu:hover{overflow-y:auto}
.sidebar-menu::-webkit-scrollbar{width:2px}
.sidebar-menu::-webkit-scrollbar-thumb{background:var(--sidebar-muted);border-radius:10px}
.sidebar-menu .menu-label{font-size:10px;text-transform:uppercase;letter-spacing:0.8px;color:var(--sidebar-muted);padding:12px 12px 6px 12px;font-weight:600;opacity:0;transition:all 0.3s;white-space:nowrap;display:flex;align-items:center;justify-content:space-between}
.sidebar.expanded .sidebar-menu .menu-label,.sidebar.mobile-open .sidebar-menu .menu-label{opacity:0.7}
.sidebar-menu a{display:flex;align-items:center;gap:12px;padding:9px 12px;border-radius:8px;text-decoration:none;color:var(--sidebar-muted);font-weight:500;font-size:13px;transition:all 0.2s;position:relative;white-space:nowrap;margin-bottom:1px}
.sidebar-menu a i{width:20px;font-size:16px;text-align:center;flex-shrink:0}
.sidebar-menu a .menu-text{opacity:0;transition:all 0.2s;transform:translateX(-4px)}
.sidebar.expanded .sidebar-menu a .menu-text,.sidebar.mobile-open .sidebar-menu a .menu-text{opacity:1;transform:translateX(0)}
.sidebar-menu a:hover{background:color-mix(in srgb, var(--sidebar-text) 6%, transparent);color:var(--sidebar-text)}
.sidebar-menu a.active{background:color-mix(in srgb, var(--primary-500) 12%, transparent);color:var(--primary-500);font-weight:600}
.sidebar-menu a.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:20px;background:var(--primary-500);border-radius:0 4px 4px 0}
.sidebar-menu .divider{height:1px;background:color-mix(in srgb, var(--sidebar-text) 8%, transparent);margin:10px 12px;opacity:0;transition:all 0.3s}
.sidebar.expanded .sidebar-menu .divider,.sidebar.mobile-open .sidebar-menu .divider{opacity:1}

.sidebar-footer{padding:10px 10px 14px;border-top:1px solid color-mix(in srgb, var(--sidebar-text) 8%, transparent);flex-shrink:0}
.sidebar-footer .logout-btn{display:flex;align-items:center;gap:12px;padding:9px 12px;border-radius:8px;border:none;background:transparent;color:var(--sidebar-muted);cursor:pointer;width:100%;transition:all 0.2s;font-size:13px;font-weight:500}
.sidebar-footer .logout-btn i{width:20px;font-size:16px;text-align:center;flex-shrink:0}
.sidebar-footer .logout-btn .logout-text{opacity:0;transition:all 0.2s;transform:translateX(-4px)}
.sidebar.expanded .sidebar-footer .logout-btn .logout-text,.sidebar.mobile-open .sidebar-footer .logout-btn .logout-text{opacity:1;transform:translateX(0)}
.sidebar-footer .logout-btn:hover{background:color-mix(in srgb, var(--danger) 12%, transparent);color:var(--danger)}

.hamburger-mobile{display:none;position:fixed;top:14px;left:14px;z-index:1001;background:var(--sidebar-surface);color:var(--sidebar-text);border:none;width:42px;height:42px;border-radius:10px;font-size:18px;cursor:pointer;box-shadow:0 4px 12px color-mix(in srgb, var(--shadow) 15%, transparent);transition:all 0.2s}
.hamburger-mobile.hidden{opacity:0;pointer-events:none;transform:scale(0.8)}

.main-content{margin-left:var(--sidebar-collapsed);transition:all 0.3s cubic-bezier(0.4,0,0.2,1);min-height:100vh;background:var(--background)}
.main-content.shifted{margin-left:var(--sidebar-width)}
.content-header{display:flex;align-items:center;justify-content:space-between;padding:20px 28px;border-bottom:1px solid var(--border);background:var(--surface)}
.header-title{margin:0;font-size:20px;font-weight:700}
.header-subtitle{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px}
.header-status{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:50px;font-size:12px;font-weight:700}
.status-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
.content-body{padding:24px 28px}

@media(max-width:768px){
.hamburger-mobile{display:flex;align-items:center;justify-content:center}
.sidebar{transform:translateX(-100%);width:var(--sidebar-width)!important}
.sidebar.mobile-open{transform:translateX(0)}
.sidebar-overlay.active{display:block}
.main-content{margin-left:0!important;padding:0;padding-top:10px}
.content-body{padding:16px 16px}
.content-header{padding:14px 16px}
.sidebar .sidebar-brand .brand-text,.sidebar .sidebar-menu a .menu-text,.sidebar .sidebar-footer .logout-btn .logout-text,.sidebar .sidebar-menu .menu-label,.sidebar .sidebar-menu .divider{opacity:1!important;transform:none!important}
}
</style>
</body>
</html>

@props(['carrinhoCount' => 0])
<div x-data="{ menuOpen: false, cartCount: {{ $carrinhoCount }} }">
    {{-- HEADER --}}
    <header style="background-color:#6CC51D;width:100%;padding:12px 16px 14px;box-shadow:0 4px 16px rgba(0,0,0,0.10);position:sticky;top:0;z-index:100;">

        {{-- LINHA SUPERIOR --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <button x-on:click="menuOpen = true" style="background:transparent;border:none;font-size:28px;color:#fff;cursor:pointer;padding:4px 6px;display:flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;line-height:1;">
                <i class="fas fa-bars"></i>
            </button>

            <a href="/vitrine" wire:navigate style="font-size:26px;font-weight:700;letter-spacing:-0.5px;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,0.10);text-decoration:none;margin-right:auto;margin-left:8px;">
                Oferta<span style="color:#FFD700;font-weight:300;">X</span>
            </a>

            <button x-on:click="$dispatch('abrir-carrinho')" style="font-size:26px;color:#fff;background:rgba(255,255,255,0.12);width:48px;height:48px;border-radius:40px;display:flex;align-items:center;justify-content:center;transition:0.15s;text-decoration:none;position:relative;border:1px solid rgba(255,255,255,0.20);cursor:pointer;">
                <i class="fas fa-shopping-bag" style="font-size:26px;"></i>
                <span x-show="cartCount > 0" style="position:absolute;top:-2px;right:-2px;background:#FFD700;color:#1e3d0a;font-size:11px;font-weight:700;width:20px;height:20px;border-radius:30px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(255,215,0,0.5);border:2px solid #6CC51D;" x-text="cartCount > 99 ? '99+' : cartCount"></span>
            </button>
        </div>

        {{-- BARRA DE PESQUISA --}}
        <div style="display:flex;align-items:center;background-color:#fff;border-radius:60px;padding:0 8px 0 18px;box-shadow:0 2px 10px rgba(0,0,0,0.08);border:2px solid #FFD700;width:100%;transition:box-shadow 0.2s,border-color 0.2s;">
            <input type="text" placeholder="O que voce procura?" aria-label="Pesquisar"
                style="flex:1;border:none;outline:none;background:transparent;padding:14px 0;font-size:16px;color:#1e2b1a;font-weight:500;width:100%;"
                x-init="$el.addEventListener('keydown', function(e) { if(e.key==='Enter') { try { $wire.set('busca', this.value); } catch(e) {} } })">
            <button style="background:transparent;border:none;font-size:22px;color:#6CC51D;padding:10px 14px 10px 10px;cursor:pointer;border-radius:40px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-search" style="font-size:22px;"></i>
            </button>
        </div>
    </header>

    {{-- OVERLAY --}}
    <div x-show="menuOpen" x-on:click="menuOpen = false" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.25);backdrop-filter:blur(2px);z-index:998;" x-cloak></div>

    {{-- MENU LATERAL --}}
    <nav x-show="menuOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" style="position:fixed;top:0;left:0;width:280px;max-width:82%;height:100vh;background:#fff;box-shadow:6px 0 30px rgba(0,0,0,0.10);z-index:999;padding:28px 24px 30px;display:flex;flex-direction:column;overflow-y:auto;border-right:3px solid #FFD700;" x-cloak>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;border-bottom:3px solid #FFD700;padding-bottom:16px;">
            <span style="font-size:28px;font-weight:700;color:#6CC51D;">Oferta<span style="color:#FFD700;font-weight:300;">X</span></span>
            <button x-on:click="menuOpen = false" style="background:#FFF9C4;border:1px solid #FFD700;width:40px;height:40px;border-radius:40px;font-size:24px;color:#6CC51D;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;">
            <a href="/vitrine" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-size:20px;font-weight:500;padding:12px 14px;border-radius:16px;display:flex;align-items:center;gap:16px;background:#fafafa;border-left:4px solid transparent;">
                <i class="fas fa-home" style="width:28px;font-size:22px;color:#6CC51D;"></i> Inicio
            </a>
            <a href="/vitrine/ofertas" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-size:20px;font-weight:500;padding:12px 14px;border-radius:16px;display:flex;align-items:center;gap:16px;background:#fafafa;border-left:4px solid transparent;">
                <i class="fas fa-tag" style="width:28px;font-size:22px;color:#6CC51D;"></i> Ofertas
            </a>
            <a href="/vitrine/minha-conta" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-size:20px;font-weight:500;padding:12px 14px;border-radius:16px;display:flex;align-items:center;gap:16px;background:#fafafa;border-left:4px solid transparent;">
                <i class="fas fa-user" style="width:28px;font-size:22px;color:#6CC51D;"></i> Minha conta
            </a>
            <a href="/vitrine/pedidos" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-size:20px;font-weight:500;padding:12px 14px;border-radius:16px;display:flex;align-items:center;gap:16px;background:#fafafa;border-left:4px solid transparent;">
                <i class="fas fa-box" style="width:28px;font-size:22px;color:#6CC51D;"></i> Meus pedidos
            </a>
        </div>

        <div style="height:1px;background:#FFD700;margin:16px 0 10px;opacity:0.4;"></div>

        <div style="display:flex;flex-direction:column;gap:8px;padding:0 4px;">
            <a href="/vitrine/favoritos" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-weight:500;padding:10px 14px;border-radius:16px;background:#fafafa;display:flex;align-items:center;gap:14px;border-left:4px solid transparent;">
                <i class="fas fa-star" style="color:#6CC51D;width:24px;"></i> Favoritos
            </a>
            <a href="/vitrine/atendimento" wire:navigate x-on:click="menuOpen = false" style="text-decoration:none;color:#1e2b1a;font-weight:500;padding:10px 14px;border-radius:16px;background:#fafafa;display:flex;align-items:center;gap:14px;border-left:4px solid transparent;">
                <i class="fas fa-headset" style="color:#6CC51D;width:24px;"></i> Atendimento
            </a>
        </div>

        <div style="margin-top:auto;font-size:14px;color:#4d5e40;padding-top:20px;border-top:1px solid #FFD700;opacity:0.7;">
            <i class="fas fa-shield-alt" style="margin-right:6px;color:#FFD700;"></i> Compre com seguranca
            <div style="margin-top:8px;font-size:13px;color:#5b704b;">v2.0 · ERP Mercado</div>
        </div>
    </nav>
</div>

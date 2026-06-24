<div x-data="lojaApp" style="font-family:'Segoe UI',Roboto,system-ui,sans-serif;background:var(--background);min-height:100vh;">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-vitrine-header :carrinhoCount="0" />

    <style>
        .store-container { max-width:900px; margin:0 auto; padding:16px; }
        .product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; }
        .product-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:14px; display:flex; flex-direction:column; gap:8px; transition:box-shadow 0.2s; }
        .product-card:active { box-shadow:0 2px 8px rgba(0,0,0,0.08); }
        @media (min-width:640px) { .product-grid { grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; } }
        @media (min-width:1024px) { .product-grid { grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px; } }
    </style>

    <div class="store-container">

        <div class="product-grid">
            @forelse ($this->produtos as $p)
                <div class="product-card">
                    <div style="font-size:13px;font-weight:600;color:var(--text);line-height:1.3;">{{ $p['nome_completo'] }}</div>
                    <div style="font-size:22px;font-weight:900;color:#6CC51D;margin:4px 0;">R$ {{ number_format((float)$p['preco_venda'], 2, ',', '.') }}</div>
                    <div style="font-size:11px;color:var(--muted);">
                        Estoque: <span style="font-weight:600;color:{{ $p['estoque'] > 0 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format((float)$p['estoque'], 3, ',', '.') }}</span>
                    </div>
                    <button @@click="adicionar({{ $p['id'] }}, '{{ addslashes($p['nome_completo']) }}', {{ $p['preco_venda'] }}, {{ $p['estoque'] }})"
                            style="margin-top:auto;padding:10px;border:0;border-radius:10px;background:#6CC51D;color:#fff;font-weight:700;cursor:pointer;font-size:14px;"
                            x-bind:disabled="!podeAdicionar({{ $p['id'] }}, {{ $p['estoque'] }})"
                            x-text="!podeAdicionar({{ $p['id'] }}, {{ $p['estoque'] }}) ? 'Indisponivel' : '+ Adicionar'">
                    </button>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;padding:40px 20px;color:var(--muted);">
                    @if (strlen($this->busca) >= 2) Nenhum produto encontrado.
                    @else Nenhum produto disponivel. @endif
                </div>
            @endforelse
        </div>
        <div style="background:var(--surface);border-radius:20px 20px 0 0;width:100%;max-width:500px;max-height:80vh;overflow-y:auto;padding:20px;border-top:1px solid var(--border);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="margin:0;font-size:18px;color:var(--text);">🛒 Carrinho</h2>
                <button @@click="showCart = false" style="background:none;border:0;font-size:24px;cursor:pointer;color:var(--muted);">✕</button>
            </div>

            <template x-for="(item, idx) in itens" :key="item.id">
                <div style="display:flex;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);">
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;color:var(--text);" x-text="item.nome"></div>
                        <div style="font-size:12px;color:var(--muted);">R$ <span x-text="item.preco.toFixed(2)"></span></div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <button @@click="item.quantidade = Math.max(0.001, item.quantidade - 1); calc()" style="width:28px;height:28px;border:1px solid var(--border);border-radius:6px;background:var(--surface);cursor:pointer;color:var(--text);">−</button>
                        <input type="number" x-model="item.quantidade" @@input="calc()" step="0.001" min="0.001" style="width:60px;text-align:center;padding:4px;border:1px solid var(--border);border-radius:6px;font-size:13px;background:var(--surface);color:var(--text);">
                        <button @@click="item.quantidade += 1; calc()" style="width:28px;height:28px;border:1px solid var(--border);border-radius:6px;background:var(--surface);cursor:pointer;color:var(--text);">+</button>
                    </div>
                    <div style="font-weight:700;font-size:14px;width:90px;text-align:right;color:var(--text);" x-text="'R$ ' + (item.quantidade * item.preco).toFixed(2)"></div>
                    <button @@click="itens.splice(idx, 1); calc()" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:18px;">✕</button>
                </div>
            </template>

            <div x-show="itens.length === 0" style="text-align:center;padding:30px 0;color:var(--muted);">Carrinho vazio</div>

            <div x-show="itens.length > 0" style="margin-top:16px;">
                <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:900;margin-bottom:16px;color:var(--text);">
                    <span>Total</span><span x-text="'R$ ' + total.toFixed(2)"></span>
                </div>
                <div style="margin-bottom:12px;">
                    <input x-model="clienteNome" placeholder="Seu nome *" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;margin-bottom:6px;box-sizing:border-box;background:var(--surface);color:var(--text);">
                    <input x-model="clienteWhatsApp" placeholder="WhatsApp" style="width:100%;padding:10px;border:1px solid var(--border);border-radius:8px;box-sizing:border-box;background:var(--surface);color:var(--text);">
                </div>
                <button @@click="finalizar()" :disabled="!clienteNome || itens.length === 0"
                        style="width:100%;padding:14px;border:0;border-radius:12px;background:var(--primary-600);color:var(--on-primary, #fff);font-weight:700;font-size:16px;cursor:pointer;">
                    ✅ Finalizar Pedido
                </button>
            </div>
        </div>
    </div>

    <div x-show="pedidoId" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
        <div style="background:var(--surface);border-radius:20px;padding:30px;text-align:center;max-width:400px;width:90%;border:1px solid var(--border);">
            <div style="font-size:48px;margin-bottom:12px;">✅</div>
            <h2 style="margin:0 0 8px;font-size:20px;color:var(--text);">Pedido Confirmado!</h2>
            <p style="color:var(--muted);margin-bottom:4px;">Nº do pedido: <strong x-text="pedidoId" style="color:var(--text);"></strong></p>
            <p style="color:var(--muted);font-size:13px;">Pedido registrado e estoque atualizado.</p>
            <button @@click="pedidoId = null; showCart = false; itens = []; total = 0; $wire.$set('ultimoPedidoId', null)" style="margin-top:16px;padding:12px 32px;border:0;border-radius:10px;background:var(--primary-600);color:var(--on-primary, #fff);font-weight:700;cursor:pointer;">Continuar</button>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('lojaApp', () => ({
                itens: [], total: 0, showCart: false, clienteNome: '', clienteWhatsApp: '', pedidoId: null,
                init() {
                    const saved = localStorage.getItem('loja_carrinho');
                    if (saved) { try { this.itens = JSON.parse(saved); this.calc(); } catch(e) {} }
                    window.addEventListener('abrir-carrinho', () => { this.showCart = true; });
                },
                salvar() { localStorage.setItem('loja_carrinho', JSON.stringify(this.itens)); },
                calc() { this.total = this.itens.reduce((s, i) => s + (i.quantidade || 0) * i.preco, 0); this.salvar(); },
                adicionar(id, nome, preco, estoque) {
                    if (estoque <= 0) return;
                    const e = this.itens.find(i => i.id === id);
                    if (e) { e.quantidade += 1; } else { this.itens.push({ id, nome, preco, quantidade: 1 }); }
                    this.calc();
                },
                podeAdicionar(id, estoque) { return estoque > 0; },
                async finalizar() {
                    if (!this.clienteNome || this.itens.length === 0) return;
                    await this.$wire.set('clienteNome', this.clienteNome);
                    await this.$wire.set('clienteWhatsApp', this.clienteWhatsApp || '');
                    const r = await this.$wire.finalizar(this.itens.map(i => ({ id: i.id, quantidade: i.quantidade })));
                    this.pedidoId = r || 'OK';
                    this.itens = []; this.total = 0; this.clienteNome = ''; this.clienteWhatsApp = '';
                    localStorage.removeItem('loja_carrinho');
                }
            }));
        });
    </script>

    <style>
        * { box-sizing:border-box; }
        body { margin:0; background:var(--background); color:var(--text); }
        input:focus { outline:2px solid var(--primary-500); outline-offset:-1px; }
        button:disabled { opacity:0.5; cursor:not-allowed !important; }
        [x-cloak] { display:none!important; }
    </style>
</div>

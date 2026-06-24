<div>
    <script src="/js/html5-qrcode.min.js"></script>
    @if ($this->passo === 'inicio')
        @include('livewire.pdv.inicio')
    @endif
    @if ($this->passo === 'venda')
        @include('livewire.pdv.venda')
    @endif
    @if ($this->passo === 'pagamento')
        @include('livewire.pdv.pagamento')
    @endif
    @if ($this->passo === 'finalizada')
        @include('livewire.pdv.finalizada')
    @endif
</div>

<script>
function pdvApp() {
    return {
        showScanner: false, scanError: null, qr: null,
        showClientModal: false, showDiscountModal: false,
        currentTime: '', currentDate: '', searchTimer: null,
        isFullscreen: false, estoqueBaixoMsg: '', estoqueBaixoQtd: 0,
        init() {
            this.updateClock();
            setInterval(() => this.updateClock(), 1000);
            this.$nextTick(() => { if (this.$refs.searchInput) this.$refs.searchInput.focus(); });
            window.addEventListener('beforeunload', (e) => {
                if (this.$wire.get('passo') === 'venda' && this.$wire.get('carrinho',[]).length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
            });
            window.addEventListener('produto-adicionado', () => this.tocarSom());
        },
        tocarSom() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                // Tenta tocar MP3 se existir
                const a = new Audio('/sounds/barcode_beep_sound.mp3');
                a.volume = 0.4;
                a.play().catch(() => {
                    // Fallback: Web Audio API - bip realista de scanner
                    const o = ctx.createOscillator();
                    const g = ctx.createGain();
                    o.connect(g); g.connect(ctx.destination);
                    o.frequency.value = 2500;
                    o.type = 'square';
                    g.gain.value = 0.15;
                    o.start();
                    g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.06);
                    o.stop(ctx.currentTime + 0.06);
                    // Reverb curto pra dar o "click" do scanner
                    const o2 = ctx.createOscillator();
                    const g2 = ctx.createGain();
                    o2.connect(g2); g2.connect(ctx.destination);
                    o2.frequency.value = 1800;
                    o2.type = 'sine';
                    g2.gain.value = 0.08;
                    o2.start(ctx.currentTime + 0.01);
                    g2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.04);
                    o2.stop(ctx.currentTime + 0.04);
                });
            } catch(e) {}
        },
        toggleFullscreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen().catch(() => {});
            } else {
                document.documentElement.requestFullscreen().catch(() => {});
            }
        },
        updateClock() {
            const n = new Date();
            this.currentTime = n.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            this.currentDate = n.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },
        scheduleSearch(val) {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.$wire.set('buscaProduto', val), 300);
        },
        searchEnter() {
            const val = this.$refs.searchInput?.value;
            if (val && val.length > 0) this.$wire.call('adicionarProdutoPorCodigo', val);
        },
        numpad(val) {
            if (!this.$refs.searchInput) return;
            if (val === 'bs') this.$refs.searchInput.value = this.$refs.searchInput.value.slice(0, -1);
            else if (val === 'limpar') this.$refs.searchInput.value = '';
            else this.$refs.searchInput.value += val;
            this.$refs.searchInput.dispatchEvent(new Event('input'));
            this.$refs.searchInput.focus();
        },
        openScanner() {
            this.showScanner = true; this.scanError = null;
            this.$nextTick(() => {
                const el = document.getElementById('scanner-elem');
                if (!el || typeof Html5Qrcode === 'undefined') { this.scanError = 'Biblioteca não carregada'; return; }
                if (!window.isSecureContext) { this.scanError = 'Câmera exige HTTPS'; return; }
                this.qr = new Html5Qrcode('scanner-elem');
                this.qr.start({ facingMode: 'environment' }, { fps: 15, qrbox: { width: 250, height: 150 } },
                    (texto) => { this.closeScanner(); this.$refs.searchInput.value = texto; this.$wire.call('adicionarProdutoPorCodigo', texto); }
                ).catch(e => { this.scanError = e.message; });
            });
        },
        closeScanner() { if (this.qr) { this.qr.stop().catch(() => {}); this.qr = null; } this.showScanner = false; this.$nextTick(() => this.$refs.searchInput?.focus()); },
        cancelItem() { this.$wire.get('carrinho', []).then(cart => { if (cart.length > 0) this.$wire.call('removerItem', cart.length - 1); }); },
        showClient() { this.showClientModal = true; this.showDiscountModal = false; },
        showDiscount() { this.showDiscountModal = true; this.showClientModal = false; },
        handleKey(e) {
            if (e.key === 'Escape') { this.showScanner = false; this.showClientModal = false; this.showDiscountModal = false; }
            if (e.key === 'F2') { e.preventDefault(); this.$wire.call('novaVenda'); }
            if (e.key === 'F3') { e.preventDefault(); this.$nextTick(() => this.$refs.searchInput?.focus()); }
            if (e.key === 'F4') { e.preventDefault(); this.showDiscount(); }
            if (e.key === 'F5') { e.preventDefault(); this.$wire.call('irPagamento'); }
            if (e.key === 'F6') { e.preventDefault(); this.showClient(); }
            if (e.key === 'F8') { e.preventDefault(); this.$wire.call('abrirDashboard'); }
        }
    }
}
</script>

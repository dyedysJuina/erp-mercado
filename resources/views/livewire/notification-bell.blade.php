<div wire:poll.60s="atualizar"
     x-data="{
         count: @entangle('count'),
         sinal: @entangle('sinal'),
         init() {
             this.$watch('sinal', () => { this.tocarSom(); });
         },
         tocarSom() {
             try {
                 const a = new Audio('/sounds/notification.mp3');
                 a.volume = 0.5;
                 a.play().catch(() => this.tocarSomFallback());
             } catch(e) { this.tocarSomFallback(); }
         },
         tocarSomFallback() {
             try {
                 const ctx = new (window.AudioContext || window.webkitAudioContext)();
                 const o = ctx.createOscillator();
                 const g = ctx.createGain();
                 const o2 = ctx.createOscillator();
                 const g2 = ctx.createGain();
                 o.connect(g);
                 g.connect(ctx.destination);
                 o2.connect(g2);
                 g2.connect(ctx.destination);
                 o.frequency.value = 830;
                 o2.frequency.value = 1100;
                 o.type = 'sine';
                 o2.type = 'sine';
                 g.gain.value = 0.25;
                 g2.gain.value = 0.15;
                 o.start();
                 o2.start();
                 g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
                 g2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                 setTimeout(() => { o.stop(); o2.stop(); }, 300);
             } catch(e) {}
         }
     }"
     style="display:inline-block;">
    <a href="{{ route('notificacoes') }}" wire:navigate style="position:relative;color:var(--muted);font-size:18px;text-decoration:none;">
        <i class="fas fa-bell bell-icon"></i>
        @if ($count > 0)
            <span style="position:absolute;top:-6px;right:-8px;display:flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 4px;border-radius:999px;background:var(--danger);color:#fff;font-size:10px;font-weight:800;line-height:1;">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </a>
</div>

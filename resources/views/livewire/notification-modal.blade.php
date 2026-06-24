<div x-data="{
    aberto: @entangle('modalAberto'),
    sinal: @entangle('sinal'),
    init() {
        this.$watch('sinal', () => { this.aberto = true; });
    }
}">
    {{-- OVERLAY + MODAL --}}
    <div x-show="aberto" style="position:fixed;inset:0;z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;" x-cloak>
        {{-- Overlay escuro --}}
        <div style="position:absolute;inset:0;background:rgba(0,0,0,0.6);" x-on:click="aberto = false"></div>

        {{-- Modal --}}
        <div style="position:relative;background:var(--surface, #fff);border-radius:20px;border:1px solid var(--border, #e2e8f0);width:100%;max-width:480px;max-height:80vh;display:flex;flex-direction:column;box-shadow:0 25px 60px rgba(0,0,0,0.3);">
            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border, #e2e8f0);flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="width:36px;height:36px;border-radius:10px;background:color-mix(in srgb, var(--warning, #f59e0b) 12%, transparent);display:flex;align-items:center;justify-content:center;color:var(--warning, #f59e0b);font-size:16px;"><i class="fas fa-bell"></i></span>
                    <div>
                        <h3 style="margin:0;font-size:16px;font-weight:800;color:var(--text, #0f172a);">Notificacoes</h3>
                        <span style="font-size:11px;color:var(--muted, #94a3b8);">
                            @if ($totalNaoLidas > 0)
                                {{ $totalNaoLidas }} nao lida(s)
                            @else
                                Nenhuma pendente
                            @endif
                        </span>
                    </div>
                </div>
                <div style="display:flex;gap:6px;">
                    @if ($totalNaoLidas > 0)
                        <button wire:click="marcarTodas" style="padding:6px 12px;border:0;border-radius:6px;background:color-mix(in srgb, var(--primary-500, #6366f1) 10%, transparent);color:var(--primary-600, #4f46e5);cursor:pointer;font-size:11px;font-weight:700;"><i class="fas fa-check-double"></i> Todas</button>
                    @endif
                    <button wire:click="fechar" style="width:32px;height:32px;border:0;border-radius:6px;background:color-mix(in srgb, var(--text, #0f172a) 5%, transparent);color:var(--text, #0f172a);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;">&times;</button>
                </div>
            </div>

            {{-- Body --}}
            <div style="overflow-y:auto;flex:1;padding:8px 0;">
                @forelse ($naoLidas as $n)
                    <a href="{{ $n['link'] ?? '#' }}" wire:navigate @if(!$n['link']) wire:click="marcarLida({{ $n['id'] }})" @endif style="text-decoration:none;color:inherit;display:block;">
                        <div style="display:flex;align-items:start;gap:10px;padding:10px 20px;border-bottom:1px solid color-mix(in srgb, var(--border, #e2e8f0) 50%, transparent);cursor:pointer;" x-on:click="if('{{ $n['link'] }}') { $wire.marcarLida({{ $n['id'] }}); }">
                            <span style="width:8px;height:8px;border-radius:50%;background:var(--warning, #f59e0b);flex-shrink:0;margin-top:6px;"></span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;font-weight:700;color:var(--text, #0f172a);">{{ $n['titulo'] }}</div>
                                <div style="font-size:11px;color:var(--muted, #94a3b8);margin-top:2px;line-height:1.3;">{{ $n['mensagem'] }}</div>
                                <div style="font-size:9px;color:var(--muted, #94a3b8);margin-top:4px;">{{ \Carbon\Carbon::parse($n['created_at'])->format('d/m/Y H:i') }}</div>
                            </div>
                            <button wire:click.stop="marcarLida({{ $n['id'] }})" style="background:none;border:0;color:var(--muted, #94a3b8);cursor:pointer;font-size:14px;padding:4px;flex-shrink:0;" title="Marcar como lida"><i class="fas fa-check-circle"></i></button>
                        </div>
                    </a>
                @empty
                    <div style="text-align:center;padding:40px 20px;color:var(--muted, #94a3b8);">
                        <i class="fas fa-check-circle" style="font-size:36px;display:block;margin-bottom:10px;color:var(--success, #22c55e);opacity:0.4;"></i>
                        <p style="font-size:14px;font-weight:600;color:var(--text, #0f172a);margin:0 0 4px;">Tudo em dia!</p>
                        <p style="font-size:12px;margin:0;">Nenhuma notificacao pendente.</p>
                    </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div style="padding:12px 20px;border-top:1px solid var(--border, #e2e8f0);text-align:center;flex-shrink:0;">
                <a href="{{ route('notificacoes') }}" wire:navigate wire:click="fechar" style="font-size:12px;color:var(--primary-600, #4f46e5);font-weight:600;text-decoration:none;">Ver todas as notificacoes &rarr;</a>
            </div>
        </div>
    </div>
</div>

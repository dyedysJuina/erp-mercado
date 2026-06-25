<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    @php $p = $this->pedido(); $item = $this->item; $prog = $this->progresso(); @endphp
    <div style="max-width:480px;margin:0 auto;background:color-mix(in srgb,var(--text)4%,transparent);min-height:100vh;display:flex;flex-direction:column;">
        {{-- HEADER --}}
        <header style="background:var(--header-bg);color:var(--text);border-bottom:1px solid var(--header-border);padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10;">
            <a href="/separacao" wire:navigate style="color:var(--text);text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:4px;">
                <i class="fas fa-chevron-left"></i> Voltar
            </a>
            <span style="font-weight:800;font-size:14px;color:var(--text);">Pedido #{{ $this->pedidoId }}</span>
            @if ($p && $p->cliente && $p->cliente->whatsapp)
                <a href="https://wa.me/55{{ preg_replace('/\D/', '', $p->cliente->whatsapp) }}" target="_blank" style="background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;text-decoration:none;display:flex;align-items:center;gap:4px;">
                    <i class="fab fa-whatsapp"></i> Cliente
                </a>
            @else
                <span></span>
            @endif
        </header>

        @if (!$item)
            {{-- RESUME / FINAL --}}
            <div style="flex:1;padding:16px;display:flex;flex-direction:column;justify-content:space-between;">
                <div>
                    <div style="text-align:center;margin-bottom:16px;">
                        <i class="fas fa-check-circle" style="font-size:40px;color:var(--success);margin-bottom:8px;"></i>
                        <h2 style="font-size:18px;font-weight:800;color:var(--text);margin:0;">Separacao Concluida</h2>
                        <p style="font-size:13px;color:var(--muted);">Pedido #{{ $this->pedidoId }} — {{ $p?->cliente?->nome ?? '' }}</p>
                    </div>

                    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:12px;margin-bottom:14px;">
                        @forelse ($this->processados as $pr)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid color-mix(in srgb,var(--text)6%,transparent);font-size:12px;">
                                <div>
                                    <span style="font-weight:600;font-size:11px;text-transform:uppercase;">{{ $pr['nome'] }}</span>
                                    <span style="font-size:10px;color:var(--muted);display:block;">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }} un</span>
                                </div>
                                <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:6px;{{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);' : ($pr['qtd_separada'] > 0 ? 'background:color-mix(in srgb,var(--warning)12%,transparent);color:var(--warning);' : 'background:color-mix(in srgb,var(--danger)12%,transparent);color:var(--danger);') }}">
                                    {{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'OK' : ($pr['qtd_separada'] > 0 ? 'PARCIAL' : 'FALTOU') }}
                                </span>
                            </div>
                        @empty
                            <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">Nenhum item processado.</div>
                        @endforelse
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
                        <div style="background:color-mix(in srgb,var(--primary-600)8%,transparent);border:1px solid color-mix(in srgb,var(--primary-600)12%,transparent);border-radius:10px;padding:12px;text-align:center;">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--muted);">Com Obs.</span>
                            <span style="display:block;font-size:20px;font-weight:900;color:var(--text);">{{ count(array_filter($this->processados, fn($i) => !empty($i['observacao']) && $i['observacao'] !== 'OK')) }}</span>
                        </div>
                        <div style="background:color-mix(in srgb,var(--warning)8%,transparent);border:1px solid color-mix(in srgb,var(--warning)12%,transparent);border-radius:10px;padding:12px;text-align:center;">
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--muted);">Substituidos</span>
                            <span style="display:block;font-size:20px;font-weight:900;color:var(--text);">0</span>
                        </div>
                    </div>
                </div>

                <div>
                    <button wire:click="finalizarSeparacao" style="width:100%;padding:14px;border:0;border-radius:10px;background:var(--success);color:var(--on-success);font-weight:800;font-size:15px;cursor:pointer;">
                        <i class="fas fa-check-double" style="margin-right:6px;"></i> Confirmar e Finalizar
                    </button>
                    <a href="/separacao" wire:navigate style="display:block;text-align:center;margin-top:10px;font-size:13px;color:var(--muted);text-decoration:none;">Voltar para lista</a>
                </div>
            </div>
        @else
            {{-- ITEM CURRENT --}}
            <div style="flex:1;padding:16px;display:flex;flex-direction:column;">
                {{-- Progress --}}
                <div style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:var(--muted);margin-bottom:4px;">
                        <span>PROGRESSO DO PEDIDO <span id="txt-progresso-itens">{{ $prog['feitos'] }}/{{ $prog['total'] }}</span></span>
                    </div>
                    <div style="height:8px;border-radius:4px;background:color-mix(in srgb,var(--text)8%,transparent);overflow:hidden;">
                        <div style="height:100%;border-radius:4px;width:{{ $prog['pct'] }}%;background:var(--success);transition:width 0.3s;"></div>
                    </div>
                </div>

                {{-- ITEM CARD --}}
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px;margin-bottom:14px;">
                    <div style="display:flex;align-items:center;gap:8px;background:color-mix(in srgb,var(--warning)10%,transparent);color:var(--warning);padding:6px 10px;border-radius:8px;font-size:11px;font-weight:700;margin-bottom:12px;">
                        <i class="fas fa-map-pin"></i> <span id="prod-localizacao">Corredor —</span>
                    </div>

                    <h2 style="font-size:16px;font-weight:800;color:var(--text);margin:0 0 4px;text-transform:uppercase;">{{ $item['nome'] }}</h2>
                    <p style="font-size:11px;color:var(--muted);margin:0 0 12px;">SKU: {{ $item['sku'] ?: '—' }}</p>

                    <div style="display:flex;align-items:center;justify-content:space-between;background:color-mix(in srgb,var(--text)4%,transparent);border:1px solid var(--border);border-radius:10px;padding:12px;margin-bottom:12px;">
                        <div>
                            <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Solicitado</span>
                            <div style="font-size:20px;font-weight:900;color:var(--text);">{{ number_format($item['qtd_pedido'], 0, ',', '.') }} un</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <button wire:click="decrementar" style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:18px;font-weight:700;display:flex;align-items:center;justify-content:center;">−</button>
                            <input type="number" wire:model.blur="itens.{{ $this->itemAtual }}.qtd_separada" style="width:50px;height:36px;text-align:center;border:1px solid var(--primary-600);border-radius:8px;font-size:16px;font-weight:800;outline:none;">
                            <button wire:click="incrementar" style="width:36px;height:36px;border-radius:8px;border:1px solid var(--border);background:var(--surface);cursor:pointer;font-size:18px;font-weight:700;display:flex;align-items:center;justify-content:center;">+</button>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:12px;">
                        <button wire:click="definirStatus('ok')" style="padding:10px;border:2px solid var(--success);border-radius:8px;background:color-mix(in srgb,var(--success)8%,transparent);color:var(--success);font-weight:700;font-size:11px;cursor:pointer;text-align:center;">
                            <i class="fas fa-check-circle" style="display:block;font-size:16px;margin-bottom:2px;"></i> OK
                        </button>
                        <button wire:click="definirStatus('parcial')" style="padding:10px;border:2px solid var(--warning);border-radius:8px;background:color-mix(in srgb,var(--warning)8%,transparent);color:var(--warning);font-weight:700;font-size:11px;cursor:pointer;text-align:center;">
                            <i class="fas fa-exclamation-triangle" style="display:block;font-size:16px;margin-bottom:2px;"></i> Parcial
                        </button>
                        <button wire:click="definirStatus('faltou')" style="padding:10px;border:2px solid var(--danger);border-radius:8px;background:color-mix(in srgb,var(--danger)8%,transparent);color:var(--danger);font-weight:700;font-size:11px;cursor:pointer;text-align:center;">
                            <i class="fas fa-times-circle" style="display:block;font-size:16px;margin-bottom:2px;"></i> Faltou
                        </button>
                    </div>

                    <div style="margin-bottom:4px;">
                        <label style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:block;margin-bottom:4px;">Observacao</label>
                        <input wire:model="itens.{{ $this->itemAtual }}.observacao" type="text" placeholder="Motivo..." style="width:100%;padding:8px 10px;border:1px solid var(--border);border-radius:8px;font-size:12px;outline:none;">
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div style="display:flex;gap:8px;margin-top:auto;">
                    <button wire:click="pularItem" style="flex:1;padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface);cursor:pointer;font-weight:700;font-size:13px;color:var(--text);">Pular</button>
                    <button wire:click="confirmarProximo" style="flex:1;padding:12px;border:0;border-radius:10px;background:var(--success);color:var(--on-success);cursor:pointer;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;gap:4px;">
                        Confirmar <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                {{-- PROCESSADOS --}}
                @if (count($this->processados) > 0)
                    <div style="margin-top:14px;border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                        <div style="background:color-mix(in srgb,var(--text)4%,transparent);padding:8px 12px;font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;display:flex;justify-content:space-between;">
                            <span>Itens processados</span>
                            <span style="background:color-mix(in srgb,var(--text)8%,transparent);padding:1px 6px;border-radius:4px;">{{ count($this->processados) }}</span>
                        </div>
                        @foreach ($this->processados as $pr)
                            <div style="display:flex;justify-content:space-between;padding:6px 12px;border-top:1px solid color-mix(in srgb,var(--text)6%,transparent);font-size:11px;">
                                <span style="font-weight:600;text-transform:uppercase;">{{ $pr['nome'] }}</span>
                                <span style="color:{{ $pr['qtd_separada'] >= $pr['qtd_pedido'] ? 'var(--success)' : ($pr['qtd_separada'] > 0 ? 'var(--warning)' : 'var(--danger)') }};">{{ $pr['qtd_separada'] }}/{{ $pr['qtd_pedido'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

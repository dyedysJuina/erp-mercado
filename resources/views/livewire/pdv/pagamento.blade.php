<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px;background:#0f172a;">
    <section style="max-width:920px;width:100%;padding:20px 24px;background:#fff;border-radius:20px;">
        {{-- Total no topo --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;flex-wrap:wrap;">
            <span style="color:var(--success);font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:1px;">Pagamento</span>
            <h2 style="font-size:32px;font-weight:900;color:#0f172a;margin:0;">R$ {{ number_format($this->total, 2, ',', '.') }}</h2>
            <span style="color:#64748b;font-size:12px;">Subtotal: R$ {{ number_format($this->subtotal, 2, ',', '.') }}{{ $this->decimal($this->desconto) > 0 ? ' &middot; Desconto: -R$ '.number_format($this->decimal($this->desconto), 2, ',', '.') : '' }}{{ $this->decimal($this->acrescimo) > 0 ? ' &middot; Acrescimo: +R$ '.number_format($this->decimal($this->acrescimo), 2, ',', '.') : '' }}</span>
        </div>

        {{-- Cliente info --}}
        @php $cliInfo = $this->dadosClientePdv; @endphp
        @if ($cliInfo)
            <div style="display:flex;gap:8px;margin-bottom:14px;padding:8px 12px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;align-items:center;flex-wrap:wrap;font-size:11px;">
                <span style="font-weight:700;color:var(--success);"><i class="fas fa-user"></i> {{ $cliInfo['nome'] }}</span>
                <span style="color:#475569;">Ticket: R$ {{ number_format($cliInfo['ticket_medio'], 2, ',', '.') }}</span>
                <span style="color:#475569;">Compras: {{ $cliInfo['total_compras'] }}</span>
                @if ($cliInfo['dias_ultima'] !== null)
                    <span style="color:#475569;">Ultima: {{ $cliInfo['ultima_compra'] }} ({{ $cliInfo['dias_ultima'] }} dias)</span>
                @endif
                <span style="font-weight:700;text-transform:uppercase;font-size:10px;padding:2px 8px;border-radius:4px;{{ $cliInfo['classificacao'] === 'top' ? 'background:#fef3c7;color:#d97706;' : ($cliInfo['classificacao'] === 'medio' ? 'background:#dbeafe;color:#2563eb;' : 'background:#f1f5f9;color:#64748b;') }}">{{ $cliInfo['classificacao'] }}</span>
                @if (!empty($cliInfo['favoritos']))
                    <span style="color:#94a3b8;">| Mais compra: {{ implode(', ', array_column($cliInfo['favoritos'], 'nome')) }}</span>
                @endif
                <button wire:click="removerCliente" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:12px;margin-left:auto;">&times;</button>
            </div>
        @else
            <div style="margin-bottom:14px;">
                <input wire:model.blur="buscaCliente" type="search" placeholder="CPF do cliente (para identificar)..." style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:#0f172a;">
                @if (count($this->clientes) > 0)
                    <div style="margin-top:4px;max-height:150px;overflow-y:auto;border:1px solid var(--border);border-radius:6px;">
                        @foreach ($this->clientes as $client)
                            <button wire:click="selecionarCliente({{ $client['id'] }})" style="display:block;width:100%;text-align:left;padding:6px 10px;border:0;border-bottom:1px solid var(--border);background:none;cursor:pointer;color:#0f172a;font-size:12px;">
                                <strong>{{ $client['nome'] }}</strong>
                                <small style="color:#64748b;">{{ $client['cpf'] ?? '' }}</small>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
            {{-- COLUNA ESQUERDA: pagamentos + dinheiro --}}
            <div>
                @if (count($this->pagamentos) > 0)
                    <div style="background:#f8fafc;border-radius:12px;padding:10px 12px;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px;">Pagamentos</div>
                        @foreach ($this->pagamentos as $idx => $pg)
                            @php $isDin = $pg['tipo'] === 'dinheiro'; $isPix = $pg['tipo'] === 'pix'; @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-radius:8px;margin-bottom:4px;background:{{ $isDin ? '#f0fdf4' : ($isPix ? '#f0f9ff' : '#faf5ff') }};">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-size:16px;">
                                        @if ($isDin) <i class="fas fa-money-bill-wave" style="color:var(--success);"></i>
                                        @elseif ($isPix) <i class="fa-brands fa-pix" style="color:#00b4d8;"></i>
                                        @else <i class="fas fa-credit-card" style="color:#8b5cf6;"></i> @endif
                                    </span>
                                    <span style="font-weight:600;font-size:13px;color:#0f172a;">{{ $pg['nome'] }}</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-weight:700;font-size:14px;color:#0f172a;">R$ {{ number_format($pg['valor'], 2, ',', '.') }}</span>
                                    <button wire:click="removerPagamento({{ $idx }})" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:14px;padding:4px;">&times;</button>
                                </div>
                            </div>
                        @endforeach
                        <div style="display:flex;justify-content:space-between;padding:4px 4px 0;font-size:13px;font-weight:700;color:#0f172a;border-top:2px solid #0f172a;margin-top:4px;">
                            <span>Total pago</span>
                            <span style="color:var(--success);">R$ {{ number_format($this->totalPago, 2, ',', '.') }}</span>
                        </div>
                        @if ($this->restantePagar > 0.01)
                            <div style="text-align:right;font-size:12px;color:var(--warning);font-weight:600;margin-top:2px;">Restante: R$ {{ number_format($this->restantePagar, 2, ',', '.') }}</div>
                        @endif
                    </div>
                @endif

                @php $formaDinheiroSel = collect($this->pagamentos)->where('tipo', 'dinheiro')->isNotEmpty(); $temPix = collect($this->pagamentos)->where('tipo', 'pix')->isNotEmpty(); @endphp
                @if ($formaDinheiroSel && !$temPix)
                    <div style="margin-top:12px;">
                        <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Valor recebido em dinheiro</label>
                        <div style="display:flex;align-items:center;border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                            <span style="padding:0 14px;color:#64748b;font-weight:700;font-size:14px;">R$</span>
                            <input wire:model.live.debounce.200ms="valorRecebido" type="text" placeholder="{{ number_format($this->restantePagar > 0 ? $this->restantePagar : 0, 2, ',', '.') }}" style="border:0;flex:1;height:40px;outline:none;padding:0 14px;font-size:16px;font-weight:700;color:#0f172a;">
                        </div>
                    </div>
                    @php $trocoCalc = $this->trocoCalculado; @endphp
                    @if ($trocoCalc > 0)
                        <div style="display:flex;justify-content:space-between;padding:8px 10px;border-radius:10px;background:#f0fdf4;color:var(--success);font-weight:700;font-size:14px;margin-top:8px;border:2px solid #bbf7d0;">
                            <span><i class="fas fa-arrow-left"></i> Troco</span>
                            <span>R$ {{ number_format($trocoCalc, 2, ',', '.') }}</span>
                        </div>
                    @endif
                @endif
            </div>

            {{-- COLUNA DIREITA: formas de pagamento + ações --}}
            <div>
                @php $tiposSelecionados = collect($this->pagamentos)->pluck('tipo')->toArray(); @endphp
                <p style="font-size:11px;font-weight:600;color:#64748b;margin:0 0 6px;">{{ count($this->pagamentos) > 0 ? 'Clique para trocar a forma' : 'Escolha a forma de pagamento' }}</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:12px;">
                    @foreach ($this->formasPagamento as $fp)
                        @php $sel = in_array($fp['tipo'], $tiposSelecionados); @endphp
                        <button type="button" wire:click="adicionarPagamento({{ $fp['id'] }})" 
                                style="padding:10px 6px;border-radius:10px;cursor:pointer;text-align:center;transition:0.15s;{{ $sel ? 'background:var(--success);color:#fff;border:2px solid var(--success);font-weight:700;' : 'background:#fff;color:var(--text);border:1px solid var(--border);' }}"
                                @if($sel) disabled @endif>
                            <div style="font-size:20px;margin-bottom:2px;">
                                @if ($fp['tipo'] === 'dinheiro') <i class="fas fa-money-bill-wave" style="color:{{ $sel ? '#fff' : 'var(--success)' }};"></i>
                                @elseif ($fp['tipo'] === 'pix') <i class="fa-brands fa-pix" style="color:{{ $sel ? '#fff' : '#00b4d8' }};"></i>
                                @else <i class="fas fa-credit-card" style="color:{{ $sel ? '#fff' : '#8b5cf6' }};"></i> @endif
                            </div>
                            <span style="font-size:11px;font-weight:600;">{{ $fp['nome'] }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($temPix && $this->restantePagar <= 0.01)
                    <div style="text-align:center;padding:12px;margin-bottom:12px;background:#f0f9ff;border-radius:12px;border:1px dashed #38bdf8;">
                        <div style="font-size:36px;margin-bottom:4px;color:#00b4d8;"><i class="fa-brands fa-pix"></i></div>
                        <p style="font-size:12px;font-weight:700;color:#0f172a;margin:0 0 2px;">PIX Gerado</p>
                        <p style="font-size:11px;color:#64748b;margin:0;">QR Code na proxima versao</p>
                        <div style="width:120px;height:120px;margin:8px auto 0;background:#fff;border-radius:12px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">
                            <i class="fas fa-qrcode" style="font-size:40px;opacity:0.3;"></i>
                        </div>
                    </div>
                @endif

                @error('forma_pagamento_id')<div style="color:var(--danger);font-size:12px;margin-bottom:6px;">{{ $message }}</div>@enderror
                @error('caixa')<div style="padding:8px;border-radius:8px;background:#fef2f2;color:var(--danger);font-size:12px;margin-bottom:8px;">{{ $message }}</div>@enderror

                <div style="display:flex;gap:8px;">
                    <button wire:click="voltarVenda" style="flex:1;padding:12px;border:1px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:700;color:#475569;font-size:14px;">Voltar</button>
                    <button wire:click="finalizar" wire:loading.attr="disabled" style="flex:1;padding:12px;border:0;border-radius:10px;background:var(--success);color:#fff;cursor:pointer;font-weight:800;font-size:14px;" @disabled($this->restantePagar > 0.01 || count($this->pagamentos) === 0)>
                        <span wire:loading.remove><i class="fas fa-check-circle"></i> Finalizar</span>
                        <span wire:loading>Finalizando...</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
</div>
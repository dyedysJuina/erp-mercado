<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">
    <section style="max-width:520px;width:100%;padding:32px;background:var(--surface);border:1px solid var(--border);border-radius:20px;text-align:center;">

        @if ($this->caixaAberturaId)
            {{-- GESTÃO DO CAIXA --}}
            <div style="width:60px;height:60px;margin:0 auto 16px;border-radius:18px;background:color-mix(in srgb, var(--primary-600) 10%, transparent);color:var(--primary-600);display:grid;place-items:center;font-size:32px;">
                <i class="fas fa-cash-register"></i>
            </div>
            <h2 style="margin:0;font-size:20px;color:var(--text);">Gestão do Caixa</h2>
            <p style="color:var(--muted);font-size:13px;margin:4px 0 20px;">Gerencie o caixa, veja o dashboard ou registre devoluções.</p>

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                <button wire:click="$set('passo', 'venda')" style="width:100%;padding:14px;border:0;border-radius:12px;background:var(--primary-600);color:#fff;font-weight:800;font-size:14px;cursor:pointer;">
                    <i class="fas fa-cart-plus"></i> Voltar à Venda
                </button>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <button wire:click="abrirDashboard" style="padding:14px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-weight:700;font-size:12px;cursor:pointer;">
                        <i class="fas fa-chart-bar"></i> Dashboard
                    </button>
                    <button wire:click="abrirDevolucao" style="padding:14px;border:1px solid var(--border);border-radius:10px;background:var(--surface);color:var(--text);font-weight:700;font-size:12px;cursor:pointer;">
                        <i class="fas fa-undo-alt"></i> Devolução
                    </button>
                </div>
                <button wire:click="$set('closeModalOpen', true)" style="width:100%;padding:12px;border:1px solid var(--danger);border-radius:10px;background:color-mix(in srgb, var(--danger) 6%, transparent);color:var(--danger);font-weight:800;font-size:13px;cursor:pointer;">
                    <i class="fas fa-cash-register"></i> Fechar Caixa
                </button>
            </div>

        @else
            {{-- ABRIR CAIXA --}}
            <div style="width:60px;height:60px;margin:0 auto 20px;border-radius:18px;background:color-mix(in srgb, var(--primary-500) 12%, transparent);color:var(--primary-600);display:grid;place-items:center;font-size:32px;">
                <i class="fas fa-cash-register"></i>
            </div>
            <h2 style="margin:0;font-size:22px;color:var(--text);">Abrir o caixa</h2>
            <p style="color:var(--muted);font-size:13px;margin:6px 0 24px;">Escolha a loja e informe o fundo inicial.</p>

            <div style="text-align:left;margin-bottom:15px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:var(--text);">Loja</label>
                <select wire:model="loja_id" style="width:100%;height:44px;padding:0 14px;border:1px solid var(--border);border-radius:10px;font-size:14px;background:color-mix(in srgb, var(--surface) 97%, var(--text));color:var(--text);outline:none;">
                    <option value="">Selecione...</option>
                    @foreach ($this->lojas as $store)
                        <option value="{{ $store['id'] }}">{{ $store['nome'] }}</option>
                    @endforeach
                </select>
                @error('loja_id')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="text-align:left;margin-bottom:15px;">
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;color:var(--text);">Fundo de caixa</label>
                <div style="display:flex;align-items:center;border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                    <span style="padding:0 12px;color:var(--muted);font-weight:700;font-size:13px;">R$</span>
                    <input wire:model.blur="valorAbertura" type="text" placeholder="0,00" style="border:0;flex:1;height:44px;outline:none;padding:0 12px;background:transparent;color:var(--text);font-size:14px;">
                </div>
                @error('valorAbertura')<div style="color:var(--danger);font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <button wire:click="iniciarVenda" wire:loading.attr="disabled" style="width:100%;padding:14px;border:0;border-radius:12px;background:var(--primary-600);color:var(--on-primary,#fff);font-weight:800;font-size:15px;cursor:pointer;margin-top:8px;">
                <span wire:loading.remove>🔓 Abrir caixa e iniciar</span>
                <span wire:loading>Abrindo...</span>
            </button>

            @if (count($this->historico) > 0)
                <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);text-align:left;">
                    <h4 style="margin:0 0 10px;font-size:13px;color:var(--text);">Últimas vendas</h4>
                    @foreach ($this->historico as $sale)
                        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;color:var(--text);">
                            <span><strong>#{{ $sale['id'] }}</strong> · {{ $sale['status'] }}</span>
                            <span style="font-weight:700;">R$ {{ number_format((float) $sale['total'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </section>

    {{-- MODAL FECHAMENTO --}}
    <div x-show="$wire.closeModalOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:20px;" @click.self="$wire.set('closeModalOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:20px;padding:24px;max-width:480px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 style="margin:0 0 16px;font-size:17px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-cash-register" style="color:var(--primary-600);"></i> Fechamento de Caixa
            </h3>

            @php $df = $this->dadosFechamento; @endphp

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                    <span style="color:#64748b;">Abertura</span><span style="font-weight:700;color:#0f172a;">R$ {{ number_format($df['abertura'], 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                    <span style="color:#64748b;">Vendas</span><span style="font-weight:700;color:var(--success);">R$ {{ number_format($df['vendas'], 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                    <span style="color:#64748b;">Suprimentos</span><span style="font-weight:700;color:var(--primary-600);">R$ {{ number_format($df['suprimentos'], 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border);font-size:13px;">
                    <span style="color:#64748b;">Sangrias</span><span style="font-weight:700;color:var(--warning);">- R$ {{ number_format($df['sangrias'], 2, ',', '.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:2px solid #0f172a;font-size:14px;font-weight:900;color:#0f172a;">
                    <span>Valor Esperado</span><span>R$ {{ number_format($df['esperado'], 2, ',', '.') }}</span>
                </div>
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Valor Encontrado no Caixa *</label>
                <input wire:model.live="closeValorEncontrado" type="text" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:16px;font-weight:700;color:#0f172a;">
            </div>

            @if ($df['diferenca'] !== null)
                <div style="padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;font-weight:700;display:flex;justify-content:space-between;{{ $df['diferenca'] >= 0 ? 'background:#f0fdf4;color:var(--success);' : 'background:#fef2f2;color:var(--danger);' }}">
                    <span>{{ $df['diferenca'] >= 0 ? '📍 Sobra' : '📍 Falta' }}</span>
                    <span>R$ {{ number_format(abs($df['diferenca']), 2, ',', '.') }}</span>
                </div>
            @endif

            <div style="display:flex;gap:8px;">
                <button type="button" wire:click="$set('closeModalOpen', false)" style="flex:1;padding:12px;border:1px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Cancelar</button>
                <button type="button" wire:click="fecharCaixa" style="flex:1;padding:12px;border:0;border-radius:10px;background:var(--danger);color:#fff;cursor:pointer;font-weight:800;" wire:loading.attr="disabled">
                    <span wire:loading.remove>🔒 Fechar Caixa</span>
                    <span wire:loading>Fechando...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- DASHBOARD MODAL --}}
    <div wire:poll.30s x-data="{ time: new Date().toLocaleTimeString('pt-BR') }" x-init="setInterval(() => time = new Date().toLocaleTimeString('pt-BR'), 30000)" x-show="$wire.dashboardOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:20px;" @click.self="$wire.set('dashboardOpen', false)" @keydown.escape.window="$wire.set('dashboardOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:20px;padding:24px;max-width:640px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <div>
                    <h3 style="margin:0;font-size:18px;color:#0f172a;display:flex;align-items:center;gap:8px;"><i class="fas fa-chart-bar" style="color:var(--primary-600);"></i> Dashboard do PDV</h3>
                    <span style="font-size:10px;color:#94a3b8;" x-text="'Última atualização: ' + time"></span>
                </div>
                <button type="button" wire:click="$set('dashboardOpen', false)" style="background:none;border:0;font-size:20px;color:#94a3b8;cursor:pointer;">&times;</button>
            </div>

            @php $vh = $this->dashboardVendasHoje; $tm = $this->dashboardTicketMedio; @endphp
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;">
                <div style="padding:14px;border-radius:12px;background:color-mix(in srgb, var(--primary-500) 8%, transparent);text-align:center;">
                    <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Faturamento</span>
                    <div style="font-size:20px;font-weight:900;color:var(--text);margin-top:4px;">R$ {{ number_format($vh['total'], 2, ',', '.') }}</div>
                </div>
                <div style="padding:14px;border-radius:12px;background:color-mix(in srgb, var(--success) 8%, transparent);text-align:center;">
                    <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Vendas</span>
                    <div style="font-size:20px;font-weight:900;color:var(--text);margin-top:4px;">{{ $vh['qtd'] }}</div>
                </div>
                <div style="padding:14px;border-radius:12px;background:color-mix(in srgb, #6366f1 8%, transparent);text-align:center;">
                    <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Ticket Médio</span>
                    <div style="font-size:20px;font-weight:900;color:var(--text);margin-top:4px;">R$ {{ number_format($tm, 2, ',', '.') }}</div>
                </div>
                <div style="padding:14px;border-radius:12px;background:color-mix(in srgb, var(--danger) 8%, transparent);text-align:center;">
                    <span style="font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;">Canceladas</span>
                    <div style="font-size:20px;font-weight:900;color:var(--danger);margin-top:4px;">{{ $vh['canceladas'] }}</div>
                </div>
            </div>

            {{-- Vendas por hora --}}
            @php $porHora = $this->dashboardVendasPorHora; $maxHora = max(array_column($porHora, 'total') ?: [1]); @endphp
            <div style="margin-bottom:10px;">
                <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px;">Vendas por Hora</div>
                <div style="display:flex;align-items:end;gap:4px;height:100px;">
                    @foreach ($porHora as $h)
                        @php $altH = $maxHora > 0 ? max(4, ($h['total'] / $maxHora) * 80) : 4; @endphp
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                            <div style="width:100%;border-radius:4px 4px 0 0;background:var(--primary-500);height:{{ $altH }}px;min-height:4px;" title="R$ {{ number_format($h['total'], 2, ',', '.') }}"></div>
                            <span style="font-size:7px;color:var(--muted);margin-top:2px;">{{ $h['hora'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Movimentos do caixa --}}
            @if ($this->caixaAberturaId)
                <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:12px;">
                    <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:6px;">Movimentos do Caixa</div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                        <div style="padding:10px;border-radius:8px;background:color-mix(in srgb, var(--warning) 8%, transparent);text-align:center;">
                            <span style="font-size:9px;font-weight:600;color:var(--muted);">Sangrias</span>
                            <div style="font-size:15px;font-weight:700;color:var(--warning);">R$ {{ number_format($this->totalSangrias, 2, ',', '.') }}</div>
                        </div>
                        <div style="padding:10px;border-radius:8px;background:color-mix(in srgb, var(--primary-600) 8%, transparent);text-align:center;">
                            <span style="font-size:9px;font-weight:600;color:var(--muted);">Suprimentos</span>
                            <div style="font-size:15px;font-weight:700;color:var(--primary-600);">R$ {{ number_format($this->totalSuprimentos, 2, ',', '.') }}</div>
                        </div>
                        <div style="padding:10px;border-radius:8px;background:color-mix(in srgb, var(--success) 8%, transparent);text-align:center;">
                            <span style="font-size:9px;font-weight:600;color:var(--muted);">Saldo</span>
                            <div style="font-size:15px;font-weight:700;color:var(--success);">R$ {{ number_format($this->totalSangrias + $this->totalSuprimentos > 0 ? ($this->dashboardVendasHoje['total'] + $this->totalSuprimentos - $this->totalSangrias) : $this->dashboardVendasHoje['total'], 2, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <button type="button" wire:click="$set('dashboardOpen', false)" style="width:100%;margin-top:16px;padding:10px;border:0;border-radius:8px;background:var(--primary-600);color:#fff;font-weight:700;cursor:pointer;">Fechar</button>
        </div>
    </div>

    {{-- DEVOLUÇÃO MODAL --}}
    <div x-show="$wire.devModalOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:20px;" @click.self="$wire.set('devModalOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:20px;padding:24px;max-width:520px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <h3 style="margin:0 0 16px;font-size:17px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-undo-alt" style="color:var(--primary-600);"></i> Devolução / Troca
            </h3>

            {{-- Buscar venda --}}
            @if (!$this->devVenda)
                <div style="margin-bottom:14px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Nº da Venda</label>
                    <div style="display:flex;gap:6px;">
                        <input wire:model="devBuscaVendaId" type="number" placeholder="Ex: 1" style="flex:1;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                        <button wire:click="buscarVendaDevolucao" style="padding:10px 20px;border:0;border-radius:8px;background:var(--primary-600);color:#fff;font-weight:700;cursor:pointer;">Buscar</button>
                    </div>
                </div>
            @endif

            @if ($this->devMensagem)
                <div style="padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:13px;font-weight:600;{{ str_contains($this->devMensagem, 'sucesso') ? 'background:#f0fdf4;color:var(--success);' : 'background:#fef2f2;color:var(--danger);' }}">
                    {{ $this->devMensagem }}
                    @if (str_contains($this->devMensagem, 'sucesso'))
                        <button wire:click="abrirDevolucao" style="display:block;margin-top:8px;padding:6px 14px;border:1px solid var(--success);border-radius:6px;background:#fff;color:var(--success);cursor:pointer;font-weight:700;">Nova Devolução</button>
                    @endif
                </div>
            @endif

            {{-- Itens da venda --}}
            @if ($this->devVenda)
                <div style="margin-bottom:12px;">
                    <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:8px;">Venda #{{ $this->devVenda['id'] }} — R$ {{ number_format($this->devVenda['total'] ?? 0, 2, ',', '.') }}</div>
                    @foreach ($this->devVenda['itens'] as $item)
                        @php $selected = in_array($item['id'], $this->devItensSelecionados); @endphp
                        <label style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;margin-bottom:4px;cursor:pointer;{{ $selected ? 'background:#f0fdf4;border:1px solid #bbf7d0;' : 'background:#f8fafc;border:1px solid var(--border);' }}">
                            <input type="checkbox" wire:click="alternarItemDevolucao({{ $item['id'] }})" {{ $selected ? 'checked' : '' }} style="width:18px;height:18px;">
                            <div style="flex:1;font-size:13px;color:#0f172a;">
                                <span style="font-weight:600;">{{ $item['variacao']['nome_completo'] ?? '#' . $item['produto_variacao_id'] }}</span>
                                <span style="display:block;font-size:11px;color:#64748b;">{{ $item['quantidade'] }} x R$ {{ number_format($item['preco_unitario'], 2, ',', '.') }}</span>
                            </div>
                            <span style="font-weight:700;color:#0f172a;">R$ {{ number_format($item['quantidade'] * $item['preco_unitario'], 2, ',', '.') }}</span>
                        </label>
                    @endforeach
                </div>

                @php
                    $totalDev = collect($this->devVenda['itens'])->whereIn('id', $this->devItensSelecionados)->sum(fn($i) => $i['quantidade'] * $i['preco_unitario']);
                @endphp

                <div style="display:flex;justify-content:space-between;padding:8px 0;border-top:2px solid #0f172a;font-size:14px;font-weight:900;color:#0f172a;margin-bottom:12px;">
                    <span>Total a devolver</span><span>R$ {{ number_format($totalDev, 2, ',', '.') }}</span>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Motivo *</label>
                    <select wire:model="devMotivo" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                        <option value="">Selecione...</option>
                        <option value="Produto danificado">Produto danificado</option>
                        <option value="Produto errado">Produto errado</option>
                        <option value="Produto vencido">Produto vencido</option>
                        <option value="Arrependimento">Arrependimento</option>
                        <option value="Troca por outro produto">Troca por outro produto</option>
                    </select>
                </div>

                <div style="display:flex;gap:8px;">
                    <button type="button" wire:click="abrirDevolucao" style="flex:1;padding:12px;border:1px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Cancelar</button>
                    <button type="button" wire:click="confirmarDevolucao" style="flex:1;padding:12px;border:0;border-radius:10px;background:var(--warning);color:#fff;cursor:pointer;font-weight:800;">
                        <span wire:loading.remove>Confirmar Devolução</span>
                        <span wire:loading>Processando...</span>
                    </button>
                </div>
            @endif

            @if (!$this->devVenda && !$this->devMensagem)
                <div style="text-align:center;padding:20px;color:#64748b;font-size:13px;">
                    <i class="fas fa-search" style="font-size:32px;display:block;margin-bottom:8px;opacity:0.3;"></i>
                    Informe o número da venda para buscar.
                </div>
            @endif
        </div>
    </div>
</div>

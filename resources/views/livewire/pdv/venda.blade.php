<div style="display:flex;flex-direction:column;height:100dvh;overflow:hidden;background:#0f172a;" x-data="pdvApp()" @keydown.window="handleKey($event)" @estoque-baixo.window="estoqueBaixoMsg = $event.detail.nome; estoqueBaixoQtd = $event.detail.estoque; setTimeout(() => estoqueBaixoMsg = '', 4000)" x-init="estoqueBaixoMsg = ''; estoqueBaixoQtd = 0;">
    <header style="display:flex;align-items:center;justify-content:space-between;padding:8px 20px;background:#0b1220;border-bottom:1px solid rgba(255,255,255,0.06);flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:8px;background:rgba(34,197,94,0.12);display:flex;align-items:center;justify-content:center;color:var(--success);font-size:16px;"><i class="fas fa-shopping-cart"></i></div>
                <span style="font-weight:800;font-size:16px;color:#fff;">{{ collect($this->lojas)->firstWhere('id', (int)$this->loja_id)['nome'] ?? 'PDV' }}</span>
            </div>
            <div style="display:flex;gap:10px;font-size:10px;color:#94a3b8;font-weight:600;">
                <span style="display:flex;align-items:center;gap:4px;background:#14213d;padding:3px 10px;border-radius:20px;">
                    <span style="width:5px;height:5px;border-radius:50%;background:var(--success);"></span>Caixa Aberto
                </span>
                <span>{{ auth()->user()?->name ?? 'Operador' }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:11px;">
            <button wire:click="abrirDashboard" style="background:rgba(99,102,241,0.15);border:0;border-radius:6px;color:#818cf8;cursor:pointer;padding:4px 8px;font-size:10px;font-weight:600;" title="Dashboard"><i class="fas fa-chart-bar"></i></button>
            <button wire:click="abrirDevolucao" style="background:rgba(245,158,11,0.15);border:0;border-radius:6px;color:var(--warning);cursor:pointer;padding:4px 8px;font-size:10px;font-weight:600;" title="Devolução"><i class="fas fa-undo-alt"></i></button>
            <button @click="toggleFullscreen()" style="background:rgba(255,255,255,0.06);border:0;border-radius:6px;color:#94a3b8;cursor:pointer;padding:4px 8px;font-size:10px;font-weight:600;">
                <i class="fas fa-expand"></i>
            </button>
            <span x-text="currentTime" style="color:#fff;font-weight:700;font-size:14px;"></span>
        </div>
    </header>
    <main style="flex:1;display:grid;grid-template-columns:1fr minmax(320px,400px);gap:10px;padding:10px;overflow:hidden;">
        <div style="display:flex;flex-direction:column;gap:8px;">
            <div style="background:#fff;border-radius:10px;padding:10px 14px;flex-shrink:0;position:relative;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <i class="fas fa-search" style="color:#94a3b8;font-size:13px;"></i>
                    <input x-ref="searchInput" @input="scheduleSearch($el.value)" @keydown.enter.prevent="searchEnter()" type="text" placeholder="Código, nome ou escaneie..." style="flex:1;border:0;outline:none;font-size:13px;color:#0f172a;background:transparent;padding:4px 10px;">
                    <button type="button" @click="openScanner()" style="background:none;border:0;color:#64748b;cursor:pointer;font-size:16px;padding:4px;"><i class="fas fa-camera"></i></button>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                    <span style="font-size:10px;font-weight:600;color:#64748b;">Qtd:</span>
                    <input wire:model.live="qtdBusca" type="text" inputmode="numeric" 
                           style="width:36px;text-align:center;padding:1px 4px;border:1px solid var(--border);border-radius:4px;font-size:12px;font-weight:700;color:#0f172a;outline:none;">
                </div>
                @if (mb_strlen(trim($this->buscaProduto)) >= 2)
                    <div style="position:absolute;z-index:20;left:12px;right:12px;top:48px;max-height:260px;overflow:auto;border:1px solid var(--border);border-radius:8px;background:#fff;box-shadow:0 12px 30px rgba(0,0,0,0.15);">
                        @forelse ($this->resultadosProduto as $product)
                            <button wire:click="adicionarProduto({{ $product['id'] }})" style="display:flex;align-items:center;gap:10px;width:100%;padding:6px 12px;border:0;border-bottom:1px solid var(--border);background:transparent;color:#0f172a;text-align:left;cursor:pointer;font-size:12px;min-height:48px;" @disabled($product['estoque_disponivel'] <= 0) @click="if($refs.searchInput) { setTimeout(() => $refs.searchInput.focus(), 0); }">
                                <img src="{{ $product['foto_url'] ? asset('storage/' . $product['foto_url']) : '' }}" 
                                     style="width:36px;height:36px;border-radius:6px;object-fit:cover;background:#f1f5f9;flex-shrink:0;{{ $product['foto_url'] ? '' : 'display:none;' }}"
                                     onerror="this.style.display='none'">
                                <div style="width:36px;height:36px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:14px;flex-shrink:0;" 
                                     {{ $product['foto_url'] ? 'style=display:none' : '' }}>
                                    <i class="fas fa-box"></i>
                                </div>
                                <span style="flex:1;"><strong>{{ $product['nome_completo'] }}</strong><small style="display:block;color:#64748b;font-size:10px;margin-top:1px;">{{ $product['marca']['nome'] ?? '' }} · Est: {{ number_format((float)$product['estoque_disponivel'], 3, ',', '.') }}</small></span>
                                <span style="color:var(--success);font-weight:800;white-space:nowrap;">R$ {{ number_format((float)$product['preco_venda'], 2, ',', '.') }}</span>
                            </button>
                        @empty
                            <div style="padding:16px;text-align:center;color:#94a3b8;font-size:12px;">Nenhum produto encontrado.</div>
                        @endforelse
                    </div>
                @endif
            </div>
            <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;background:#fff;border-radius:10px;">
                <div style="display:grid;grid-template-columns:36px 36px 1fr 70px 70px 70px 90px 30px;padding:8px 12px;background:#f8fafc;border-bottom:1px solid var(--border);font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;flex-shrink:0;">
                    <span></span><span></span><span>Descrição</span><span style="text-align:right;">Qtde.</span><span style="text-align:right;">Desconto</span><span style="text-align:right;">Unit.</span><span style="text-align:right;">Total</span><span></span>
                </div>
                <div style="flex:1;overflow-y:auto;">
                    @forelse ($this->carrinho as $index => $item)
                        <div wire:key="cart-{{ $item['variacao_id'] }}" style="display:grid;grid-template-columns:36px 36px 1fr 70px 70px 70px 90px 30px;padding:6px 12px;border-bottom:1px solid #f1f5f9;align-items:center;font-size:12px;color:#0f172a;min-height:48px;">
                            <span style="font-weight:700;color:#94a3b8;font-size:11px;">{{ $index + 1 }}</span>
                            <div style="position:relative;">
                                <img src="{{ $item['foto_url'] ? asset('storage/' . $item['foto_url']) : '' }}"
                                     style="width:32px;height:32px;border-radius:5px;object-fit:cover;background:#f1f5f9;{{ $item['foto_url'] ? '' : 'display:none;' }}"
                                     onerror="this.style.display='none'">
                                <div style="width:32px;height:32px;border-radius:5px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;{{ $item['foto_url'] ? 'display:none;' : '' }}">
                                    <i class="fas fa-box"></i>
                                </div>
                                @if ((float)$item['quantidade'] > 1)
                                    <span style="position:absolute;top:-4px;right:-4px;background:var(--success);color:#fff;font-size:8px;font-weight:800;width:16px;height:16px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:1.5px solid #fff;">{{ $item['quantidade'] }}</span>
                                @endif
                            </div>
                            <div>
                                <span style="font-weight:600;display:block;text-transform:uppercase;font-size:11px;">{{ $item['nome'] }}</span>
                                <span style="font-size:10px;color:#94a3b8;font-family:monospace;">{{ $item['codigo_barras'] ?: ($item['sku'] ?? '#' . $item['variacao_id']) }}</span>
                            </div>
                            <input wire:model.live.debounce.300ms="carrinho.{{ $index }}.quantidade" type="text" inputmode="decimal" style="text-align:right;padding:3px 6px;border:1px solid var(--border);border-radius:5px;font-size:12px;width:60px;margin-left:auto;color:#0f172a;background:#fff;outline:none;min-height:36px;">
                            <input wire:model.live.debounce.300ms="carrinho.{{ $index }}.desconto" type="text" inputmode="decimal" placeholder="0,00" style="text-align:right;padding:3px 6px;border:1px solid var(--border);border-radius:5px;font-size:12px;width:60px;margin-left:auto;color:var(--danger);background:#fffcfc;outline:none;min-height:36px;">
                            <span style="text-align:right;font-weight:500;font-size:12px;">R$ {{ number_format((float)$item['preco'], 2, ',', '.') }}</span>
                            <span style="text-align:right;font-weight:700;font-size:12px;">R$ {{ number_format(((float)$item['quantidade'])*(float)$item['preco'] - (float)($item['desconto'] ?? 0), 2, ',', '.') }}</span>
                            <button wire:click="removerItem({{ $index }})" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:14px;padding:4px;min-height:36px;"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    @empty
                        <div style="display:grid;place-items:center;color:#94a3b8;font-size:12px;padding:40px;"><i class="fas fa-box-open" style="font-size:32px;margin-bottom:8px;opacity:0.3;"></i>Busque um produto</div>
                    @endforelse
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;padding:8px 14px;border-top:1px solid var(--border);background:#fff;flex-shrink:0;">
                    <div><span style="color:#64748b;font-size:10px;font-weight:700;">Itens</span><br><span style="font-weight:900;font-size:18px;color:#0f172a;">{{ count($this->carrinho) }}</span></div>
                    <div style="text-align:right;"><span style="color:#64748b;font-size:10px;font-weight:700;">Subtotal</span><br><span style="font-weight:900;font-size:18px;color:var(--success);">R$ {{ number_format($this->subtotal, 2, ',', '.') }}</span></div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(70px,1fr));gap:4px;flex-shrink:0;">
                <button wire:click="abrirListaCancelItem" {{ count($this->carrinho) === 0 ? 'disabled' : '' }} style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;{{ count($this->carrinho) === 0 ? 'opacity:0.4;' : '' }}" title="Cancelar Item">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--danger);display:grid;place-items:center;color:var(--danger);"><i class="fas fa-trash-alt" style="font-size:9px;"></i></span>Cancelar Item
                </button>
                <button wire:click="abrirCancelVenda" {{ count($this->carrinho) === 0 ? 'disabled' : '' }} style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;{{ count($this->carrinho) === 0 ? 'opacity:0.4;' : '' }}">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--danger);display:grid;place-items:center;color:var(--danger);"><i class="fas fa-times" style="font-size:10px;"></i></span>Cancelar Venda
                </button>
                <button wire:click="$set('passo', 'inicio')" style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:var(--text);font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--primary-600);display:grid;place-items:center;color:var(--primary-600);"><i class="fas fa-home" style="font-size:9px;"></i></span>Início
                </button>
                <button @click="showDiscount()" style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--success);display:grid;place-items:center;color:var(--success);"><i class="fas fa-percentage" style="font-size:9px;"></i></span>Desconto <span style="font-size:7px;color:#94a3b8;">F4</span>
                </button>
                <button @click="showClient()" style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--primary-600);display:grid;place-items:center;color:var(--primary-600);"><i class="fas fa-user" style="font-size:9px;"></i></span>Cliente <span style="font-size:7px;color:#94a3b8;">F6</span>
                </button>
                <button wire:click="abrirMovModal('sangria')" style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--warning);display:grid;place-items:center;color:var(--warning);"><i class="fas fa-hand-holding" style="font-size:9px;"></i></span>Sangria
                </button>
                <button wire:click="abrirMovModal('suprimento')" style="padding:6px;background:#fff;border:1px solid var(--border);border-radius:8px;cursor:pointer;color:#475569;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;border:2px solid var(--primary-600);display:grid;place-items:center;color:var(--primary-600);"><i class="fas fa-hand-holding-heart" style="font-size:9px;"></i></span>Suprimento
                </button>
                <button wire:click="irPagamento" style="padding:6px;background:var(--success);border:0;border-radius:8px;cursor:pointer;color:#fff;font-size:9px;font-weight:700;display:flex;flex-direction:column;align-items:center;gap:3px;min-height:44px;">
                    <span style="width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,0.2);display:grid;place-items:center;"><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>Pagamento <span style="font-size:7px;color:rgba(255,255,255,0.6);">F5</span>
                </button>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <div style="background:#fff;border-radius:12px;padding:16px 18px;flex-shrink:0;">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;font-weight:500;margin-bottom:6px;"><span>Subtotal</span><span style="font-weight:700;font-size:14px;">R$ {{ number_format($this->subtotal, 2, ',', '.') }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;font-weight:500;margin-bottom:6px;"><span>Desconto</span><span style="color:var(--success);font-weight:700;font-size:14px;">- R$ {{ number_format($this->decimal($this->desconto), 2, ',', '.') }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#475569;font-weight:500;margin-bottom:10px;"><span>Acréscimo</span><span style="color:var(--primary-600);font-weight:700;font-size:14px;">+ R$ {{ number_format($this->decimal($this->acrescimo), 2, ',', '.') }}</span></div>
                <div style="border-top:3px solid #0f172a;padding-top:12px;display:flex;justify-content:space-between;align-items:end;">
                    <span style="font-weight:900;font-size:15px;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px;">Total</span>
                    <span style="font-weight:900;font-size:30px;color:var(--success);">R$ {{ number_format($this->total, 2, ',', '.') }}</span>
                </div>
            </div>
            <div style="flex:1;background:#0f172a;border-radius:10px;padding:8px;display:flex;flex-direction:column;">
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;flex:1;">
                    <button @click="numpad('7')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">7</button>
                    <button @click="numpad('8')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">8</button>
                    <button @click="numpad('9')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">9</button>
                    <button @click="numpad('bs')" style="background:#1e293b;border:0;border-radius:6px;color:#f87171;font-weight:800;font-size:16px;cursor:pointer;min-height:44px;"><i class="fas fa-backspace"></i></button>
                    <button @click="numpad('4')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">4</button>
                    <button @click="numpad('5')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">5</button>
                    <button @click="numpad('6')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">6</button>
                    <button @click="numpad('limpar')" style="background:#1e293b;border:0;border-radius:6px;color:#fb923c;font-weight:800;font-size:11px;cursor:pointer;min-height:44px;">C</button>
                    <button @click="numpad('1')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">1</button>
                    <button @click="numpad('2')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">2</button>
                    <button @click="numpad('3')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">3</button>
                    <button @click="numpad('+')" style="background:#1e293b;border:0;border-radius:6px;color:var(--success);font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">+</button>
                    <button @click="numpad('0')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">0</button>
                    <button @click="numpad(',')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:18px;cursor:pointer;min-height:44px;">,</button>
                    <button @click="numpad('00')" style="background:#1e293b;border:0;border-radius:6px;color:#fff;font-weight:800;font-size:16px;cursor:pointer;min-height:44px;">00</button>
                    <button wire:click="irPagamento" style="background:var(--success);border:0;border-radius:6px;color:#fff;font-weight:800;font-size:13px;cursor:pointer;min-height:44px;">OK</button>
                </div>
            </div>
        </div>
    </main>
    {{-- Toast de estoque baixo --}}
    <div x-show="estoqueBaixoMsg" style="position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:999;background:var(--warning);color:#fff;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,0.3);display:flex;align-items:center;gap:8px;">
        <i class="fas fa-exclamation-triangle"></i>
        <span x-text="'Estoque baixo: ' + estoqueBaixoMsg + ' (' + estoqueBaixoQtd + ' un)'"></span>
    </div>
    <div wire:ignore x-show="showScanner" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;" @click.self="closeScanner()" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:400px;width:90%;text-align:center;">
            <p style="font-size:14px;font-weight:700;color:#0f172a;margin-bottom:12px;">Escaneie o código de barras</p>
            <div id="scanner-elem" style="width:100%;aspect-ratio:1;overflow:hidden;border-radius:12px;background:#000;"></div>
            <div x-show="scanError" style="font-size:12px;color:var(--danger);margin-top:6px;" x-text="scanError"></div>
            <button @click="closeScanner()" style="margin-top:12px;padding:8px 24px;background:var(--danger);color:#fff;border:0;border-radius:8px;cursor:pointer;font-weight:700;">Fechar</button>
        </div>
    </div>
    <div x-show="showClientModal" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;" @click.self="showClientModal = false" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:400px;width:90%;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;">Cliente</h3>
            @if ($this->clienteNome)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-radius:8px;background:#f0fdf4;color:var(--success);font-size:14px;font-weight:700;">
                    <span>{{ $this->clienteNome }}</span>
                    <button wire:click="removerCliente" style="background:none;border:0;color:var(--danger);cursor:pointer;">Remover</button>
                </div>
            @else
                <input wire:model.live.debounce.250ms="buscaCliente" type="search" placeholder="Nome ou CPF..." style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                @if (count($this->clientes) > 0)
                    <div style="margin-top:8px;max-height:200px;overflow-y:auto;">
                        @foreach ($this->clientes as $client)
                            <button wire:click="selecionarCliente({{ $client['id'] }})" style="display:block;width:100%;text-align:left;padding:8px 10px;border:0;border-bottom:1px solid var(--border);background:none;cursor:pointer;color:#0f172a;font-size:13px;min-height:44px;">
                                <strong>{{ $client['nome'] }}</strong>
                                <small style="color:#64748b;">{{ $client['cpf'] ?? '' }}</small>
                            </button>
                        @endforeach
                    </div>
                @endif
            @endif
            <button @click="showClientModal = false" style="width:100%;margin-top:12px;padding:10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Fechar</button>
        </div>
    </div>
    <div x-show="showDiscountModal" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;" @click.self="showDiscountModal = false" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:360px;width:90%;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;">Desconto / Acréscimo</h3>
            <div style="display:flex;gap:8px;margin-bottom:12px;">
                <button type="button" x-on:click="$wire.set('descontoTipo', 'valor')" style="flex:1;padding:8px;border:0;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px;{{ $this->descontoTipo === 'valor' ? 'background:var(--success);color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">Valor (R$)</button>
                <button type="button" x-on:click="$wire.set('descontoTipo', 'percentual')" style="flex:1;padding:8px;border:0;border-radius:8px;cursor:pointer;font-weight:700;font-size:12px;{{ $this->descontoTipo === 'percentual' ? 'background:var(--success);color:#fff;' : 'background:#f1f5f9;color:#475569;' }}">Percentual (%)</button>
            </div>
            @if ($this->descontoTipo === 'valor')
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Desconto (R$)</label>
                    <input wire:model.blur="desconto" type="text" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                </div>
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Acréscimo (R$)</label>
                    <input wire:model.blur="acrescimo" type="text" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                </div>
            @else
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Desconto (%)</label>
                    <input wire:model.blur="descontoPct" type="text" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                </div>
                <div style="margin-bottom:8px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Acréscimo (%)</label>
                    <input wire:model.blur="acrescimoPct" type="text" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                </div>
            @endif
            <button x-on:click="if($wire.descontoTipo === 'percentual') { $wire.call('aplicarDescontoPercentual') }; showDiscountModal = false" style="width:100%;padding:10px;border:0;border-radius:8px;background:var(--success);color:#fff;cursor:pointer;font-weight:700;">Aplicar</button>
        </div>
    </div>
    {{-- CANCELAR LISTA MODAL --}}
    <div x-show="$wire.cancelListaOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;" @click.self="$wire.set('cancelListaOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:420px;width:90%;max-height:80dvh;display:flex;flex-direction:column;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span style="width:28px;height:28px;border-radius:50%;background:#fef2f2;display:grid;place-items:center;color:var(--danger);font-size:12px;"><i class="fas fa-trash-alt"></i></span>
                Selecionar Itens para Cancelar
            </h3>
            <p style="font-size:12px;color:#64748b;margin-bottom:8px;">Marque os itens que deseja cancelar:</p>
            @error('cancelLista')<div style="font-size:11px;color:var(--danger);margin-bottom:6px;">{{ $message }}</div>@enderror
            <div style="flex:1;overflow-y:auto;border:1px solid var(--border);border-radius:8px;">
                @foreach ($this->carrinho as $i => $item)
                    <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-bottom:1px solid #f1f5f9;cursor:pointer;font-size:13px;min-height:44px;{{ in_array($i, $this->cancelItensSelecionados) ? 'background:#fef2f2;' : '' }}">
                        <input type="checkbox" wire:click="toggleCancelItem({{ $i }})" {{ in_array($i, $this->cancelItensSelecionados) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--danger);">
                        <strong style="flex:1;color:#0f172a;">{{ $item['nome'] }}</strong>
                        <span style="font-weight:700;color:#475569;">R$ {{ number_format(((float)$item['quantidade'])*(float)$item['preco'], 2, ',', '.') }}</span>
                    </label>
                @endforeach
            </div>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button type="button" wire:click="$set('cancelListaOpen', false)" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Voltar</button>
                <button type="button" wire:click="confirmarSelecaoCancela" style="flex:1;padding:10px;border:0;border-radius:8px;background:var(--danger);color:#fff;cursor:pointer;font-weight:700;">Continuar</button>
            </div>
        </div>
    </div>

    {{-- CANCELAR MODAL --}}
    <div x-show="$wire.cancelModalOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;" @click.self="$wire.set('cancelModalOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:380px;width:90%;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span style="width:28px;height:28px;border-radius:50%;background:#fef2f2;display:grid;place-items:center;color:var(--danger);font-size:12px;"><i class="fas fa-trash-alt"></i></span>
                Cancelar {{ $this->cancelTipo === 'venda' ? 'Venda' : (count($this->cancelItensSelecionados) > 1 ? count($this->cancelItensSelecionados) . ' Itens' : 'Item') }}
            </h3>
            <p style="font-size:13px;color:#64748b;margin-bottom:12px;">Informe o motivo do cancelamento:</p>
            <div style="margin-bottom:14px;">
                <select wire:model="cancelMotivo" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                    <option value="">Selecione...</option>
                    <option value="Cliente desistiu">Cliente desistiu</option>
                    <option value="Produto errado">Produto errado</option>
                    <option value="Problema no leitor">Problema no leitor</option>
                    <option value="Troca por outro produto">Troca por outro produto</option>
                    <option value="Erro do operador">Erro do operador</option>
                </select>
                @error('cancelMotivo')<div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" wire:click="$set('cancelModalOpen', false)" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Voltar</button>
                <button type="button" wire:click="confirmarCancelamento" style="flex:1;padding:10px;border:0;border-radius:8px;background:var(--danger);color:#fff;cursor:pointer;font-weight:700;">Confirmar</button>
            </div>
        </div>
    </div>

    {{-- MOVIMENTO MODAL --}}
    <div x-show="$wire.movModalOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;" @click.self="$wire.fecharMovModal()" x-cloak>
        <div style="background:#fff;border-radius:16px;padding:20px;max-width:380px;width:90%;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;font-size:12px;{{ $this->movTipo === 'sangria' ? 'background:#fef3c7;color:var(--warning);' : 'background:#dbeafe;color:var(--primary-600);' }}">
                    <i class="fas {{ $this->movTipo === 'sangria' ? 'fa-hand-holding' : 'fa-hand-holding-heart' }}"></i>
                </span>
                {{ $this->movTipo === 'sangria' ? 'Sangria' : 'Suprimento' }}
            </h3>
            <div style="margin-bottom:10px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Valor (R$) *</label>
                <input wire:model="movValor" type="text" inputmode="decimal" placeholder="0,00" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                @error('movValor')<div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>@enderror
            </div>
            <div style="margin-bottom:14px;">
                <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:4px;">Motivo *</label>
                <input wire:model="movMotivo" placeholder="Ex: Depósito bancário" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                @error('movMotivo')<div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" wire:click="fecharMovModal" style="flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Cancelar</button>
                <button type="button" wire:click="registrarMovimento" style="flex:1;padding:10px;border:0;border-radius:8px;cursor:pointer;font-weight:700;color:#fff;{{ $this->movTipo === 'sangria' ? 'background:var(--warning);' : 'background:var(--primary-600);' }}">
                    <span wire:loading.remove>Registrar</span>
                    <span wire:loading>...</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'F11' && window.location.href.includes('/vendas')) {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
        }
    }
});
</script>

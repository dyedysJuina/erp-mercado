<div style="font-family:'Inter',system-ui,sans-serif;min-height:100vh;background:color-mix(in srgb,var(--text)4%,var(--background));color:var(--text);">

    <x-vitrine-header :carrinhoCount="0" />

    <div style="max-width:800px;margin:0 auto;padding:1rem;">
        {{-- Abas --}}
        <div style="display:flex;gap:4px;overflow-x:auto;margin-top:-1.25rem;margin-bottom:1.25rem;scrollbar-width:none;">
            @php $abas = ['dados'=>'Meus Dados','pedidos'=>'Pedidos','enderecos'=>'Enderecos','seguranca'=>'Seguranca']; $icons = ['dados'=>'fa-user','pedidos'=>'fa-box','enderecos'=>'fa-map-marker-alt','seguranca'=>'fa-lock']; @endphp
            @foreach ($abas as $key => $label)
                <button wire:click="$set('aba','{{ $key }}')" style="flex-shrink:0;padding:0.6rem 1.2rem;border-radius:999px;font-size:0.82rem;font-weight:600;border:none;cursor:pointer;transition:0.2s;{{ $aba === $key ? 'background:var(--primary-500);color:#fff;box-shadow:0 4px 12px color-mix(in srgb,var(--primary-500)30%,transparent);' : 'background:var(--surface);color:var(--muted);border:1px solid var(--border);' }}">
                    <i class="fas {{ $icons[$key] }}" style="margin-right:4px;"></i> {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- DADOS --}}
        @if ($aba === 'dados' && $c)
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:700;margin:0 0 1rem;color:var(--text);">Meus Dados</h2>
                <form wire:submit="salvarDados">
                    <div style="margin-bottom:0.75rem;">
                        <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">Nome completo</label>
                        <input wire:model="edit_nome" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        @error('edit_nome')<p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--danger);">{{ $message }}</p>@enderror
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:0.75rem;">
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">E-mail</label>
                            <input wire:model="edit_email" type="email" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        </div>
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">WhatsApp</label>
                            <input wire:model="edit_whatsapp" type="tel" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                            @error('edit_whatsapp')<p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--danger);">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:1rem;">
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">CPF</label>
                            <input wire:model="edit_cpf" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        </div>
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">Data Nasc.</label>
                            <input wire:model="edit_data_nascimento" type="date" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        </div>
                    </div>
                    <button type="submit" style="width:100%;padding:0.7rem;background:var(--primary-500);color:#fff;border:none;border-radius:0.6rem;font-size:0.88rem;font-weight:600;cursor:pointer;">Salvar alteracoes</button>
                </form>
            </div>
        @endif

        {{-- PEDIDOS --}}
        @if ($aba === 'pedidos')
            @php $pedidos = $this->pedidosRecentes; @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:700;margin:0 0 1rem;color:var(--text);">Meus Pedidos</h2>
                @if (count($pedidos) > 0)
                    @foreach ($pedidos as $p)
                        @php $pTotal = number_format($p['total'],2,',','.'); @endphp
                        <div style="padding:0.75rem 0;border-bottom:1px solid var(--border);">
                            <div style="display:flex;justify-content:space-between;align-items:start;">
                                <div>
                                    <strong style="font-size:0.9rem;color:var(--text);">Pedido #{{ $p['id'] }}</strong>
                                    <span style="font-size:0.75rem;color:var(--muted);margin-left:6px;">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y') }}</span>
                                    <div style="font-size:0.8rem;color:var(--muted);margin-top:2px;">{{ count($p['itens']) }} itens · R$ {{ $pTotal }}</div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.65rem;font-weight:600;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);">{{ $p['status'] }}</span>
                                    <button wire:click="verPedido({{ $p['id'] }})" style="display:block;margin-top:4px;padding:2px 8px;border:1px solid var(--border);border-radius:6px;background:transparent;font-size:0.72rem;cursor:pointer;color:var(--primary-500);">Detalhes</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:0.85rem;">Nenhum pedido encontrado.</div>
                @endif
            </div>
            @if ($this->pedidoDetalhe)
                @php $p = $this->pedidoDetalhe; @endphp
                <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;margin-top:0.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                        <h3 style="margin:0;font-size:1rem;font-weight:700;color:var(--text);">Pedido #{{ $p['id'] }}</h3>
                        <button wire:click="fecharPedido" style="padding:4px 10px;border:1px solid var(--border);border-radius:6px;background:transparent;font-size:0.75rem;cursor:pointer;color:var(--muted);">Fechar</button>
                    </div>
                    <div style="font-size:0.78rem;color:var(--muted);margin-bottom:0.5rem;">{{ \Carbon\Carbon::parse($p['created_at'])->format('d/m/Y H:i') }} · <strong style="color:var(--primary-500);">{{ $p['status'] }}</strong></div>
                    @foreach ($p['itens'] as $item)
                        @php $v = $item['variacao'] ?? []; $itemNome = $v['nome_completo'] ?? 'Produto'; @endphp
                        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid var(--border);font-size:0.82rem;">
                            <span style="color:var(--text);">{{ $itemNome }} <span style="color:var(--muted);font-size:0.7rem;">x{{ number_format($item['quantidade_solicitada'],1,',','.') }}</span></span>
                            <span style="font-weight:600;color:var(--text);">R$ {{ number_format($item['total_item'],2,',','.') }}</span>
                        </div>
                    @endforeach
                    <div style="text-align:right;margin-top:0.5rem;font-size:1rem;font-weight:700;color:var(--text);">Total: R$ {{ number_format($p['total'],2,',','.') }}</div>
                </div>
            @endif
        @endif

        {{-- ENDERECOS --}}
        @if ($aba === 'enderecos' && $c)
            @php $enderecos = $this->enderecos; @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 style="font-size:1.1rem;font-weight:700;margin:0;color:var(--text);">Enderecos</h2>
                    <button wire:click="novoEndereco" style="padding:6px 14px;background:var(--primary-500);color:#fff;border:none;border-radius:0.6rem;font-size:0.78rem;font-weight:600;cursor:pointer;">+ Novo</button>
                </div>
                @if (count($enderecos) > 0)
                    @foreach ($enderecos as $e)
                        @php $ePri = $e['principal']; $eTit = $e['titulo'] ?? ''; @endphp
                        <div style="padding:0.75rem 0;border-bottom:1px solid var(--border);">
                            <div style="display:flex;justify-content:space-between;align-items:start;gap:8px;">
                                <div style="flex:1;min-width:0;">
                                    <strong style="font-size:0.88rem;color:var(--text);">{{ $e['logradouro'] }}{{ $e['numero'] ? ', '.$e['numero'] : '' }}</strong>@if($ePri) <span style="background:var(--primary-500);color:#fff;font-size:0.55rem;padding:1px 6px;border-radius:4px;font-weight:700;vertical-align:middle;">PRINCIPAL</span>@endif
                                    <div style="font-size:0.78rem;color:var(--muted);margin-top:2px;">{{ $e['bairro'] ?? '' }}{{ $eTit ? ' · '.$eTit : '' }}{{ $e['cep'] ? ' · CEP '.$e['cep'] : '' }}</div>
                                </div>
                                <div style="display:flex;gap:4px;flex-shrink:0;">
                                    <button wire:click="editarEndereco({{ $e['id'] }})" style="padding:3px 8px;border:1px solid var(--border);border-radius:5px;background:transparent;font-size:0.7rem;cursor:pointer;color:var(--text);">Editar</button>
                                    @if(!$ePri)<button wire:click="definirEnderecoPrincipal({{ $e['id'] }})" style="padding:3px 8px;border:1px solid var(--border);border-radius:5px;background:transparent;font-size:0.7rem;cursor:pointer;color:var(--muted);">⭐</button>@endif
                                    @if(!$ePri)<button wire:click="excluirEndereco({{ $e['id'] }})" style="padding:3px 8px;border:1px solid var(--border);border-radius:5px;background:transparent;font-size:0.7rem;cursor:pointer;color:var(--danger);"><i class="fas fa-trash"></i></button>@endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:0.85rem;">Nenhum endereco cadastrado.</div>
                @endif
            </div>
        @endif

        {{-- SEGURANCA --}}
        @if ($aba === 'seguranca' && $c)
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:700;margin:0 0 1rem;color:var(--text);">Seguranca</h2>
                <form wire:submit="alterarSenha">
                    <div style="margin-bottom:0.75rem;">
                        <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">Senha atual</label>
                        <input wire:model="senha_atual" type="password" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        @error('senha_atual')<p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--danger);">{{ $message }}</p>@enderror
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:0.75rem;">
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">Nova senha</label>
                            <input wire:model="senha_nova" type="password" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                            @error('senha_nova')<p style="margin:0.2rem 0 0;font-size:0.72rem;color:var(--danger);">{{ $message }}</p>@enderror
                        </div>
                        <div style="flex:1;min-width:150px;">
                            <label style="display:block;font-size:0.72rem;font-weight:600;color:var(--muted);margin-bottom:0.25rem;">Confirmar</label>
                            <input wire:model="senha_confirmacao" type="password" style="width:100%;padding:0.7rem 0.9rem;border-radius:0.6rem;border:1px solid var(--border);font-size:0.9rem;color:var(--text);background:var(--surface);outline:none;box-sizing:border-box;">
                        </div>
                    </div>
                    <button type="submit" style="width:100%;padding:0.7rem;background:var(--primary-500);color:#fff;border:none;border-radius:0.6rem;font-size:0.88rem;font-weight:600;cursor:pointer;">Alterar senha</button>
                </form>
            </div>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:1rem;padding:1.25rem;margin-top:0.75rem;">
                <button wire:click="logout" style="padding:0.6rem 1.25rem;background:color-mix(in srgb,var(--danger)10%,transparent);color:var(--danger);border:1px solid color-mix(in srgb,var(--danger)20%,transparent);border-radius:0.6rem;font-weight:600;font-size:0.82rem;cursor:pointer;">Sair da conta</button>
            </div>
        @endif
    </div>
</div>

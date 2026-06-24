<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background:#0f172a;">
    <section style="max-width:440px;width:100%;padding:28px;background:#fff;border-radius:20px;text-align:center;">
        <div style="width:54px;height:54px;margin:0 auto 12px;border-radius:16px;background:#f0fdf4;color:var(--success);display:grid;place-items:center;font-size:30px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="margin:0;font-size:20px;color:#0f172a;">Venda concluida</h2>
        <p style="color:#64748b;font-size:12px;margin-top:2px;">Venda #{{ $this->vendaId }} · {{ now()->format('d/m/Y H:i') }}</p>

        <div style="background:#f8fafc;border-radius:12px;padding:14px;margin:14px 0;text-align:left;font-size:12px;color:#475569;border:1px solid #e2e8f0;">
            @if ($this->clienteNome)
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>Cliente</span><span style="font-weight:600;color:#0f172a;">{{ $this->clienteNome }}</span></div>
            @endif
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>Itens</span><span style="font-weight:600;color:#0f172a;">{{ count($this->carrinho) }}</span></div>
            @if (count($this->pagamentos) > 0)
                <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>Pagamento</span><span style="font-weight:600;color:#0f172a;">{{ collect($this->pagamentos)->pluck('nome')->implode(', ') }}</span></div>
            @endif
            <div style="display:flex;justify-content:space-between;padding-top:6px;border-top:1px solid #e2e8f0;margin-top:4px;font-size:14px;font-weight:800;color:#0f172a;">
                <span>Total</span><span style="color:var(--success);">R$ {{ number_format($this->totalFinalizado, 2, ',', '.') }}</span>
            </div>
        </div>

        @if ((float) str_replace(['.', ','], ['', '.'], $this->troco) > 0)
            <div style="display:flex;justify-content:space-between;padding:10px;border-radius:8px;background:#f0fdf4;color:var(--success);font-weight:700;font-size:14px;margin-bottom:14px;">
                <span>Troco</span><span>R$ {{ $this->troco }}</span>
            </div>
        @endif

        <div style="margin-top:12px;">
            @if ($this->nfceStatus === 'rascunho')
                <span style="display:block;font-size:11px;color:#64748b;margin-bottom:6px;">NFC-e gerada como rascunho</span>
                @if ($this->nfceDocumentoId)
                    <a href="{{ route('fiscal.danfe', $this->nfceDocumentoId) }}" target="_blank" style="display:inline-block;padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a;font-weight:700;text-decoration:none;font-size:12px;margin:2px;">Visualizar DANFE</a>
                    <a href="{{ route('fiscal.xml', $this->nfceDocumentoId) }}" target="_blank" style="display:inline-block;padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a;font-weight:700;text-decoration:none;font-size:12px;margin:2px;">Download XML</a>
                @endif
            @elseif ($this->nfceStatus === 'autorizada')
                <span style="display:block;font-size:11px;color:var(--success);margin-bottom:6px;">NFC-e autorizada ✓</span>
                @if ($this->nfceDocumentoId)
                    <a href="{{ route('fiscal.danfe', $this->nfceDocumentoId) }}" target="_blank" style="display:inline-block;padding:6px 14px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a;font-weight:700;text-decoration:none;font-size:12px;">Visualizar DANFE</a>
                @endif
            @elseif ($this->nfceStatus === 'erro')
                <span style="display:block;font-size:11px;color:var(--danger);margin-bottom:6px;">Erro ao emitir NFC-e</span>
                @error('nfce')<span style="font-size:10px;color:var(--danger);display:block;">{{ $message }}</span>@enderror
            @else
                <button wire:click="emitirNfce" wire:loading.attr="disabled" style="padding:8px 16px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#0f172a;font-weight:700;cursor:pointer;font-size:12px;">
                    <span wire:loading.remove>Emitir NFC-e</span>
                    <span wire:loading>Emitindo...</span>
                </button>
            @endif
        </div>

        <div style="display:flex;gap:6px;margin-top:12px;">
            <button wire:click="abrirEstorno" style="flex:1;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:var(--danger);font-weight:700;cursor:pointer;font-size:12px;">
                <i class="fas fa-undo-alt"></i> Estornar
            </button>
            <button wire:click="novaVenda" style="flex:2;padding:10px;border:0;border-radius:8px;background:var(--success);color:#fff;font-weight:800;cursor:pointer;font-size:13px;">
                Nova venda
            </button>
        </div>
    </section>

    {{-- ESTORNO MODAL --}}
    <div x-show="$wire.estornoModalOpen" style="position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;padding:20px;" @click.self="$wire.set('estornoModalOpen', false)" x-cloak>
        <div style="background:#fff;border-radius:20px;padding:24px;max-width:400px;width:100%;">
            <h3 style="margin:0 0 12px;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:8px;">
                <span style="width:32px;height:32px;border-radius:50%;background:#fef2f2;display:grid;place-items:center;color:var(--danger);"><i class="fas fa-undo-alt"></i></span>
                Estornar Venda #{{ $this->vendaId }}
            </h3>
            <p style="font-size:13px;color:#64748b;margin-bottom:14px;">Isso ira cancelar a venda, estornar o estoque e cancelar o lancamento financeiro. Informe o motivo:</p>
            <div style="margin-bottom:14px;">
                <select wire:model="estornoMotivo" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:8px;font-size:14px;color:#0f172a;">
                    <option value="">Selecione...</option>
                    <option value="Cliente desistiu">Cliente desistiu</option>
                    <option value="Produto danificado">Produto danificado</option>
                    <option value="Erro na finalizacao">Erro na finalizacao</option>
                    <option value="Troca/devolucao">Troca / Devolucao</option>
                </select>
                @error('estornoMotivo')<div style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</div>@enderror
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" wire:click="$set('estornoModalOpen', false)" style="flex:1;padding:12px;border:1px solid var(--border);border-radius:10px;background:#fff;cursor:pointer;font-weight:700;color:#475569;">Cancelar</button>
                <button type="button" wire:click="confirmarEstorno" style="flex:1;padding:12px;border:0;border-radius:10px;background:var(--danger);color:#fff;cursor:pointer;font-weight:800;">
                    <span wire:loading.remove>Confirmar Estorno</span>
                    <span wire:loading>Estornando...</span>
                </button>
            </div>
        </div>
    </div>
</div>

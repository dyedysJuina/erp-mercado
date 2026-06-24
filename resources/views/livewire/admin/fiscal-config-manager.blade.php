<div>
    <div class="main-content-pad" style="max-width:720px;">
        <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Configuração Fiscal</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Admin</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Certificado</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
        @if (session('fiscal_ok'))<div style="padding:12px 16px;border-radius:10px;background:color-mix(in srgb,var(--success)12%,transparent);color:var(--success);font-weight:700;margin-bottom:16px;"><i class="fas fa-check-circle"></i> {{ session('fiscal_ok') }}</div>@endif
        <div class="form-card" style="margin-bottom:16px;">
            <div class="form-card-header"><span class="form-card-title"><i class="fas fa-cog"></i> Dados do Perfil Fiscal</span></div>
            <form wire:submit="salvar" class="form-body">
                <div class="grid-2" style="gap:12px;"><div class="field"><label>Nome do Perfil</label><input wire:model="nome" placeholder="Ex: Perfil SN MT">@error('nome')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Descrição</label><input wire:model="descricao" placeholder="Opcional"></div></div>
                <div class="grid-3" style="gap:12px;margin-top:12px;">
                    <div class="field"><label>Ambiente</label><select wire:model="ambiente"><option value="2">Homologação (testes)</option><option value="1">Produção</option></select>@error('ambiente')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>Série NFC-e</label><input wire:model="serie_nfce" placeholder="1">@error('serie_nfce')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>Regime Tributário</label><select wire:model="regime_tributario"><option value="1">Simples Nacional</option><option value="2">Lucro Presumido</option><option value="3">Lucro Real</option></select></div>
                </div>
                <div class="grid-2" style="gap:12px;margin-top:12px;">
                    <div class="field"><label>CFOP Saída Padrão</label><input wire:model="cfop_saida_padrao" placeholder="5102"></div>
                    <div class="field"><label>CFOP Entrada Padrão</label><input wire:model="cfop_entrada_padrao" placeholder="1102"></div>
                </div>
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
                    <div class="section-title" style="margin-bottom:12px;"><i class="fas fa-shield-alt"></i> Certificado Digital A1</div>
                    @if ($certificado_carregado)<div style="padding:12px;border-radius:10px;background:color-mix(in srgb,var(--success)10%,transparent);color:var(--success);margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;"><span><i class="fas fa-check-circle"></i> Certificado carregado</span><button type="button" wire:click="removerCertificado" wire:confirm="Remover certificado?" style="background:none;border:0;color:var(--danger);cursor:pointer;font-size:13px;">Remover</button></div>@endif
                    <div class="grid-2" style="gap:12px;"><div class="field"><label>Arquivo (.pfx / .p12)</label><input type="file" wire:model="certificado" accept=".pfx,.p12">@error('certificado')<div class="err">{{ $message }}</div>@enderror</div><div class="field"><label>Senha do Certificado</label><input type="password" wire:model="certificado_senha" placeholder="Senha"></div></div>
                </div>
                <div class="form-actions" style="margin-top:20px;"><button type="submit" class="btn-primary btn-lg"><span wire:loading.remove>Salvar Configuração</span><span wire:loading>Salvando...</span></button></div>
            </form>
        </div>
        <div class="form-card">
            <div class="form-card-header"><span class="form-card-title"><i class="fas fa-info-circle"></i> Status da NFC-e</span></div>
            <div class="form-body" style="font-size:13px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div><strong>Ambiente:</strong> {{ $ambiente === '1' ? 'Produção' : 'Homologação' }}</div>
                    <div><strong>Série:</strong> {{ $serie_nfce }}</div>
                    <div><strong>Certificado:</strong> {{ $certificado_carregado ? 'Carregado ✅' : 'Ausente ❌' }}</div>
                    <div><strong>Próximo número:</strong> {{ $numero_nfce_atual }}</div>
                </div>
                <div style="margin-top:12px;padding:10px;border-radius:8px;background:color-mix(in srgb,var(--warning)10%,transparent);color:var(--warning);font-size:12px;"><i class="fas fa-exclamation-triangle"></i> @if (!$certificado_carregado)<strong>Sem certificado:</strong> as NFC-e serão geradas como rascunho.@elseif ($ambiente === '2')<strong>Homologação:</strong> teste sem valor fiscal.@else<strong>Produção:</strong> NFC-e com valor fiscal real.@endif</div>
            </div>
        </div>
    </div>
</div>

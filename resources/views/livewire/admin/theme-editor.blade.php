<?php
$groups = [
    'Primario (escalas)' => ['primary_50', 'primary_100', 'primary_200', 'primary_300', 'primary_400', 'primary_500', 'primary_600', 'primary_700', 'primary_800', 'primary_900'],
    'Layout' => ['surface', 'background', 'text', 'muted', 'border'],
    'Sidebar' => ['sidebar_surface', 'sidebar_text', 'sidebar_muted', 'sidebar_active_bg', 'sidebar_active_text', 'sidebar_active_dot'],
    'Cabecalho' => ['header_bg', 'header_border'],
    'Feedback' => ['success', 'warning', 'danger'],
    'Contraste e efeitos' => ['on_primary', 'on_success', 'on_warning', 'on_danger', 'shadow'],
];
$labels = [
    'primary_50'=>'Primary 50','primary_100'=>'Primary 100','primary_200'=>'Primary 200','primary_300'=>'Primary 300','primary_400'=>'Primary 400',
    'primary_500'=>'Primary 500','primary_600'=>'Primary 600','primary_700'=>'Primary 700','primary_800'=>'Primary 800','primary_900'=>'Primary 900',
    'surface'=>'Superficie (cards)','background'=>'Fundo da pagina','text'=>'Texto principal','muted'=>'Texto secundario','border'=>'Bordas',
    'sidebar_surface'=>'Fundo da sidebar','sidebar_text'=>'Texto da sidebar','sidebar_muted'=>'Texto secundario sidebar',
    'sidebar_active_bg'=>'Fundo item ativo','sidebar_active_text'=>'Texto item ativo','sidebar_active_dot'=>'Ponto item ativo',
    'header_bg'=>'Fundo do cabecalho','header_border'=>'Borda do cabecalho',
    'success'=>'Sucesso','warning'=>'Atencao','danger'=>'Erro',
    'on_primary'=>'Texto sobre cor primária','on_success'=>'Texto sobre sucesso','on_warning'=>'Texto sobre atenção','on_danger'=>'Texto sobre erro',
    'shadow'=>'Cor base das sombras',
];
?>
<div class="main-content-pad">
    <div class="header-row"><div><div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div><h1 class="page-title">Temas</h1><div class="breadcrumb"><span>Início</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span>Admin</span><i class="fas fa-chevron-right breadcrumb-arrow"></i><span class="breadcrumb-active">Temas</span></div></div><div class="status-online"><span class="status-dot"></span> Conectado</div></div>
    <div style="display:grid;grid-template-columns:220px 1fr;gap:16px;align-items:start;">
        {{-- Sidebar temas --}}
        <div class="card" style="padding:8px;">
            <p class="section-title" style="font-size:11px;padding:3px 8px;">Temas</p>
            <div style="display:flex;flex-direction:column;gap:2px;">
                @foreach ($themes as $theme)
                    <button wire:click="selectTheme({{ $theme->id }})" style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;border:0;cursor:pointer;text-align:left;width:100%;font-size:13px;color:{{ $editing->id === $theme->id ? 'var(--primary-600)' : 'var(--text)' }};{{ $editing->id === $theme->id ? 'background:color-mix(in srgb,var(--primary-500)8%,transparent);font-weight:700;' : 'background:transparent;' }}" onmouseover="this.style.background='color-mix(in srgb,var(--text)3%,transparent)'" onmouseout="this.style.background='{{ $editing->id === $theme->id ? 'color-mix(in srgb,var(--primary-500)8%,transparent)' : 'transparent' }}'">
                        <span style="display:inline-block;width:16px;height:16px;border-radius:50%;border:1px solid var(--border);background-color:{{ $theme->tokens['primary_500'] }};flex-shrink:0;"></span>
                        {{ $theme->name }}
                        @if (!$theme->is_active)<span style="margin-left:auto;font-size:11px;color:var(--muted);">(inativo)</span>@endif
                    </button>
                @endforeach
            </div>
        </div>
        {{-- Form --}}
        <div>
            <form wire:submit="save" style="display:flex;flex-direction:column;gap:16px;">
                <div class="card">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                        <div style="flex:1;"><label style="font-size:13px;font-weight:600;display:block;margin-bottom:4px;color:var(--text);">Nome do tema</label><input wire:model="name" class="input-field" placeholder="Ex: Verde Operacional">@error('name')<p style="font-size:12px;color:var(--danger);margin-top:2px;">{{ $message }}</p>@enderror</div>
                        <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--text);cursor:pointer;"><input wire:model="is_active" type="checkbox" style="width:16px;height:16px;"> Ativo</label>
                    </div>
                </div>
                @foreach ($groups as $groupName => $keys)
                    <div class="card">
                        <div class="section-title" style="margin-bottom:12px;">{{ $groupName }}</div>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;">
                            @foreach ($keys as $key)
                                @php $val = $this->tokens[$key]; @endphp
                                <div>
                                    <label style="font-size:11px;color:var(--muted);font-weight:600;display:block;margin-bottom:4px;">{{ $labels[$key] ?? $key }}</label>
                                    <div style="display:flex;align-items:center;gap:6px;">
                                        <input type="color" value="{{ $val }}" style="width:36px;height:36px;border-radius:6px;border:1px solid var(--border);cursor:pointer;padding:2px;background:transparent;" wire:change="$set('tokens.{{ $key }}', $event.target.value)" x-on:input="document.documentElement.style.setProperty('--{{ str_replace('_', '-', $key) }}', $event.target.value)">
                                        <input wire:model.blur="tokens.{{ $key }}" class="input-field" style="font-family:monospace;font-size:12px;height:36px;" placeholder="#hex">
                                    </div>
                                    @error("tokens.{{ $key }}")<p style="font-size:11px;color:var(--danger);margin-top:2px;">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div style="display:flex;align-items:center;gap:8px;">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary"><span wire:loading.remove>Salvar e ativar este tema</span><span wire:loading>Salvando...</span></button>
                    @if (session('saved'))<span class="badge-success">Salvo e ativado!</span>@endif
                </div>
            </form>
        </div>
    </div>
</div>

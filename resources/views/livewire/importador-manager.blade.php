<div>
    <div x-data="{ show: @entangle('toastShow'), msg: @entangle('toastMsg') }"
         x-init="$watch('show', val => { if(val) setTimeout(() => show = false, 4000) })"
         x-show="show" class="toast-fixed" x-cloak>
        <span class="toast-icon"><i class="fas fa-check"></i></span><span x-text="msg"></span>
    </div>
    <div class="main-content-pad">
        <div class="header-row">
            <div>
                <div class="admin-badge"><i class="fas fa-crown"></i> ADMIN</div>
                <h1 class="page-title">Importador</h1>
                <div class="breadcrumb">
                    <span>Inicio</span><i class="fas fa-chevron-right breadcrumb-arrow"></i>
                    <span class="breadcrumb-active">Importador</span>
                </div>
            </div>
            <div class="status-online"><span class="status-dot"></span> Conectado</div>
        </div>

        {{-- Step indicator --}}
        <div style="display:flex;gap:4px;margin-bottom:20px;">
            <div style="flex:1;padding:10px;text-align:center;border-radius:8px;font-weight:800;font-size:12px;{{ $passo === 'upload' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);' }}">1. Upload</div>
            <div style="flex:1;padding:10px;text-align:center;border-radius:8px;font-weight:800;font-size:12px;{{ $passo === 'mapping' ? 'background:var(--primary-600);color:#fff;' : ($passo === 'upload' ? 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);' : 'background:color-mix(in srgb,var(--primary-500)10%,transparent);color:var(--primary-600);') }}">2. Mapear Colunas</div>
            <div style="flex:1;padding:10px;text-align:center;border-radius:8px;font-weight:800;font-size:12px;{{ $passo === 'processando' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);' }}">3. Importar</div>
            <div style="flex:1;padding:10px;text-align:center;border-radius:8px;font-weight:800;font-size:12px;{{ $passo === 'resultado' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--muted);' }}">4. Resultado</div>
        </div>

        @if ($passo === 'upload')
            {{-- STEP 1: UPLOAD --}}
            <div class="card" style="padding:40px;text-align:center;">
                <i class="fas fa-cloud-upload-alt" style="font-size:48px;color:var(--primary-500);display:block;margin-bottom:12px;"></i>
                <h3 style="margin:0 0 8px;font-size:18px;">Importar Dados</h3>
                <p style="color:var(--muted);font-size:13px;margin-bottom:20px;">Selecione o tipo de dados e faça upload do arquivo CSV</p>

                <div style="display:flex;justify-content:center;gap:8px;margin-bottom:20px;">
                    <button wire:click="$set('tipo', 'produtos')" style="padding:8px 20px;border-radius:8px;border:0;cursor:pointer;font-weight:700;font-size:13px;{{ $tipo === 'produtos' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--text);' }}"><i class="fas fa-box"></i> Produtos</button>
                    <button wire:click="$set('tipo', 'clientes')" style="padding:8px 20px;border-radius:8px;border:0;cursor:pointer;font-weight:700;font-size:13px;{{ $tipo === 'clientes' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--text);' }}"><i class="fas fa-users"></i> Clientes</button>
                    <button wire:click="$set('tipo', 'fornecedores')" style="padding:8px 20px;border-radius:8px;border:0;cursor:pointer;font-weight:700;font-size:13px;{{ $tipo === 'fornecedores' ? 'background:var(--primary-600);color:#fff;' : 'background:color-mix(in srgb,var(--text)5%,var(--surface));color:var(--text);' }}"><i class="fas fa-truck"></i> Fornecedores</button>
                </div>

                <div x-data="{ dragging: false }">
                    <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="
                        dragging = false;
                        if ($event.dataTransfer.files.length) {
                            let file = $event.dataTransfer.files[0];
                            if (file.type === 'text/csv' || file.name.endsWith('.csv') || file.name.endsWith('.txt')) {
                                $wire.upload('arquivo', file);
                            }
                        }
                    " style="border:3px dashed var(--border);border-radius:16px;padding:40px;cursor:pointer;transition:0.2s;" x-bind:style="dragging ? 'background:color-mix(in srgb,var(--primary-500)8%,transparent);border-color:var(--primary-500);' : ''">
                        <input type="file" wire:model="arquivo" accept=".csv,.txt" style="display:none;" id="fileInput" x-on:change="show">
                        <label for="fileInput" style="cursor:pointer;">
                            <i class="fas fa-file-csv" style="font-size:32px;color:var(--muted);display:block;margin-bottom:8px;"></i>
                            <span style="font-weight:700;color:var(--primary-600);">Clique para selecionar</span>
                            <span style="color:var(--muted);"> ou arraste o arquivo CSV aqui</span>
                        </label>
                    </div>
                </div>
                @error('arquivo')<div class="err" style="margin-top:8px;">{{ $message }}</div>@enderror

                <div style="margin-top:20px;text-align:left;font-size:12px;color:var(--muted);background:color-mix(in srgb,var(--text)3%,var(--surface));padding:16px;border-radius:8px;">
                    <strong style="color:var(--text);">Formato esperado:</strong>
                    @if ($tipo === 'produtos')
                        <p style="margin:4px 0 0;">CSV com cabecalho. Colunas: Nome, SKU, Marca, Categoria, Preco Venda, NCM, Unidade...</p>
                    @elseif ($tipo === 'clientes')
                        <p style="margin:4px 0 0;">CSV com cabecalho. Colunas: Nome, CPF, Email, WhatsApp, Logradouro, Cidade, UF...</p>
                    @else
                        <p style="margin:4px 0 0;">CSV com cabecalho. Colunas: Razao Social, CNPJ, Telefone, Email, Logradouro, Cidade...</p>
                    @endif
                    <p style="margin:4px 0 0;">Delimitador: <strong>auto</strong> (virgula ou ponto e virgula) | Tamanho max: 10MB</p>
                </div>
            </div>

        @elseif ($passo === 'mapping')
            {{-- STEP 2: MAPPING --}}
            <div class="card" style="padding:20px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="margin:0;font-size:16px;"><i class="fas fa-columns"></i> Mapear Colunas</h3>
                    <span style="font-size:13px;color:var(--muted);">{{ $totalLinhas }} linha(s) encontrada(s) | {{ count($cabecalhos) }} coluna(s)</span>
                </div>

                @error('colunas')<div class="err" style="margin-bottom:12px;">{{ $message }}</div>@enderror

                <div style="overflow-x:auto;margin-bottom:16px;">
                    <table class="data-table" style="font-size:12px;min-width:600px;">
                        <thead><tr><th class="data-table-th text-left">Campo no Sistema</th><th class="data-table-th text-left">Coluna do CSV</th></tr></thead>
                        <tbody>
                            @php $campos = $this->camposTipo(); @endphp
                            @foreach ($campos as $campo => $label)
                                @php $obrigatorio = str_contains($label, '*'); @endphp
                                <tr class="data-table-tr">
                                    <td class="data-table-td font-semibold" style="{{ $obrigatorio ? 'font-weight:800;' : '' }}">{{ $label }}</td>
                                    <td class="data-table-td">
                                        <select wire:model="colunas.{{ $campo }}" style="width:100%;max-width:300px;height:34px;padding:0 6px;border:1px solid var(--border);border-radius:5px;font-size:12px;">
                                            <option value="">— Nao importar —</option>
                                            @foreach ($cabecalhos as $idx => $header)
                                                <option value="{{ $idx }}">{{ $header }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <h4 style="font-size:13px;margin:0 0 8px;color:var(--text);">Preview (primeiras {{ count($preview) }} linhas)</h4>
                <div style="overflow-x:auto;margin-bottom:16px;">
                    <table class="data-table" style="font-size:11px;">
                        <thead><tr>@foreach ($cabecalhos as $h)<th class="data-table-th text-left">{{ $h }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($preview as $row)
                                <tr class="data-table-tr">
                                    @foreach ($cabecalhos as $idx => $h)
                                        <td class="data-table-td" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row[$idx] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="display:flex;gap:8px;">
                    <button wire:click="$set('passo', 'upload')" class="btn-secondary btn-lg" style="flex:1;">Voltar</button>
                    <button wire:click="iniciarImportacao" class="btn btn-primary btn-lg" style="flex:2;"><i class="fas fa-play"></i> Iniciar Importacao</button>
                </div>
            </div>

        @elseif ($passo === 'processando')
            {{-- STEP 3: PROCESSING --}}
            <div class="card" style="padding:40px;text-align:center;">
                <i class="fas fa-spinner fa-spin" style="font-size:48px;color:var(--primary-500);display:block;margin-bottom:16px;"></i>
                <h3 style="margin:0 0 8px;">Importando...</h3>
                <p style="color:var(--muted);margin-bottom:20px;">Lote {{ $lote }} | Processados: {{ $processados }}/{{ $totalLinhas }}</p>

                <div style="max-width:400px;margin:0 auto;height:12px;border-radius:6px;background:color-mix(in srgb,var(--text)8%,var(--surface));overflow:hidden;">
                    <div style="height:100%;border-radius:6px;background:var(--primary-600);width:{{ $totalLinhas > 0 ? min(100, ($processados / $totalLinhas) * 100) : 0 }}%;transition:width 0.3s;"></div>
                </div>

                <div style="display:flex;justify-content:center;gap:20px;margin-top:16px;">
                    <span style="color:var(--success);font-weight:700;"><i class="fas fa-check"></i> {{ $sucesso }}</span>
                    @if ($erros > 0)<span style="color:var(--danger);font-weight:700;"><i class="fas fa-times"></i> {{ $erros }}</span>@endif
                </div>

                <div wire:poll.500ms="processarLote" style="display:none;"></div>
            </div>

        @elseif ($passo === 'resultado')
            {{-- STEP 4: RESULTS --}}
            <div class="card" style="padding:40px;text-align:center;">
                <i class="fas {{ $erros === 0 ? 'fa-check-circle' : 'fa-exclamation-triangle' }}" style="font-size:48px;{{ $erros === 0 ? 'color:var(--success);' : 'color:var(--warning);' }}display:block;margin-bottom:12px;"></i>
                <h3 style="margin:0 0 8px;">{{ $erros === 0 ? 'Importacao concluida com sucesso!' : 'Importacao concluida com erros' }}</h3>
                <p style="color:var(--muted);margin-bottom:16px;">{{ $processados }} linha(s) processadas</p>

                <div style="display:flex;justify-content:center;gap:24px;margin-bottom:20px;">
                    <div><span style="display:block;font-size:28px;font-weight:900;color:var(--success);">{{ $sucesso }}</span><span style="font-size:12px;color:var(--muted);">Sucesso</span></div>
                    @if ($erros > 0)<div><span style="display:block;font-size:28px;font-weight:900;color:var(--danger);">{{ $erros }}</span><span style="font-size:12px;color:var(--muted);">Erros</span></div>@endif
                </div>

                @if (count($logErros) > 0)
                    <div style="text-align:left;max-height:300px;overflow-y:auto;border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:16px;">
                        <h4 style="margin:0 0 8px;font-size:13px;color:var(--danger);"><i class="fas fa-times-circle"></i> Erros:</h4>
                        @foreach ($logErros as $err)
                            <div style="font-size:11px;color:var(--danger);padding:3px 0;border-bottom:1px solid color-mix(in srgb,var(--danger)10%,transparent);">{{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <button wire:click="resetar" class="btn btn-primary btn-lg"><i class="fas fa-redo"></i> Nova Importacao</button>
            </div>
        @endif
    </div>
</div>

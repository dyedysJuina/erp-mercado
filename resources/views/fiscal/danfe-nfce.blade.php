<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>DANFE NFC-e #{{ $doc->numero }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #000; background: #fff; padding: 8px; }
        .cupom { max-width: 302px; margin: 0 auto; }
        .center { text-align: center; }
        .linha { border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th { padding: 3px 0; text-align: left; font-weight: 700; border-bottom: 1px solid #000; }
        td { padding: 2px 0; vertical-align: top; }
        .direita { text-align: right; }
        .centro { text-align: center; }
        .mono { font-family: 'Courier New', monospace; }
        .qrcode { text-align: center; margin: 8px 0; }
        .qrcode-placeholder { width: 110px; height: 110px; border: 2px solid #000; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; color: #666; }
        .chave { font-size: 9px; word-break: break-all; }
        .totals { margin-top: 4px; }
        .totals tr td { padding: 2px 0; font-weight: 700; }
        .totals .linha { border-top: 1px solid #000; margin: 2px 0; }
        @media print { @page { margin: 0; size: 80mm auto; } body { padding: 4px; } }
    </style>
</head>
<body>
    <div class="cupom">
        <div class="center">
            <strong style="font-size:13px;">{{ $loja->nome }}</strong><br>
            {{ $loja->logradouro ?? '' }}, {{ $loja->numero ?? 'S/N' }}<br>
            {{ $loja->bairro ?? '' }} - {{ $loja->cidade?->nome ?? $loja->cidade ?? '' }} / {{ $loja->cidade?->estado?->uf ?? $loja->uf ?? 'MT' }}<br>
            CNPJ: {{ $loja->cnpj }}<br>
            IE: {{ $loja->inscricao_estadual ?? 'ISENTO' }}
        </div>
        <div class="linha"></div>
        <div class="center">
            <strong>DANFE NFC-e</strong><br>
            Documento Auxiliar da Nota Fiscal Eletrônica<br>
            NFC-e - Modelo 65 - Série {{ $doc->serie }}
        </div>
        <div class="linha"></div>
        <table>
            <tr><td style="width:60px;"><strong>Número</strong></td><td>{{ $doc->numero }}</td></tr>
            <tr><td><strong>Data</strong></td><td>{{ $doc->emitida_at->format('d/m/Y H:i:s') }}</td></tr>
            <tr><td><strong>Consumidor</strong></td><td>{{ $doc->cliente?->nome ?? 'NÃO IDENTIFICADO' }}</td></tr>
            @if ($doc->cliente?->cpf)
                <tr><td><strong>CPF</strong></td><td>{{ $doc->cliente->cpf }}</td></tr>
            @endif
        </table>
        <div class="linha"></div>
        <table>
            <thead>
                <tr>
                    <th style="width:20px;">Cód.</th>
                    <th>Descrição</th>
                    <th style="width:30px;" class="direita">Qtd</th>
                    <th style="width:50px;" class="direita">Valor</th>
                    <th style="width:50px;" class="direita">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $nItem = 0; @endphp
                @foreach ($doc->itens as $i)
                    @php
                        $nItem++;
                        $gtin = $i->variacao?->codigosBarras?->firstWhere('principal', true)?->codigo ?? '';
                        $nome = $i->variacao?->nome_completo ?? 'Produto #' . $i->produto_variacao_id;
                    @endphp
                    <tr>
                        <td style="font-size:9px;">{{ $nItem }}</td>
                        <td>
                            <span style="font-weight:600;">{{ $nome }}</span>
                            @if ($gtin)
                                <span style="display:block;font-size:8px;color:#666;">GTIN: {{ $gtin }}</span>
                            @endif
                        </td>
                        <td class="direita">{{ number_format($i->quantidade, 3, ',', '.') }}</td>
                        <td class="direita">{{ number_format($i->valor_unitario, 2, ',', '.') }}</td>
                        <td class="direita">{{ number_format($i->valor_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="linha"></div>
        <table class="totals">
            <tr><td style="font-weight:700;font-size:11px;">Valor Total</td><td class="direita" style="font-weight:900;font-size:14px;">R$ {{ number_format($doc->valor_total, 2, ',', '.') }}</td></tr>
        </table>
        <div class="linha"></div>
        <div class="center chave">
            <strong style="font-size:10px;">Chave de Acesso</strong><br>
            {{ $doc->chave_acesso }}<br>
            <small style="font-size:8px;">
                @if ($doc->status === 'rascunho')
                    NFC-e em RASCUNHO - sem valor fiscal
                @else
                    Consulte pela Chave de Acesso em www.sefaz.mt.gov.br
                @endif
            </small>
        </div>
        <div class="linha"></div>
        <div class="qrcode">
            @if ($doc->status === 'rascunho')
                <div class="qrcode-placeholder">QR Code<br>(disponível após<br>autorização)</div>
            @else
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ $doc->chave_acesso }}" alt="QR Code NFC-e" style="width:110px;height:110px;">
            @endif
        </div>
        <div class="center" style="font-size:8px;">
            <strong>ERP Mercado</strong> — {{ $doc->created_at->format('d/m/Y H:i') }}
        </div>
        <div style="text-align:center;margin-top:8px;font-size:9px;font-weight:700;">
            OBRIGADO E VOLTE SEMPRE!
        </div>
    </div>
    <script>
        @if ($doc->status !== 'rascunho')
            window.print();
        @endif
    </script>
</body>
</html>

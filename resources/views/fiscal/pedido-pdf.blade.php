<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',monospace; font-size:12px; color:#000; background:#fff; padding:20px; }
        .header { text-align:center; margin-bottom:16px; }
        .header h1 { font-size:18px; }
        .header p { font-size:11px; color:#555; }
        .linha { border-top:1px dashed #000; margin:8px 0; }
        table { width:100%; border-collapse:collapse; font-size:11px; }
        th { text-align:left; padding:6px 4px; font-size:10px; border-bottom:1px solid #000; text-transform:uppercase; }
        td { padding:4px; border-bottom:1px solid #ddd; }
        .right { text-align:right; }
        .center { text-align:center; }
        .bold { font-weight:700; }
        .info-table td { border:0; padding:2px 4px; font-size:11px; }
        .totals td { border-top:2px solid #000; border-bottom:0; font-weight:700; font-size:13px; padding:6px 4px; }
        .footer { text-align:center; margin-top:16px; font-size:10px; color:#888; }
        @media print { @page { margin:10mm; } body { padding:0; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEDIDO DE COMPRA</h1>
        <p>Pedido #{{ $pedido->id }}</p>
    </div>
    <div class="linha"></div>

    <table class="info-table">
        <tr><td style="width:120px;"><strong>Fornecedor:</strong></td><td>{{ $pedido->fornecedor->razao_social ?? '—' }}</td></tr>
        <tr><td><strong>CNPJ/CPF:</strong></td><td>{{ $pedido->fornecedor->cnpj_cpf ?? '—' }}</td></tr>
        <tr><td><strong>Data:</strong></td><td>{{ $pedido->created_at->format('d/m/Y') }}</td></tr>
        <tr><td><strong>Previsão:</strong></td><td>{{ $pedido->previsao_entrega ? \Carbon\Carbon::parse($pedido->previsao_entrega)->format('d/m/Y') : '—' }}</td></tr>
        <tr><td><strong>Condição:</strong></td><td>{{ $pedido->condicao_pagamento ?? '—' }}</td></tr>
        <tr><td><strong>Frete:</strong></td><td>{{ $pedido->tipo_frete ?? '—' }}</td></tr>
    </table>

    <div class="linha"></div>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">#</th>
                <th>Produto</th>
                <th style="width:50px;" class="center">Qtd</th>
                <th style="width:80px;" class="right">Valor Un.</th>
                <th style="width:80px;" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pedido->itens as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->variacao?->nome_completo ?? '#' . $item->produto_variacao_id }}</td>
                    <td class="center">{{ number_format($item->quantidade_pedida, 3, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->custo_unitario, 2, ',', '.') }}</td>
                    <td class="right">R$ {{ number_format($item->total_item, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="linha"></div>

    <table>
        <tr><td style="width:80%;text-align:right;font-weight:700;font-size:12px;">Total Produtos:</td><td style="text-align:right;font-weight:700;font-size:12px;">R$ {{ number_format($pedido->total_produtos, 2, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;font-weight:700;">Frete:</td><td style="text-align:right;">R$ {{ number_format($pedido->valor_frete, 2, ',', '.') }}</td></tr>
        <tr><td style="text-align:right;font-weight:700;">Desconto:</td><td style="text-align:right;color:#c00;">- R$ {{ number_format($pedido->valor_desconto, 2, ',', '.') }}</td></tr>
        <tr class="totals"><td style="text-align:right;font-size:16px;">TOTAL DO PEDIDO:</td><td style="text-align:right;font-size:16px;">R$ {{ number_format($pedido->total_pedido, 2, ',', '.') }}</td></tr>
    </table>

    @if ($pedido->observacoes)
        <div class="linha"></div>
        <p><strong>Observações:</strong><br>{{ $pedido->observacoes }}</p>
    @endif

    <div class="linha"></div>
    <div class="footer">
        <p>ERP Mercado — Pedido gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Status: <strong>{{ $pedido->status }}</strong></p>
    </div>
    <script>window.print();</script>
</body>
</html>

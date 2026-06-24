<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas</title>
    <style>
        @page { margin: 8mm; size: A4; }
        body { font-family: 'Courier New', monospace; margin: 0; padding: 0; }
        .page { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4mm; }
        .label {
            border: 1px dashed #ccc;
            padding: 4mm;
            text-align: center;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 50mm;
        }
        .label .nome { font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 2mm; line-height: 1.2; }
        .label .preco { font-size: 22px; font-weight: 900; color: #c00; margin: 2mm 0; }
        .label .preco-label { font-size: 8px; color: #666; }
        .label .codigo { font-size: 18px; font-weight: 300; letter-spacing: 2px; margin: 2mm 0; font-family: 'Courier New', monospace; }
        .label .sku { font-size: 8px; color: #999; margin-top: 1mm; }
        .label .marca { font-size: 8px; color: #666; }
        .label .validade { font-size: 7px; color: #999; margin-top: 1mm; }
        .no-print { text-align: center; margin: 10mm 0; }
        .no-print button { padding: 10px 30px; font-size: 16px; cursor: pointer; }
        @media print {
            .no-print { display: none; }
            .label { border: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir Etiquetas</button>
        <button onclick="window.close()">Fechar</button>
    </div>
    <div class="page">
        @foreach ($produtos as $p)
            @for ($i = 0; $i < $qtd; $i++)
                @php
                    $preco = isset($precos[$p->id]) ? (float)$precos[$p->id]->{$campo} : 0;
                    $codigo = $p->codigosBarras?->firstWhere('principal', true)?->codigo ?? $p->sku ?? '';
                    $marca = $p->marca?->nome ?? '';
                @endphp
                <div class="label">
                    <div class="marca">{{ $marca }}</div>
                    <div class="nome">{{ $p->nome_completo }}</div>
                    <div class="preco-label">Preco {{ $campo === 'preco_atacado' ? 'Atacado' : 'Venda' }}</div>
                    <div class="preco">R$ {{ number_format($preco, 2, ',', '.') }}</div>
                    @if ($codigo)
                        <div class="codigo">{{ $codigo }}</div>
                    @endif
                    <div class="sku">SKU: {{ $p->sku ?? '-' }}</div>
                </div>
            @endfor
        @endforeach
    </div>
</body>
</html>

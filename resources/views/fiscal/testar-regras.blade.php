<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Testar Regras Fiscais</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:system-ui,sans-serif; background:#f1f5f9; padding:30px; color:#0f172a; }
        .container { max-width:900px; margin:0 auto; }
        h1 { font-size:24px; margin-bottom:6px; }
        p { color:#64748b; margin-bottom:20px; font-size:14px; }
        .card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; padding:20px; margin-bottom:16px; }
        input, select { width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; outline:none; }
        input:focus, select:focus { border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,0.1); }
        .grid { display:grid; gap:12px; }
        .grid-2 { grid-template-columns:1fr 1fr; }
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:700; }
        .badge-500 { background:#fef2f2; color:#dc2626; }
        .badge-103 { background:#f0fdf4; color:#16a34a; }
        .badge-102 { background:#fafafa; color:#64748b; }
        .badge-300 { background:#eff6ff; color:#2563eb; }
        .tabela { width:100%; border-collapse:collapse; font-size:13px; }
        .tabela th { text-align:left; padding:8px 10px; font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; border-bottom:2px solid #e2e8f0; }
        .tabela td { padding:8px 10px; border-bottom:1px solid #f1f5f9; }
        .tabela tr:hover td { background:#f8fafc; }
        .resultado { margin-top:12px; padding:12px; border-radius:8px; font-size:14px; font-weight:600; display:none; }
        .resultado.show { display:block; }
        .resultado-ok { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
        .resultado-info { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-flask"></i> Testar Regras Fiscais</h1>
        <p>Selecione um NCM abaixo para ver qual CSOSN será sugerido automaticamente.</p>

        <div class="card">
            <h3 style="margin-bottom:8px;">🔍 Testar por NCM</h3>
            <div class="grid grid-2">
                <div>
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">NCM</label>
                    <input type="text" id="ncmInput" placeholder="Ex: 22021000" maxlength="8" style="font-family:monospace;font-size:16px;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Resultado</label>
                    <div id="resultBox" class="resultado">Selecione um NCM para testar</div>
                </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:6px;flex-wrap:wrap;">
                <span style="font-size:12px;color:#64748b;">Testes rápidos:</span>
                <button onclick="testarNcm('22021000')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">22021000 (Refri)</button>
                <button onclick="testarNcm('10063000')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">10063000 (Arroz)</button>
                <button onclick="testarNcm('04011010')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">04011010 (Leite)</button>
                <button onclick="testarNcm('34011100')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">34011100 (Sabão)</button>
                <button onclick="testarNcm('33041000')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">33041000 (Batom)</button>
                <button onclick="testarNcm('07020000')" style="padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;background:#fff;cursor:pointer;font-size:12px;">07020000 (Tomate)</button>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom:12px;">📋 Todas as regras cadastradas</h3>
            <table class="tabela">
                <thead>
                    <tr>
                        <th>Prefixo NCM</th>
                        <th>CSOSN</th>
                        <th>Descrição</th>
                        <th>Exemplo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($regras as $regra)
                        @php
                            $exemplos = [
                                '22xx' => 'Refrigerante, Cerveja',
                                '33xx' => 'Perfumes, Cosméticos',
                                '34xx' => 'Sabão, Detergente',
                                '24xx' => 'Cigarros',
                                '27xx' => 'Combustíveis',
                                '07xx' => 'Alface, Tomate',
                                '08xx' => 'Maçã, Banana',
                                '1006' => 'Arroz',
                                '1101' => 'Farinha de trigo',
                                '0201' => 'Carne bovina',
                                '0207' => 'Frango',
                                '0407' => 'Ovos',
                                '0901' => 'Café em grão',
                            ];
                            $exemplo = $exemplos[$regra->ncm_prefix] ?? '';
                        @endphp
                        <tr>
                            <td style="font-family:monospace;font-weight:700;">{{ $regra->ncm_prefix }}</td>
                            <td>
                                @if ($regra->csosn === '500')
                                    <span class="badge badge-500">500 (ST)</span>
                                @elseif ($regra->csosn === '103')
                                    <span class="badge badge-103">{{ $regra->csosn }} (Isento)</span>
                                @elseif ($regra->csosn === '300')
                                    <span class="badge badge-300">{{ $regra->csosn }} (Imune)</span>
                                @else
                                    <span class="badge badge-102">{{ $regra->csosn }} (Tributado)</span>
                                @endif
                            </td>
                            <td>{{ $regra->descricao }}</td>
                            <td style="color:#64748b;font-size:12px;">{{ $exemplo }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const ncmDb = {
        '22021000': { nome: 'Refrigerante', regra: '500 (Substituição Tributária)', desc: 'Bebidas não alcoólicas' },
        '10063000': { nome: 'Arroz polido', regra: '103 (Isento)', desc: 'Arroz' },
        '04011010': { nome: 'Leite UHT integral', regra: '102 (Tributado SN)', desc: 'Laticínios' },
        '17019900': { nome: 'Açúcar refinado', regra: '102 (Tributado SN)', desc: 'Açúcares' },
        '09011100': { nome: 'Café verde', regra: '103 (Isento)', desc: 'Café' },
        '34011100': { nome: 'Sabão em barra', regra: '500 (ST)', desc: 'Produtos de limpeza' },
        '33041000': { nome: 'Batom', regra: '500 (ST)', desc: 'Cosméticos' },
        '07020000': { nome: 'Tomate', regra: '103 (Isento)', desc: 'Hortaliças' },
    };

    function testarNcm(codigo) {
        document.getElementById('ncmInput').value = codigo;
        const box = document.getElementById('resultBox');
        const dados = ncmDb[codigo];

        fetch('/admin/testar-regras-api?ncm=' + codigo)
            .then(r => r.json())
            .then(res => {
                box.className = 'resultado show resultado-ok';
                box.innerHTML = `
                    <strong>NCM ${codigo}</strong> — ${dados ? dados.nome : 'Produto'}<br>
                    <span style="font-size:20px;font-weight:900;display:block;margin-top:6px;">
                        CSOSN <strong>${res.csosn}</strong>
                        ${res.csosn === '500' ? '🔴 ST' : res.csosn === '103' ? '🟢 Isento' : '⚪ Tributado'}
                    </span>
                    <span style="display:block;margin-top:4px;font-size:12px;color:#64748b;">
                        Regra aplicada: "${res.regra}"<br>
                        Origem: <strong>${res.origem}</strong>
                    </span>
                `;
            })
            .catch(() => {
                box.className = 'resultado show resultado-info';
                box.innerHTML = 'Erro ao consultar. Verifique o NCM digitado.';
            });
    }

    document.getElementById('ncmInput').addEventListener('input', function() {
        const val = this.value.replace(/\D/g, '');
        if (val.length >= 4) testarNcm(val);
    });
    </script>
</body>
</html>

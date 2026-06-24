<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acesso Negado</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: #0f172a; color: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            min-height: 100dvh; padding: 20px;
        }
        .container { text-align: center; max-width: 420px; }
        .code {
            font-size: 96px; font-weight: 900; line-height: 1;
            background: linear-gradient(135deg, #ef4444, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .lock { font-size: 40px; margin-bottom: 16px; color: #ef4444; }
        h1 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
        p { font-size: 14px; color: #94a3b8; line-height: 1.6; margin-bottom: 32px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 28px; border-radius: 10px; font-size: 14px; font-weight: 700;
            text-decoration: none; cursor: pointer; border: 0;
            background: #22c55e; color: #fff; transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.9; }
        .btn-voltar {
            background: transparent; color: #94a3b8; border: 1px solid #334155;
            margin-left: 8px;
        }
        .btn-voltar:hover { background: #1e293b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="lock">🔒</div>
        <div class="code">403</div>
        <h1>Acesso Negado</h1>
        <p>Você não tem permissão para acessar esta página.<br>Entre em contato com o administrador do sistema.</p>
        <div>
            <a href="{{ url()->previous() ?? '/' }}" class="btn-voltar">← Voltar</a>
            <a href="/" class="btn">🏠 Ir para o Início</a>
        </div>
    </div>
</body>
</html>

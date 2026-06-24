<!doctype html>
<html>
<head>
    <title>Setup - ERP Mercado</title>
    <style>
        body { font-family: monospace; padding: 40px; background: #0f172a; color: #e2e8f0; }
        .container { max-width: 600px; margin: 0 auto; text-align: center; }
        h1 { color: #22c55e; }
        .btn {
            display: inline-block; padding: 14px 32px; background: #22c55e; color: #0f172a;
            font-weight: 700; border: none; border-radius: 10px; font-size: 16px; cursor: pointer;
            text-decoration: none; margin-top: 20px;
        }
        .btn:hover { background: #16a34a; }
        .warn { background: #1e293b; border-radius: 10px; padding: 16px; margin: 20px 0; font-size: 14px; text-align: left; line-height: 1.6; }
        .warn strong { color: #f59e0b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>ERP Mercado - Setup</h1>
        <div class="warn">
            <strong>ATENCAO:</strong> Esta pagina executa migrations e seeders.<br>
            Certifique-se de que o banco de dados ja foi criado no cPanel<br>
            e que o arquivo .env esta configurado corretamente.
        </div>
        <a href="{{ url('/setup/run') }}" class="btn">EXECUTAR SETUP</a>
    </div>
</body>
</html>

# Deploy para HostGator - farejaofertas.com.br

## Arquivos

- `C:\temp\farejaofertas.zip` — Projeto sem vendor (~24MB)
- `.env.production` — Configuracao de producao

---

## Passo 1 - Upload via cPanel

1. Acesse o cPanel da HostGator
2. Va em **File Manager**
3. Entre na pasta `/farejaofertas.com.br/`
4. Clique em **Upload** e selecione `C:\temp\farejaofertas.zip`
5. Apos o upload, clique com botao direito no `.zip` e **Extract**
6. Delete o `.zip` apos extrair

---

## Passo 2 - Banco de Dados

1. No cPanel, va em **MySQL Databases**
2. Confirme que o banco `erp_supermercado` existe
3. Confirme que o usuario `ronand54_dyedys` esta vinculado ao banco

---

## Passo 3 - Configurar .env

1. No File Manager, va ate `/farejaofertas.com.br/.env.production`
2. Renomeie `.env.production` para `.env` (ou copie o conteudo)
3. O arquivo ja esta configurado com os dados do banco:

```
APP_URL=https://farejaofertas.com.br
DB_DATABASE=erp_supermercado
DB_USERNAME=ronand54_dyedys
DB_PASSWORD=147852
```

---

## Passo 4 - Instalar dependencias (VENDOR)

**Opcao A - Via cPanel (recomendado):**

1. No cPanel, va em **Setup PHP Composer**
2. Selecione a pasta `/farejaofertas.com.br`
3. Clique em **Install / Update**
4. Aguarde instalar todas as dependencias

**Opcao B - Via SSH (se tiver acesso):**

```bash
cd ~/farejaofertas.com.br
composer install --optimize-autoloader --no-dev
```

---

## Passo 5 - Permissoes

1. No File Manager, clique com botao direito na pasta `storage` e va em **Change Permissions**
2. Marque todas as permissoes (755) e clique em OK
3. Faca o mesmo para `bootstrap/cache`
4. Nas pastas `storage/logs`, `storage/framework/cache`, `storage/framework/sessions`, `storage/framework/views`:
   - Permissao **755** ou **777**

---

## Passo 6 - PHP Version

1. No cPanel, va em **Select PHP Version**
2. Escolha **PHP 8.3** ou superior
3. Habilite as extensoes: `bcmath`, `curl`, `gd`, `intl`, `json`, `mbstring`, `mysqlnd`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`

---

## Passo 7 - Rodar Setup

1. Acesse no navegador: `https://farejaofertas.com.br/setup`
2. Clique em **EXECUTAR SETUP**
3. Isso vai rodar as migrations, seeders e otimizacoes

---

## Passo 8 - Finalizar

1. Acesse `https://farejaofertas.com.br/login`
2. Faca login com: **admin@erp** / **admin** (ou o usuario padrao)
3. Se tudo funcionar, remova o acesso a rota `/setup` editando `routes/web.php` e comentando as 2 linhas do setup.

---

## Problemas comuns

| Problema | Solucao |
|---|---|
| Tela branca (500) | Verificar se o .env foi renomeado corretamente |
| Erro de conexao com banco | Verificar usuario/senha no .env |
| "No application key" | Rodar setup novamente ou adicionar `APP_KEY=base64:Ar5m9as2tbSPYd4PTNlfb45UBjbcRir59gcINWJYEqM=` no .env |
| CSS/JS nao carregam | Rodar `npx vite build` local e reenviar a pasta `public/build/` |

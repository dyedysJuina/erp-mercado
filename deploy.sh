#!/bin/bash
# Deploy script for farejaofertas.com.br
# Uso: bash deploy.sh
# 
# Fluxo padrao:
# 1. Atualiza codigo do Git
# 2. Instala dependencias (composer)
# 3. Roda migrations
# 4. Limpa e recria caches
# 5. (Opcional) Remove arquivos indesejados

echo "=== DEPLOY INICIADO: $(date) ==="

cd /home1/ronand54/farejaofertas.com.br || { echo "ERRO: diretorio nao encontrado"; exit 1; }

# 1. Git pull (verifica se ha mudancas locais)
echo "[1/5] Atualizando codigo do Git..."
/usr/bin/git pull
if [ $? -ne 0 ]; then
    echo "AVISO: git pull falhou. Tentando limpar mudancas locais..."
    /usr/bin/git checkout -- composer.lock
    /usr/bin/git clean -fd -- composer.lock
    /usr/bin/git pull
fi

# 2. Remove .env.production se existir (causa erro de DB)
if [ -f .env.production ]; then
    echo "[WARN] Removendo .env.production..."
    rm -f .env.production
fi

# 3. Composer install
echo "[2/5] Instalando dependencias..."
/opt/cpanel/ea-php84/root/usr/bin/php composer install --no-dev --optimize-autoloader
if [ $? -ne 0 ]; then
    echo "AVISO: composer install falhou. Tentando composer update..."
    /opt/cpanel/ea-php84/root/usr/bin/php composer update --no-dev
fi

# 4. Migrations
echo "[3/5] Rodando migrations..."
/opt/cpanel/ea-php84/root/usr/bin/php artisan migrate --force

# 5. Cache
echo "[4/5] Limpando e recriando caches..."
/opt/cpanel/ea-php84/root/usr/bin/php artisan optimize:clear
/opt/cpanel/ea-php84/root/usr/bin/php artisan optimize

echo "[5/5] Deploy finalizado com sucesso!"
echo "=== DEPLOY CONCLUIDO: $(date) ==="

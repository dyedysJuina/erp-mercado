<?php

$environmentPath = dirname(__DIR__).'/.env';

if (! file_exists($environmentPath)) {
    fwrite(STDERR, "Arquivo .env não encontrado.\n");
    exit(1);
}

$environment = file_get_contents($environmentPath);

if (preg_match('/^APP_KEY=.+$/m', $environment) === 1) {
    fwrite(STDOUT, "APP_KEY já configurada; nenhuma chave foi alterada.\n");
    exit(0);
}

$artisan = escapeshellarg(dirname(__DIR__).'/artisan');
passthru(PHP_BINARY." {$artisan} key:generate --ansi", $exitCode);

exit($exitCode);

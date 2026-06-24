# Fundação do ERP Mercado

## Stack adotada

- Laravel 13 e PHP 8.3.
- Livewire 4 para telas reativas e formulários sem recarregamento.
- Tailwind CSS 4 para o sistema visual.
- Alpine somente para interações locais pequenas.
- Spatie Permission para papéis e permissões já existentes no banco.
- MySQL 8.4 usando o banco `erp_supermercado`.

## Regras de segurança do banco

1. Nunca executar `migrate:fresh`, `db:wipe` ou `migrate:reset` no banco existente.
2. Toda alteração estrutural exige backup, migration revisada e teste de rollback.
3. O comando `composer setup` não executa migrations automaticamente.
4. Testes automatizados usam SQLite isolado e nunca o banco real.
5. O esquema estabilizado está registrado em `database/schema/mysql-schema.sql`.
6. Chaves estrangeiras só podem ser adicionadas após auditoria de órfãos e regras de exclusão.

## Padrão dos módulos

Cada módulo deve possuir modelo, regras de domínio, componente Livewire, autorização e testes. Um módulo só serve de modelo para o próximo quando seus fluxos principais estiverem cobertos e validados no navegador.

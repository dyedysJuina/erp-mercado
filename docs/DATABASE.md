# Estabilização do banco `erp_supermercado`

Data da estabilização: 20 de junho de 2026.

## Estado validado

- 101 tabelas convertidas de MyISAM para InnoDB.
- 101 tabelas convertidas de utf8mb3 para utf8mb4 (`utf8mb4_unicode_ci`).
- 2 views preservadas e consultáveis.
- 132 registros comparados entre origem e cópia de ensaio, sem divergências.
- `CHECK TABLE` aprovado para todas as tabelas.
- Aplicação Laravel/Livewire validada com resposta HTTP 200.

## Proteções e referências

- Backup: `C:\wamp64\backups\erp_mercado\erp_supermercado_20260620_162136.sql`.
- SHA-256: `3C627304AE7786658CCE6E651149E38A06CBA82A17DC9653D257F3DA2D8E14B4`.
- Cópia de ensaio: `erp_supermercado_migracao`.
- Referência estrutural: `database/schema/mysql-schema.sql`.

## Chaves estrangeiras

Adicionadas em 20 de junho de 2026.

### Processo
1. Análise manual dos relacionamentos implícitos em 137 colunas.
2. Verificação de registros órfãos — **zero órfãos encontrados**.
3. Definição de regras por domínio:
   - `CASCADE` para itens-filho (ex: `pedidos_itens.pedido_id`)
   - `RESTRICT` para referências de segurança (ex: `produtos_base.categoria_id`)
   - `SET NULL` para logs e auditoria (ex: `auditoria_logs.usuario_id`)
4. Backup antes da migração: `C:\wamp64\backups\erp_mercado\erp_supermercado_fks_before_20260620_174927.sql`
5. 97 FKs criadas em 59 tabelas via migration Laravel.
6. Aplicação validada: HTTP 200 em todas as rotas.

### FKs que não foram criadas (colunas com nomes diferentes no banco real)
Algumas colunas mapeadas no schema de referência não existem no banco real (divergência entre `meu_mercado` e `erp_supermercado`). Essas serão adicionadas conforme os módulos forem construídos.

### Regras utilizadas
| Regra | Uso |
|-------|-----|
| `CASCADE` | Itens-filho, relações hierárquicas |
| `RESTRICT` | Integridade crítica (categoria, fornecedor, etc.) |
| `SET NULL` | Logs, usuário opcional, deleção suave |

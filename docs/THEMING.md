# Temas dinâmicos

As cores do sistema têm uma única fonte de verdade: a coluna JSON `tokens` da tabela `themes`.

## Fluxo

1. O painel salva os tokens no banco.
2. `ThemeService` gera as variáveis CSS para todos os temas ativos.
3. O layout carrega o CSS estrutural e, depois, os tokens do banco.
4. A seleção é persistida em `users.theme`.
5. Prévia e salvamento atualizam as variáveis no navegador sem recarregar.
6. No próximo carregamento, o tema salvo pelo usuário é aplicado novamente.

## Regras

- Não definir cores hexadecimais, RGB ou classes de paleta diretamente nas views e folhas de estilo.
- Componentes devem consumir tokens como `var(--primary-600)`, `var(--surface)`, `var(--text)` e `var(--danger)`.
- Para texto sobre fundos coloridos, usar `--on-primary`, `--on-success`, `--on-warning` e `--on-danger`.
- Novos temas devem ser cadastrados na tabela `themes`; o serviço não possui lista fixa de slugs.
- Após qualquer alteração direta nos temas, limpar o cache da aplicação.

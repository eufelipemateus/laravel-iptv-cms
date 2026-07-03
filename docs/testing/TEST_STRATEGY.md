# Estrategia de testes

Ferramentas usadas:

- PHPUnit 11, via `vendor/bin/phpunit`.
- Laravel HTTP testing, console testing e `RefreshDatabase`.
- SQLite em memoria configurado no `phpunit.xml`.
- Pint para formatacao/verificacao dos arquivos tocados.
- Nenhuma dependencia nova foi instalada.

Escopo aplicado:

- Testes unitarios para regras de dominio: actions, models/scopes, configuracoes e calculo financeiro.
- Testes de feature para todos os fluxos web atuais do site: dashboard, config, canais, grupos, CDNs, URLs, planos, clientes, adicionais, faturas e playlists.
- Testes de middleware para Basic Auth de cliente, CDN publica e locale.
- Testes de integracao de schema e constraints principais.

Decisoes importantes:

- Auth administrativa nao foi implementada nesta rodada. Ela esta documentada como limitacao conhecida porque sera feita posteriormente.
- Basic Auth foi mantido e testado somente no acesso do cliente a playlist privada M3U.
- O fluxo de pagamento real nao existe instalado na branch ativa; a tela de pagamento foi testada sem gateway externo real.
- VOD, auditoria, processador C++ e HLS nao existem nesta branch; portanto foram inventariados como ausentes.
- Paratest, PHPStan, Infection e outras ferramentas nao foram adicionadas para respeitar a restricao de nao instalar dependencias novas.

Como executar:

```bash
vendor/bin/phpunit
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Integration
vendor/bin/pint --test tests database/factories app/Services
```

Observacao: `php artisan test` nao esta disponivel neste projeto no ambiente atual; a suite usa `vendor/bin/phpunit`.

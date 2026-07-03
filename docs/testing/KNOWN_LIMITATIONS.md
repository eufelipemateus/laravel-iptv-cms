# Limitacoes conhecidas

- Auth administrativa sera implementada posteriormente. As rotas administrativas nao foram protegidas nesta rodada por decisao explicita de escopo.
- `php artisan test` nao existe no app atual; os testes rodam via `vendor/bin/phpunit`.
- Coverage nao esta disponivel por falta de driver (`No code coverage driver available`).
- O PHP CLI emite warnings de extensoes no startup: `xdebug.so` ausente e tentativa de carregar `openssl` por caminho invalido.
- MySQL integration suite nao foi executada; a suite atual usa SQLite em memoria.
- Testes paralelos nao foram adicionados porque nao sera instalada dependencia nova.
- PHPStan/Larastan, Infection, Dusk/Playwright e ferramentas similares nao foram adicionadas para respeitar o escopo atual.
- VOD, auditoria, usuarios administrativos, webhooks, processamento C++ e HLS nao existem na branch ativa; ficam fora da cobertura ate surgirem no codigo.
- Gateways de pagamento sao opcionais e nao estao instalados localmente; a tela de pagamento foi testada com lista vazia de gateways.

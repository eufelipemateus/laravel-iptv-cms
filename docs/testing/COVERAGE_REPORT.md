# Relatorio de cobertura

Comandos executados:

```bash
vendor/bin/phpunit
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Feature
vendor/bin/phpunit --testsuite=Integration
vendor/bin/phpunit --coverage-text --colors=never
vendor/bin/pint --test ...
php artisan migrate:fresh --env=testing
```

Resultados:

- PHPUnit completo: OK, 46 testes, 273 assertions.
- Unit: OK, 16 testes, 70 assertions.
- Feature: OK, 27 testes, 188 assertions.
- Integration: OK, 3 testes, 15 assertions.
- Migrations SQLite/testing: OK.
- Pint: OK em modo formatacao e `--test`.

Cobertura percentual:

- Nao disponivel neste ambiente.
- O comando de coverage executou a suite, mas terminou com warning do runner: `No code coverage driver available`.
- O PHP tambem emite warnings de startup para `xdebug.so` e `openssl`, embora `openssl` apareca carregado em `php -m`. Isso deve ser corrigido no PHP local/CI para habilitar coverage real.

Sem driver de cobertura, nao ha numero honesto de linhas/branches a reportar. A suite foi desenhada por comportamento e regressao, nao por meta artificial de percentual.

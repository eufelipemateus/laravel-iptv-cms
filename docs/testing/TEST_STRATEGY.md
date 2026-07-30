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

## Testes do modo m3u8

- playlists publicas continuam funcionando.
- playlists individuais continuam funcionando.
- download de playlists continua funcionando.
- URLs antigas continuam validas.
- endpoints `/api/v1/tv/*` retornam `404`.
- aplicacao TV 3.0 nao fica acessivel.
- menus da TV 3.0 nao aparecem.

## Testes do modo dtv3

- API TV 3.0 funciona.
- bootstrap funciona.
- catalogo funciona.
- ativacao de dispositivos funciona.
- reproducao e heartbeat funcionam.
- `/public/m3u8/{slug}` retorna `404`.
- `/client/m3u8/{slug}` retorna `404`.
- downloads M3U8 retornam `404`.
- URLs antigas de playlists retornam `404`.
- menus e botoes de M3U8 nao aparecem.
- os dados M3U8 permanecem armazenados no banco.

## Testes de troca de modo

- instalacoes antigas recebem `m3u8` como modo padrao.
- a troca de modo nao apaga dados.
- a troca invalida os caches.
- a troca entra em vigor imediatamente.
- voltar para `m3u8` restaura o acesso as playlists.
- voltar para `dtv3` restaura o acesso a API TV 3.0.
- nunca e possivel ativar os dois modos.
- valores diferentes de `m3u8` e `dtv3` sao rejeitados.

## Nao fazer

- permitir que `m3u8` e `dtv3` funcionem simultaneamente.
- deixar rotas do modo inativo acessiveis.
- retornar `403` para uma funcionalidade de modo desativado.
- redirecionar uma rota de M3U8 para a TV 3.0.
- redirecionar uma rota da TV 3.0 para uma playlist.
- apagar dados ao trocar de modo.
- duplicar a configuracao `mode` em diferentes tabelas.
- verificar o modo usando strings espalhadas pelo codigo.
- depender somente da ocultacao de menus.
- manter downloads M3U8 acessiveis no modo `dtv3`.

## Criterios de aceitacao

- existir uma configuracao `mode`.
- somente `m3u8` ou `dtv3` puder ser selecionado.
- o modo padrao for `m3u8`.
- os dois modos nunca funcionarem simultaneamente.
- a troca de modo preservar todos os dados.
- no modo `m3u8`, todas as playlists existentes funcionarem.
- no modo `m3u8`, todas as rotas TV 3.0 retornarem `404`.
- no modo `dtv3`, toda a API TV 3.0 funcionar.
- no modo `dtv3`, todas as rotas M3U/M3U8 retornarem `404`.
- downloads M3U8 ficarem indisponiveis no modo `dtv3`.
- menus do modo inativo nao aparecerem.
- jobs e comandos respeitarem o modo ativo.
- caches forem invalidados apos a troca.
- o banco existente puder ser migrado sem perda de dados.
- testes automatizados cobrirem os dois modos.
- a documentacao explicar claramente a exclusividade dos modos.

# Bugs encontrados

## Inadimplencia consultava faturas de todos os clientes

- Severidade: alta
- Componente: `Customer::defeated`
- Observado: uma fatura vencida de outro cliente marcava qualquer cliente como inadimplente.
- Esperado: consultar apenas faturas do cliente atual.
- Teste: `CustomerDefeatedTest::test_overdue_invoice_from_another_customer_does_not_mark_customer_as_defeated`
- Correcao: consultas passaram a usar `$this->customer_invoce()`.

## Token de playlist era previsivel

- Severidade: alta
- Componente: `StoreCustomerAction`, `UpdateCustomerAction`
- Observado: `hash_acess` era `md5(now())`.
- Esperado: token imprevisivel.
- Teste: `DomainActionsTest::test_customer_hash_is_cryptographically_shaped_and_regenerates`
- Correcao: uso de `Str::random(64)`.

## Middleware de cliente usava header/echo/exit e `$_SERVER`

- Severidade: media
- Componente: `CustomerMiddleware`
- Observado: respostas nao testaveis e acoplamento a superglobal.
- Esperado: respostas Laravel com status/header.
- Teste: `CustomerMiddlewareTest`
- Correcao: uso de `Request::getUser()`, `Request::getPassword()`, suporte temporario a query `user/pass` e responses Laravel.

## Faturas tinham unique global por data

- Severidade: alta
- Componente: migration `iptv_customer_invoces`
- Observado: clientes diferentes nao poderiam ter a mesma data de vencimento.
- Esperado: duplicidade bloqueada apenas por cliente/data.
- Teste: `SchemaAndConstraintTest`
- Correcao: unique composto `iptv_customer_id` + `duedate_at`.

## Comando mensal gravava coluna incorreta e nao era idempotente

- Severidade: alta
- Componente: `GenerateInvoces`
- Observado: gravava `customer_id`, nao `iptv_customer_id`, e poderia duplicar faturas.
- Esperado: chave correta e idempotencia por periodo.
- Teste: `GenerateInvocesCommandTest`
- Correcao: `firstOrCreate` com `iptv_customer_id` e data calculada por `Date::now()`.

## Planos adicionais podiam duplicar no pivot

- Severidade: media
- Componente: `AddCustomerPlanAdditionalAction`, migration pivot
- Observado: salvar duas vezes o mesmo adicional gerava duplicidade.
- Esperado: idempotencia.
- Teste: `DomainActionsTest::test_additional_plan_action_is_idempotent_and_requires_additional_plan`
- Correcao: `syncWithoutDetaching` e unique composto.

## Grupo de outro plano podia ser removido ou apropriado indevidamente

- Severidade: alta
- Componente: Customer plan groups
- Observado: remocao buscava o grupo globalmente; add nao bloqueava grupo ja vinculado a outro plano.
- Esperado: operacoes escopadas ao plano da URL.
- Teste: `DomainActionsTest::test_group_plan_actions_do_not_remove_or_steal_other_plan_groups`
- Correcao: validacao ao adicionar e busca por `$plan->groups()`.

## Playlist privada aceitava slug de CDN que nao pertencia ao cliente

- Severidade: alta
- Componente: `CustomerChannelsM3UController`
- Observado: cliente autenticado poderia pedir playlist de outro slug.
- Esperado: 404 para CDN fora do cliente.
- Teste: `PrivatePlaylistTest::test_private_playlist_rejects_cdn_slug_that_does_not_belong_to_customer`
- Correcao: comparacao entre `customer->cdn->slug` e slug solicitado.

## Valores booleanos `"false"` viravam `true`

- Severidade: media
- Componente: `IPTVConfig::castValue`
- Observado: `boolval("false") === true`.
- Esperado: `"false"` deve ser falso.
- Teste: `IPTVConfigTest`, `PublicCdnAndLocaleMiddlewareTest`
- Correcao: `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.

## View de nova fatura apontava para namespace antigo

- Severidade: media
- Componente: `customer_invoce.blade.php` / `InvoceController`
- Observado: `IPTV::customer_invoce`/`IPTV::app` geravam erro sem namespace.
- Esperado: views locais.
- Teste: `CrudFlowTest`
- Correcao: views locais `customer_invoce` e `app`.

## Mutator de logo quebrava com string ja persistida

- Severidade: media
- Componente: `Channel::setLogoAttribute`
- Observado: factories/updates com caminho string chamavam `guessExtension()`.
- Esperado: strings devem ser preservadas como caminho.
- Teste: varias factories e `CrudFlowTest`
- Correcao: early return para `null` ou `string`.

## URLs de stream aceitavam CRLF

- Severidade: media
- Componente: `ChannelUrlRequest`
- Observado: CRLF podia corromper a playlist.
- Esperado: URL valida sem `\r`/`\n`.
- Teste: `FormRequestValidationTest`, `PublicPlaylistTest`
- Correcao: regra `url` e `not_regex`.

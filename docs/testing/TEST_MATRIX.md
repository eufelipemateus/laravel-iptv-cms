# Matriz de testes

| Area | Testes implementados | Arquivos principais |
| --- | --- | --- |
| Ambiente de teste | SQLite em memoria, suites Unit/Feature/Integration, exemplos removidos | `phpunit.xml` |
| Factories | User existente + Channel, ChannelGroup, ChannelCdn, ChannelUrl, Customer, CustomerPlan, CustomerInvoce, IPTVConfig, IPTVTaxVat | `database/factories/*Factory.php` |
| Actions | CRUD e regras de pivots, token de cliente, cancelamento, persistencia | `tests/Unit/Actions/DomainActionsTest.php` |
| Models | Relacoes, scopes M3U, inadimplencia isolada por cliente, config casting | `tests/Unit/Models/*Test.php` |
| Services | Calculo de fatura principal/adicional/imposto | `tests/Unit/Services/InvoiceCalculatorTest.php` |
| Controllers web | Fluxos CRUD do site atual, redirects, views e metodo destrutivo via POST | `tests/Feature/Controllers/CrudFlowTest.php` |
| Form Requests | Planos validos, email, URLs/CRLF, IDOR de fatura, deletes inexistentes | `tests/Feature/Requests/FormRequestValidationTest.php` |
| Middlewares | Basic Auth de cliente, inativo, inadimplente, CDN publica, locale/fallback | `tests/Feature/Middleware/*Test.php` |
| Playlists | Publica e privada, headers, conteudo M3U, isolamento de CDN/cliente, injecao CRLF | `tests/Feature/Playlists/*Test.php` |
| Comandos | Geracao mensal de faturas, somente clientes ativos, idempotencia | `tests/Feature/Commands/GenerateInvocesCommandTest.php` |
| Banco | Tabelas/colunas, faturas mesma data em clientes diferentes, unique composto por cliente/data | `tests/Integration/Database/SchemaAndConstraintTest.php` |
| Seguranca | Admin auth registrado como futuro, deletes por GET nao removem, IDOR fatura, hash imprevisivel, playlist sem CRLF | varios |

Resumo atual:

- Antes: 2 testes de exemplo.
- Depois: 46 testes reais.
- Assertions: 273.
- Suites: Unit 16 testes, Feature 27 testes, Integration 3 testes.

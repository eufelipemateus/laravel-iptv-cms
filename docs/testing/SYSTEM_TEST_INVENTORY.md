# Inventario de testes do sistema

Base analisada:

- Branch: `develop`
- Commit: `8ed00e0ab47054d0fafd82f6337dd81baf8c18a3`
- Comparacao: a branch ativa e `develop`; contra `master` ha diferencas relevantes em Actions, Form Requests, controllers, middlewares e bootstrap. A branch local foi tratada como fonte da verdade.
- Auth administrativa: fora do escopo desta rodada por decisao do produto. As rotas web administrativas permanecem com `web`, `iptv_locale` e `throttle:web`. O Basic Auth continua restrito ao acesso de cliente na playlist M3U.

## Modulos encontrados

- Encontrados: Actions, Form Requests, middlewares, comandos Artisan, models Eloquent, dashboards/cards, menu JSON, playlists publica e privada, configuracoes, faturas, planos, canais, grupos, CDNs, URLs de stream e clientes.
- Ausentes na branch ativa: Services preexistentes, DTOs, Policies, VOD, auditoria, gestao administrativa de usuarios, integracao real de pagamento instalada, processador C++, HLS/jobs de midia, webhooks e filas de dominio.
- Service adicionado: `App\Services\Invoces\InvoiceCalculator`, para isolar calculo financeiro antes embutido no controller.

## Rotas

| Rota | Metodo | Nome | Middleware | Controller | Request | Action/Service | Models/tabelas | View/redirect | Testes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| `/` | ANY | - | web | RedirectController | - | - | - | `/dashboard` | `CrudFlowTest` |
| `/dashboard` | GET | `dashboard` | web, iptv_locale, throttle:web | DashboardController@view | - | Dashboard helper/cards | dashboards | `dashboard` | `CrudFlowTest` |
| `/iptv/config` | GET | `config` | web, iptv_locale, throttle:web | ConfigController@config | - | IPTVConfig | `iptv_configs` | `config` | `CrudFlowTest`, `IPTVConfigTest` |
| `/iptv/config` | POST | `config_save` | web, iptv_locale, throttle:web, CSRF | ConfigController@configSave | UpdateConfigRequest | UpdateConfigAction | `iptv_configs` | redirect `config` | `CrudFlowTest` |
| `/channel/list` | GET | `list_channel` | web, iptv_locale, throttle:web | ChannelController@list | - | Channel::getList | `iptv_channels`, groups | `channel_list` | `CrudFlowTest`, `RelationshipsTest` |
| `/channel/add` | GET | `add_channel` | web, iptv_locale, throttle:web | ChannelController@new | - | IPTVConfig | groups/config | `channel` | `CrudFlowTest` |
| `/channel/add` | POST | `create_channel` | web, CSRF | ChannelController@create | StoreChannelRequest | StoreChannelAction | `iptv_channels`, logo file | redirect list | `CrudFlowTest`, `DomainActionsTest` |
| `/channel/{id}` | GET | `show_channel` | web | ChannelController@show | - | - | channels, groups, cdns, urls | `channel` | `CrudFlowTest` |
| `/channel/{id}` | POST | `update_channel` | web, CSRF | ChannelController@update | UpdateChannelRequest | UpdateChannelAction | `iptv_channels`, logo optional | redirect list | `CrudFlowTest`, `DomainActionsTest` |
| `/channel/del/{id}` | POST | `delete_channel` | web, CSRF | ChannelController@delete | DeleteChannelRequest | DeleteChannelAction | `iptv_channels` | redirect list | `CrudFlowTest`, `FormRequestValidationTest` |
| `/group/list` | GET | `list_channel_group` | web | ChannelGroupController@list | - | - | `iptv_channel_groups` | `channel_group_list` | `CrudFlowTest` |
| `/group/add` | GET/POST | add/create group | web, CSRF on POST | ChannelGroupController | ChannelGroupRequest | StoreChannelGroupAction | `iptv_channel_groups` | view/redirect | `CrudFlowTest`, `DomainActionsTest` |
| `/group/{id}` | GET/POST | show/update group | web, CSRF on POST | ChannelGroupController | ChannelGroupRequest | UpdateChannelGroupAction | groups | view/redirect | `CrudFlowTest` |
| `/group/del/{id}` | POST | `delete_channel_group` | web, CSRF | ChannelGroupController@delete | DeleteChannelGroupRequest | DeleteChannelGroupAction | groups | redirect | `CrudFlowTest` |
| `/cdn/list` | GET | `list_channel_cdn` | web | ChannelCdnController@list | - | IPTVConfig | cdns/config | `channel_cdn_list` | `CrudFlowTest` |
| `/cdn/add` | GET/POST | add/create cdn | web, CSRF on POST | ChannelCdnController | StoreChannelCdnRequest | StoreChannelCdnAction | `iptv_cdns` | view/redirect | `CrudFlowTest` |
| `/cdn/{id}` | GET/POST | show/update cdn | web, CSRF on POST | ChannelCdnController | UpdateChannelCdnRequest | UpdateChannelCdnAction | cdns | view/redirect | `CrudFlowTest` |
| `/cdn/del/{id}` | POST | `delete_channel_cdn` | web, CSRF | ChannelCdnController@delete | DeleteChannelCdnRequest | DeleteChannelCdnAction | cdns | redirect | `CrudFlowTest` |
| `/url/add` | POST | `create_channel_url` | web, CSRF | ChannelUrlController@create | ChannelUrlRequest | StoreChannelUrlAction | `iptv_urls` | redirect channel | `CrudFlowTest`, `FormRequestValidationTest` |
| `/url/{id}` | POST | `update_channel_url` | web, CSRF | ChannelUrlController@update | ChannelUrlRequest | UpdateChannelUrlAction | urls | redirect channel | `CrudFlowTest` |
| `/url/del/{id}` | POST | `delete_channel_url` | web, CSRF | ChannelUrlController@delete | DeleteChannelUrlRequest | DeleteChannelUrlAction | urls | redirect channel | `CrudFlowTest` |
| `/public/m3u8/{slug}` | GET | `cdn-playslit` | api, public_cdn | ChannelListM3UController@show | ChannelListM3URequest | Channel::getListM3u8 | cdns, urls, channels, groups | `list_M3U`, text/plain | `PublicPlaylistTest` |
| `/client/m3u8/{slug}` | GET | `client-playlist` | api, client | CustomerChannelsM3UController@show | CustomerChannelsM3URequest | Channel::getCustomerChannelListM3u8 | customers, cdns, plans, additionals | `list_M3U`, text/plain | `PrivatePlaylistTest`, `CustomerMiddlewareTest` |
| `/plan/list` | GET | `list_customer_plan` | web | CustomerPlanController@list | - | - | `iptv_plans` | `customer_plan_list` | `CrudFlowTest` |
| `/plan/add` | GET/POST | add/create plan | web, CSRF on POST | CustomerPlanController | CustomerPlanRequest | StoreCustomerPlanAction | plans, optional tax | view/redirect | `CrudFlowTest`, `FormRequestValidationTest` |
| `/plan/{id}` | GET/POST | show/update plan | web, CSRF on POST | CustomerPlanController | CustomerPlanRequest | UpdateCustomerPlanAction | plans/groups/tax | view/redirect | `CrudFlowTest` |
| `/plan/del/{id}` | POST | `delete_customer_plan` | web, CSRF | CustomerPlanController@delete | DeleteCustomerPlanRequest | DeleteCustomerPlanAction | plans | redirect | `CrudFlowTest` |
| `/plan/{plan_id}/group/add` | POST | `add_group_customer_plan` | web, CSRF | CustomerPlanGroupController@add | CustomerPlanGroupRequest | AddChannelGroupToCustomerPlanAction | plans/groups | redirect plan | `CrudFlowTest`, `DomainActionsTest` |
| `/plan/{plan_id}/group/delete` | POST | `delete_group_customer_plan` | web, CSRF | CustomerPlanGroupController@delete | CustomerPlanGroupRequest | RemoveChannelGroupFromCustomerPlanAction | plans/groups | redirect plan | `CrudFlowTest`, `DomainActionsTest` |
| `/customer/list` | GET | `list_customer` | web | CustomerController@list | - | Customer::getList | customers | `customer_list` | `CrudFlowTest` |
| `/customer/add` | GET/POST | add/create customer | web, CSRF on POST | CustomerController | StoreCustomerRequest | StoreCustomerAction | customers, plans, cdns | view/redirect show | `CrudFlowTest`, `FormRequestValidationTest`, `DomainActionsTest` |
| `/customer/{id}` | GET/POST | show/update customer | web, CSRF on POST | CustomerController | UpdateCustomerRequest | UpdateCustomerAction | customers, plans, cdns, invoices | view/redirect | `CrudFlowTest` |
| `/customer/del/{id}` | POST | `delete_customer` | web, CSRF | CustomerController@delete | DeleteCustomerRequest | DeleteCustomerAction | customers | redirect | `CrudFlowTest` |
| `/customer/{customer_id}/plan_additional/add` | POST | `add_additional` | web, CSRF | CustomerPlanAdditionalController@add | CustomerPlanAdditionalRequest | AddCustomerPlanAdditionalAction | pivot additionals | redirect customer | `CrudFlowTest`, `DomainActionsTest` |
| `/customer/{customer_id}/plan_additional/del` | POST | `del_additional` | web, CSRF | CustomerPlanAdditionalController@del | CustomerPlanAdditionalRequest | RemoveCustomerPlanAdditionalAction | pivot additionals | redirect customer | `CrudFlowTest`, `DomainActionsTest` |
| `/customer/{customer_id}/invoces/new` | GET/POST | new/create invoice | web, CSRF on POST | InvoceController | IPTVCustomerInvoceCreateInvoceRequest | StoreCustomerInvoceAction | invoices | view/redirect | `CrudFlowTest`, `GenerateInvocesCommandTest` |
| `/customer/{customer_id}/invoces/{id}/pay` | POST | `pay_customer_invoce` | web, CSRF | InvoceController@pay | PayCustomerInvoceRequest | InvoiceCalculator | invoices, customer, plans | `invoce` | `CrudFlowTest`, `InvoiceCalculatorTest`, `FormRequestValidationTest` |
| `/customer/{customer_id}/invoces/{id}/cancel` | POST | `cancel_customer_invoce` | web, CSRF | InvoceController@cancel | CancelCustomerInvoceRequest | CancelCustomerInvoceAction | invoices | redirect customer | `CrudFlowTest`, `FormRequestValidationTest` |
| `/api/user` | GET | - | auth:sanctum | closure | - | Sanctum | users | JSON | `AdminAuthenticationScopeTest` |
| `/up` | GET | - | framework | health closure | - | - | - | health | `AdminAuthenticationScopeTest` |

## Classes de app

- Controllers: todos os controllers de dominio possuem teste HTTP de caracterizacao.
- Actions: actions de canais, grupos, CDNs, URLs, clientes, planos, grupos de plano, adicionais e faturas cobertas por `DomainActionsTest`.
- Form Requests: cobertos por fluxo HTTP e por validacoes criticas em `FormRequestValidationTest`; autorizacao administrativa permanece futura.
- Models: relacoes, fillable/scopes relevantes, inadimplencia, config e invoice accessors cobertos.
- Middlewares: `CustomerMiddleware`, `PublicCdnMiddleware` e `IPTVLocaleMiddleware` cobertos.
- Commands: `invoce:month` coberto.
- Helpers/facades/providers: exercitados indiretamente por views/dashboard/config; providers sem logica de dominio nao receberam teste dedicado.
- Facades, Exceptions e middlewares framework-like nao receberam teste direto por serem wrappers ou extensoes padrao sem regra propria.

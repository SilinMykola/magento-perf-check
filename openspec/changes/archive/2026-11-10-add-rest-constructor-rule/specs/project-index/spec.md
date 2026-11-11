## MODIFIED Requirements

### Requirement: Indexed facts
The index SHALL provide at least:
- **DI:** per class, the constructor arguments configured with an object type ending in `\Proxy`, in any area, including through virtual types; the plugin classes declared in `di.xml`; and the `preference` elements of the global, `webapi_rest` and `webapi_soap` areas, including `app/etc/di.xml`.
- **Events:** observer classes per event and area.
- **Mview:** view IDs declared in `mview.xml`.
- **Schema:** table names that have a primary key constraint in any `db_schema.xml`.
- **Web API:** the service class and method of every route in `webapi.xml`.

#### Scenario: Plugin class known
- **WHEN** a `di.xml` declares `<plugin name="p" type="Acme\Promo\Plugin\CartPlugin"/>`
- **THEN** the index reports `Acme\Promo\Plugin\CartPlugin` as a plugin class

#### Scenario: Area-specific proxy
- **WHEN** only `etc/frontend/di.xml` configures the Proxy
- **THEN** the index records the Proxy for that class argument

#### Scenario: Web API preference
- **WHEN** `etc/webapi_rest/di.xml` declares a preference and `etc/frontend/di.xml` another one for the same type
- **THEN** the index records the `webapi_rest` preference and not the frontend one

#### Scenario: Web API route
- **WHEN** a `webapi.xml` route has `<service class="Acme\Promo\Api\CouponManagementInterface" method="apply"/>`
- **THEN** the index records `apply` as a web API method of that class

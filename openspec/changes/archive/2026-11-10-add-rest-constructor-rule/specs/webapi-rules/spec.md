## ADDED Requirements

### Requirement: P-API-01 field that only an unsupported constructor parameter could set
P-API-01 SHALL report, as an experimental `warning`, a constructor parameter of a class built for a web API input type when the core gives that parameter no request value (its type is neither simple nor an `Api\Data` type) and the input type has a getter for the field but no setter. The message SHALL name the field, the input type, the class, the parameter type and the missing setter.

#### Scenario: Sort order without a setter
- **WHEN** `SearchRequest` is built for `SearchRequestInterface`, has `?array $sort` documented as `SortOrder[]|null` where `SortOrder` is not in an `Api\Data` namespace, and the interface declares `getSort()` but no `setSort()`
- **THEN** P-API-01 reports the `$sort` parameter

#### Scenario: Setter on the interface
- **WHEN** the same interface also declares `setSort()`
- **THEN** P-API-01 reports nothing

#### Scenario: Setter only on the class
- **WHEN** `SearchRequest` declares `setSort()` and the interface does not
- **THEN** P-API-01 reports the `$sort` parameter

#### Scenario: Supported types
- **WHEN** the parameter is `string`, `int[]` by its doc type, or an `Api\Data` interface
- **THEN** P-API-01 reports nothing

#### Scenario: Field not in the type
- **WHEN** the input type has no getter for the parameter's field
- **THEN** P-API-01 reports nothing

### Requirement: Web API input types and the classes built for them
An input type SHALL be a type reachable from a service method parameter of a `webapi.xml` route, directly or through getter return types. The class built for it SHALL be the end of its `di.xml` preference chain in the global, `webapi_rest` or `webapi_soap` area, or the type itself when it has no preference. When the project index is empty, P-API-01 SHALL report nothing.

#### Scenario: Nested input type
- **WHEN** the service parameter type has a getter returning another data interface, and the class built for that interface has an unsupported parameter for a field without a setter
- **THEN** P-API-01 reports that parameter

#### Scenario: Output only
- **WHEN** a data interface appears only as a service method's return type
- **THEN** P-API-01 reports nothing for the class built for it

#### Scenario: No project configuration
- **WHEN** the project index is empty
- **THEN** P-API-01 reports nothing

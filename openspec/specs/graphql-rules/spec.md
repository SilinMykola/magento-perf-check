# graphql-rules Specification

## Purpose
Rules on GraphQL schema files (`.graphqls`) that catch declarations which make GraphQL responses uncacheable.

## Requirements

### Requirement: X-GQL-01 @cache without cacheIdentity
X-GQL-01 SHALL report, as `error`, a field of type `Query` (declared or extended) with a `@cache` directive that has neither a `cacheIdentity` argument nor `cacheable: false`. One such field makes the whole response that contains it uncacheable.

#### Scenario: Missing identity
- **WHEN** `schema.graphqls` declares `extend type Query { promoBanners: [Banner] @cache(cacheTag: "promo") }`
- **THEN** X-GQL-01 reports an error on that field's line

#### Scenario: Identity present
- **WHEN** the field has `@cache(cacheIdentity: "Acme\\Promo\\Model\\Resolver\\Identity")`
- **THEN** X-GQL-01 reports nothing

#### Scenario: Deliberately uncacheable
- **WHEN** the field has `@cache(cacheable: false)`
- **THEN** X-GQL-01 reports nothing

#### Scenario: Non-Query type
- **WHEN** a field of type `Mutation` or of an object type has `@cache` without identity
- **THEN** X-GQL-01 reports nothing

### Requirement: Unparsable schema files
A `.graphqls` file that cannot be parsed SHALL be skipped and recorded as an analysis notice, like unparsable XML.

#### Scenario: Syntax error
- **WHEN** a schema file has a syntax error
- **THEN** the summary counts one analysis notice naming that file

### Requirement: X-GQL-03 cache id factor
X-GQL-03 SHALL report, as `info`, each `<item>` of the `idFactorProviders` argument of `Magento\GraphQlCache\Model\CacheId\CacheIdCalculator` in a `di.xml` outside the `Magento_*` modules, whose factors are needed for correct content.

#### Scenario: Factor added
- **WHEN** `etc/graphql/di.xml` adds a provider to `idFactorProviders`
- **THEN** X-GQL-03 reports an info finding that names the item

#### Scenario: Core factor
- **WHEN** a `Magento_*` module adds a provider to `idFactorProviders`
- **THEN** X-GQL-03 reports nothing

#### Scenario: Same argument on another class
- **WHEN** another class has an `idFactorProviders` argument
- **THEN** X-GQL-03 reports nothing

### Requirement: Magento schema dialect
Schema parsing SHALL accept what Magento's `GraphQlReader` accepts: `/* */` and `//` comments between definitions, and `extends` before a type keyword. Comments inside a definition, which Magento rejects, SHALL be accepted as well, because reporting them is not this tool's job. Line numbers SHALL stay those of the file.

#### Scenario: License block and line comment
- **WHEN** `schema.graphqls` starts with `// @codingStandardsIgnoreFile` and a `/** */` block, and a field below has `@cache` without identity
- **THEN** no `graphql` notice is produced and X-GQL-01 reports the field on its line in the file

#### Scenario: A URL in a description
- **WHEN** a `@doc(description: "see https://example.com/*")` contains comment characters
- **THEN** the description is not changed

### Requirement: X-GQL-02 query in a resolver on a list type
X-GQL-02 SHALL report, as `warning`, a database query made in a class implementing `ResolverInterface` that is the `@resolver` of a field on a type reachable from `Query` or `Mutation` through a list, in any module of the project, also through a virtual type. A type in a list also lists what the fields of its interfaces return. A query is `load()` on a model or a flat or EAV resource model, `get()` or `getById()` on a repository, `getList()` with search criteria on a repository, running a database collection, including `foreach`, and a fetch on a database adapter. It is off by default.

#### Scenario: Field on a core list type
- **WHEN** a module adds `interface ProductInterface { brand: String @resolver(class: "Acme\\Brand") }` and `Brand::resolve()` calls `$this->brandRepository->getById($id)`
- **THEN** X-GQL-02 reports the call

#### Scenario: Collection in a foreach
- **WHEN** such a resolver iterates a database collection with `foreach`
- **THEN** X-GQL-02 reports the `foreach`

#### Scenario: Resolver without a query
- **WHEN** such a resolver only reads `$value` or calls a method of another service
- **THEN** X-GQL-02 reports nothing

#### Scenario: Batch resolver
- **WHEN** the class implements `BatchResolverInterface` or `BatchServiceContractResolverInterface`
- **THEN** X-GQL-02 reports nothing

#### Scenario: Deferred query
- **WHEN** the query is inside a closure or an arrow function, such as one passed to `ValueFactory::create()`
- **THEN** X-GQL-02 reports nothing

#### Scenario: Closure as a loop body
- **WHEN** the query is inside a callback of `array_map()`
- **THEN** X-GQL-02 reports the call

#### Scenario: Constructor
- **WHEN** the query is in the constructor of the resolver
- **THEN** X-GQL-02 reports nothing

#### Scenario: One query, one finding
- **WHEN** a resolver has `foreach ($collection->load() as $row)`
- **THEN** X-GQL-02 reports `load()` and not the `foreach` as well

#### Scenario: Type outside a list
- **WHEN** the field is on a type reached from `Query` only through non-list fields
- **THEN** X-GQL-02 reports nothing

#### Scenario: Interface field used through a list of its implementer
- **WHEN** a field is declared on an interface and only a type implementing it is returned in a list
- **THEN** X-GQL-02 reports queries of its resolver

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

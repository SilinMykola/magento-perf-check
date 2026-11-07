## ADDED Requirements

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

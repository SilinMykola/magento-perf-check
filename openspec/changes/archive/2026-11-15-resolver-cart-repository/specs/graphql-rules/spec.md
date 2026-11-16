## MODIFIED Requirements

### Requirement: X-GQL-02 query in a resolver on a list type
X-GQL-02 SHALL report, as `warning`, a database query made in a class implementing `ResolverInterface` that is the `@resolver` of a field on a type reachable from `Query` or `Mutation` through a list, in any module of the project, also through a virtual type. A type in a list also lists what the fields of its interfaces return. A query is `load()` on a model or a flat or EAV resource model, `get()` or `getById()` on a repository, `getList()` with search criteria on a repository, running a database collection, including `foreach`, and a fetch on a database adapter. `get()` on `Magento\Quote\Api\CartRepositoryInterface` whose argument is `getQuoteId()` of a `Magento\Quote\Api\Data\CartItemInterface` SHALL NOT count: the items of a list in a cart share the cart, which the cart query loaded and `QuoteRepository` keeps by id. It is off by default.

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

#### Scenario: Cart of a cart item
- **WHEN** the resolver of a field on cart items calls `$this->cartRepository->get($cartItem->getQuoteId())`
- **THEN** X-GQL-02 reports nothing
- **WHEN** it calls `get()` with an order's quote id, or any other id
- **THEN** X-GQL-02 reports the call

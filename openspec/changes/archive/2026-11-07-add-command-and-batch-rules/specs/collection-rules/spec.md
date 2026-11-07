## ADDED Requirements

### Requirement: P-COL-04 unpaged collection in a batch job
P-COL-04 SHALL report, as `warning`, a `foreach` over a database collection, or over its `getItems()`, in a class listed in `crontab.xml` or extending the console `Command`, when the same function created the collection with `create()` on another object, no page size or limit was set earlier in the function, and it has no equality filter on a unique key. It is off by default.

#### Scenario: Cron job over a whole collection
- **WHEN** a class in `crontab.xml` iterates `$this->factory->create()->addFieldToFilter('status', 'new')`
- **THEN** P-COL-04 reports the `foreach`

#### Scenario: Paged
- **WHEN** `setPageSize()` is called on the collection before the `foreach`
- **THEN** P-COL-04 reports nothing

#### Scenario: Collection from elsewhere
- **WHEN** the collection is a parameter, the result of a method of this class such as `$this->getCollection()`, or of a method of what the function created, such as `$order->getItemsCollection()`
- **THEN** P-COL-04 reports nothing

#### Scenario: Request code
- **WHEN** a controller iterates a collection it created
- **THEN** P-COL-04 reports nothing

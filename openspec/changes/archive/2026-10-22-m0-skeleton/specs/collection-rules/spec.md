# Spec Delta

## Purpose

Rules on Magento collections and database fetches that catch code loading far more rows than it uses.

## ADDED Requirements

### Requirement: P-COL-02 getFirstItem without limit
P-COL-02 SHALL report a call to `getFirstItem()` on an object whose type is a database collection (a subtype of `Magento\Framework\Data\Collection\AbstractDb`) when no limit is set. A limit is set when `setPageSize()` or `getSelect()->limit()` is called on the same collection, either earlier in the same method call chain or in an earlier statement of the same function or method. Severity: `error`.

#### Scenario: No limit
- **WHEN** code calls `$collection->addFieldToFilter('sku', $sku)->getFirstItem()` and `$collection` is a product collection
- **THEN** P-COL-02 reports an error on that line

#### Scenario: Limit in chain
- **WHEN** code calls `$collection->setPageSize(1)->getFirstItem()`
- **THEN** P-COL-02 reports nothing

#### Scenario: Limit earlier in method
- **WHEN** `$collection->setPageSize(1);` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Limit through select
- **WHEN** `$collection->getSelect()->limit(1);` precedes `$collection->getFirstItem();` in the same method
- **THEN** P-COL-02 reports nothing

#### Scenario: Collection from factory
- **WHEN** the receiver is `$this->collectionFactory->create()` and PHPStan resolves its return type to a database collection
- **THEN** P-COL-02 reports the call when no limit is set in the chain

### Requirement: P-COL-02 scope
P-COL-02 SHALL NOT report `getFirstItem()` on:
- Collections that are not database collections (in-memory `Magento\Framework\Data\Collection`).
- Receivers whose type PHPStan cannot resolve.
- Methods named `getFirstItem` on unrelated classes.

#### Scenario: In-memory collection
- **WHEN** `getFirstItem()` is called on a `Magento\Framework\Data\Collection` that is not an `AbstractDb`
- **THEN** P-COL-02 reports nothing

#### Scenario: Unknown type
- **WHEN** the receiver's type is `mixed` or an unknown class
- **THEN** P-COL-02 reports nothing

#### Scenario: Unrelated class
- **WHEN** a class that is not a collection declares and calls its own `getFirstItem()`
- **THEN** P-COL-02 reports nothing

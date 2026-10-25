# Spec Delta

## MODIFIED Requirements

### Requirement: X-EVT-01 observer on load_after
X-EVT-01 SHALL report, as `warning`, an observer on `model_load_after` or on any event ending in `_load_after`, except:
- events ending in `_collection_load_after`, which fire once per collection, not per entity;
- observers whose class is in the `Magento\Framework\EntityManager\Observer` namespace, which is core plumbing that adds no query.

The message SHALL say that `model_load_after` runs after loading any model, that `core_collection_abstract_load_after` runs after loading any collection, and that `<prefix>_load_after` runs on every load of that entity type.

#### Scenario: Generic load event
- **WHEN** `events.xml` has `<event name="model_load_after"><observer .../></event>`
- **THEN** X-EVT-01 reports a warning that names every model load

#### Scenario: Entity load event
- **WHEN** `events.xml` observes `catalog_product_load_after`
- **THEN** X-EVT-01 reports a warning that names product loads

#### Scenario: Collection load event
- **WHEN** `events.xml` observes `catalog_product_collection_load_after`
- **THEN** X-EVT-01 reports nothing

#### Scenario: EntityManager plumbing
- **WHEN** an observer with instance `Magento\Framework\EntityManager\Observer\AfterEntityLoad` is declared on `magento_catalog_api_data_productinterface_load_after`
- **THEN** X-EVT-01 reports nothing

#### Scenario: Any collection load
- **WHEN** `events.xml` observes `core_collection_abstract_load_after`
- **THEN** X-EVT-01 reports a warning that names every collection load, not one entity type

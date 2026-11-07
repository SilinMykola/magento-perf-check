# event-rules Specification

## Purpose
Rules on `events.xml` that catch observers subscribed to events which fire far more often than the observer's purpose needs.

## Requirements

### Requirement: X-EVT-01 observer on load_after
X-EVT-01 SHALL report, as `warning`, an observer on `model_load_after` or on any event ending in `_load_after`, except:
- events ending in `_collection_load_after`, which fire once per collection, not per entity;
- observers whose class is in the `Magento\Framework\EntityManager\Observer` namespace, which is core plumbing that adds no query.

The message SHALL say that `model_load_after` runs after loading any model, and that `<prefix>_load_after` runs on every load of that entity type.

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

### Requirement: X-EVT-02 observer on generic model events
X-EVT-02 SHALL report, as `warning`, an observer on `model_save_before`, `model_save_after`, `model_save_commit_after`, `model_delete_before`, `model_delete_after` or `model_delete_commit_after`.

#### Scenario: Generic save
- **WHEN** `events.xml` observes `model_save_after`
- **THEN** X-EVT-02 reports a warning

#### Scenario: Entity save
- **WHEN** `events.xml` observes `catalog_product_save_after`
- **THEN** X-EVT-02 reports nothing

### Requirement: X-EVT-03 observer on per-block render events
X-EVT-03 SHALL report, as `warning`, an observer on `core_layout_render_element`, `view_block_abstract_to_html_before` or `view_block_abstract_to_html_after`. Observers declared by module `Magento_PageCache` SHALL NOT be reported, since core needs them for FPC.

#### Scenario: Render observer
- **WHEN** a custom module observes `view_block_abstract_to_html_after`
- **THEN** X-EVT-03 reports a warning

#### Scenario: Core page cache
- **WHEN** `Magento_PageCache` observes `core_layout_render_element`
- **THEN** X-EVT-03 reports nothing

### Requirement: Event rule findings
Event rule findings SHALL point at the `<observer>` element. Observers with `disabled="true"` SHALL NOT be reported.

#### Scenario: Disabled observer
- **WHEN** the observer element has `disabled="true"`
- **THEN** no event rule reports it

### Requirement: X-AREA-01 admin event in the global area
X-AREA-01 SHALL report, as `info`, an observer in the global `etc/events.xml` on an event starting with `adminhtml_`, `controller_action_predispatch_adminhtml_` or `controller_action_postdispatch_adminhtml_`, except events starting with `adminhtml_cache_`. Disabled observers SHALL NOT be reported. Plugins SHALL NOT be reported: a class under `Controller\Adminhtml` can be a plain service that other areas use.

#### Scenario: Admin event in global file
- **WHEN** `etc/events.xml` has an observer on `adminhtml_customer_save_after`
- **THEN** X-AREA-01 reports an info finding

#### Scenario: Admin event in admin file
- **WHEN** `etc/adminhtml/events.xml` has the same observer
- **THEN** X-AREA-01 reports nothing

#### Scenario: Cache events
- **WHEN** `etc/events.xml` has an observer on `adminhtml_cache_flush_all`
- **THEN** X-AREA-01 reports nothing

#### Scenario: Plugin on an admin controller
- **WHEN** `etc/di.xml` declares a plugin on `Magento\Backend\Controller\Adminhtml\Index\Index`
- **THEN** X-AREA-01 reports nothing

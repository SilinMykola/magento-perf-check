## ADDED Requirements

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

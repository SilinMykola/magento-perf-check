# X-AREA-01: observer on an admin event in the global area

| | |
|---|---|
| Severity | `info` |
| Status | default set |
| Analyzer | XML (`etc/events.xml`) |

## What it catches

In the global `etc/events.xml`, an observer on an event that starts with `adminhtml_`, `controller_action_predispatch_adminhtml_` or `controller_action_postdispatch_adminhtml_`.

`adminhtml_cache_*` events are left out: they also fire from the `cache:flush` command. Plugins are left out too: a class under `Controller\Adminhtml` can be a plain service that other areas use, so moving its plugin could change behavior.

## Why it hurts performance

Global configuration is loaded in every area, so the storefront and the API read and merge a declaration they can never use. The cost of one declaration is small; this is why the rule is `info`.

## Example

Bad:

```xml
<!-- etc/events.xml -->
<event name="adminhtml_customer_save_after">
    <observer name="acme_sync" instance="Acme\Sync\Observer\CustomerSaved"/>
</event>
```

Good:

Move the same declaration to `etc/adminhtml/events.xml`.

## How to suppress

`<!-- perf-ignore X-AREA-01: reason -->` directly before the element.

## Known false positives

- An `adminhtml_*` event that a module also dispatches from another area. The rule does not search for dispatchers.
- An observer on a `controller_action_*_adminhtml_*` event that never fires, because the admin route id differs from `adminhtml`. Removing it is the better fix.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Security, performance, and data handling](https://developer.adobe.com/commerce/php/best-practices/extensions/security) | A | "It is NOT RECOMMENDED to register everything in global area, as the bootstrapping process will become much heavier." |
| [Adobe: Events and observers](https://developer.adobe.com/commerce/php/development/components/events-and-observers/) | A | "Declare the observer in the appropriate area." |

Verified 2026-11-07.

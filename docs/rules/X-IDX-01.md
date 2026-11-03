# X-IDX-01: indexer without an mview subscription

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | XML (`indexer.xml`), with the project index of `mview.xml` |

## What it catches

An `<indexer>` with a `view_id` for which no `mview.xml` in the project, in any module and including core, declares a `<view id="...">`. Without any Magento configuration in the project, the rule reports nothing.

## Why it hurts performance

"Update by Schedule" is the recommended mode for production: changes are recorded by database triggers and processed in batches by cron. An indexer whose view is missing cannot track changes in that mode, so the only way to keep it fresh is a full reindex, or switching it back to "Update on Save", which reindexes inside every save request.

## Example

Bad (`indexer.xml` without a matching view):

```xml
<indexer id="acme_promo" view_id="acme_promo" class="Acme\Promo\Model\Indexer\Promo"/>
```

Good: declare the view and the tables it subscribes to in `mview.xml`:

```xml
<view id="acme_promo" class="Acme\Promo\Model\Indexer\Promo" group="indexer">
    <subscriptions>
        <table name="acme_promo_rule" entity_column="rule_id"/>
    </subscriptions>
</view>
```

## How to suppress

`<!-- perf-ignore X-IDX-01: reason -->` directly before the `<indexer>`.

## Known false positives

- A view declared in a module that is not installed through Composer or `app/code` is not in the index.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Adobe: Custom indexer](https://developer.adobe.com/commerce/php/development/components/indexing/custom-indexer) | A | A custom indexer declares its mview view to support "Update by Schedule". |
| [Adobe: indexer configuration](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/maintenance/indexer-configuration) | A | Recommends "Update by Schedule" for production. |
| [Mview/View.php#L185-L189](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Mview/View.php#L185-L189) | B | Loading an undeclared view throws an exception. |

Verified 2026-10-21 (see [source audit](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/source-audit.md)).

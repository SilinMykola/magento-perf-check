# X-EVT-02: calibration sample

1 of 1 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-page-cache/etc/events.xml:49` (core)

[X-EVT-02] Observer "flush_cache_after_model_save" on "model_save_commit_after" runs for every model saved or deleted, not just one entity type. Observe a more specific event if one exists.

```
    43          <observer name="flush_all_pagecache" instance="Magento\PageCache\Observer\FlushCacheByTags" />
    44      </event>
    45      <event name="controller_action_postdispatch_adminhtml_system_currencysymbol_save">
    46          <observer name="flush_all_pagecache" instance="Magento\PageCache\Observer\InvalidateCache" />
    47      </event>
    48      <event name="model_save_commit_after">
>   49          <observer name="flush_cache_after_model_save" instance="Magento\PageCache\Observer\FlushCacheByTags" />
    50      </event>
    51      <event name="admin_user_authenticate_after">
    52          <observer name="FlushFormKey" instance="Magento\PageCache\Observer\FlushFormKey"/>
    53      </event>
    54      <event name="customer_login">
    55          <observer name="FlushFormKey" instance="Magento\PageCache\Observer\FlushFormKey"/>
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-page-cache/etc/events.xml:49` | FP | FlushCacheByTags is generic by design: it cleans built-in FPC by the identity tags of any saved model, so no entity-specific event can replace it. | re-check |  |

# P-DI-03: calibration sample

1 of 1 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-customer/Model/Session.php:195` (core)

[P-DI-03] An event is dispatched in the constructor of Magento\Customer\Model\Session: its observers run every time the object is created. Dispatch it from the method where the event happens.

```
   189              $validator,
   190              $storage,
   191              $cookieManager,
   192              $cookieMetadataFactory,
   193              $appState
   194          );
>  195          $this->_eventManager->dispatch('customer_session_init', ['customer_session' => $this]);
   196      }
   197  
   198      /**
   199       * Retrieve customer sharing configuration model
   200       *
   201       * @return Share
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-customer/Model/Session.php:195` |  |  | re-check |  |

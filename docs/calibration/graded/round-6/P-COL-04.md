# P-COL-04: calibration sample

1 of 1 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/PostcodeEu/AddressValidation/Cron/UpdateApiData.php:198` (extended)

[P-COL-04] Magento\Config\Model\ResourceModel\Config\Data\Collection is iterated without a page size in a batch job: all matching rows are held in memory at once. Walk it in pages with setPageSize() and setCurPage().

```
   192              StoreConfigHelper::PATH['account_status'],
   193              StoreConfigHelper::PATH['supported_countries']
   194          ]]);
   195  
   196          $this->_existingConfig = [];
   197  
>  198          foreach ($collection as $item) {
   199              $this->_existingConfig[$item->getScope()][$item->getScopeId()][$item->getPath()] = $item->getValue();
   200          }
   201      }
   202  
   203      /**
   204       * Save config value only if it has changed.
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/PostcodeEu/AddressValidation/Cron/UpdateApiData.php:198` | TP-low | The filter on five config paths leaves at most five rows per scope in core_config_data, read once by a daily cron job. |  |  |

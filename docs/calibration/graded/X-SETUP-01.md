# X-SETUP-01: calibration sample

4 of 4 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-data-exporter/Setup/Recurring.php:67` (core)

[X-SETUP-01] Recurring::install() does heavy work (loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.

```
    61       * @inheritdoc
    62       */
    63      public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    64      {
    65          $this->schemaSetup->startSetup();
    66  
>   67          $indexers = $this->indexerCollection->getItems();
    68  
    69          foreach ($indexers as $indexer) {
    70              try {
    71                  try {
    72                      $indexerAction = $this->actionFactory->create($indexer->getActionClass());
    73                  } catch (\BadMethodCallException $ignore) {
```

## 2. `vendor/magento/module-theme/Setup/RecurringData.php:51` (core)

[X-SETUP-01] RecurringData::install() does heavy work (loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.

```
    45      /**
    46       * {@inheritdoc}
    47       */
    48      public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    49      {
    50          $indexer = $this->indexerRegistry->get(Config::DESIGN_CONFIG_GRID_INDEXER_ID);
>   51          $indexer->reindexAll();
    52          $this->themeRegistration->register();
    53      }
    54  }
```

## 3. `vendor/magento/module-customer/Setup/RecurringData.php:44` (core)

[X-SETUP-01] RecurringData::install() does heavy work (loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.

```
    38       * @inheritDoc
    39       */
    40      public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    41      {
    42          if ($this->isNeedToDoReindex($setup)) {
    43              $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
>   44              $indexer->reindexAll();
    45          }
    46      }
    47  
    48      /**
    49       * Check is re-index needed
    50       *
```

## 4. `vendor/magento/module-indexer/Setup/Recurring.php:99` (core)

[X-SETUP-01] Recurring::install() does heavy work (loading, saving or reindexing); it runs on every setup:upgrade. Move one-time work to a data or schema patch.

```
    93          foreach ($this->config->getIndexers() as $index) {
    94              $indexerId = $index['indexer_id'];
    95              $state = $this->stateFactory->create();
    96              $state->loadByIndexer($indexerId);
    97              //  If state does not exist, create default index mode to scheduled
    98              if (empty($state->getData('state_id'))) {
>   99                  $indexer = $this->indexerFactory->create()->load($indexerId);
   100                  $indexer->setScheduled(true);
   101              }
   102          }
   103  
   104          /** @var State[] $stateIndexers */
   105          $stateIndexers = [];
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-data-exporter/Setup/Recurring.php:67` |  |  | re-check |  |
| 2 | `vendor/magento/module-theme/Setup/RecurringData.php:51` |  |  | re-check |  |
| 3 | `vendor/magento/module-customer/Setup/RecurringData.php:44` |  |  | re-check |  |
| 4 | `vendor/magento/module-indexer/Setup/Recurring.php:99` |  |  |  |  |

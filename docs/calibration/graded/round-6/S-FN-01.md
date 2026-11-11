# S-FN-01: calibration sample

6 of 6 findings (seed 20261110). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Swissup/Core/Model/ComponentList/Loader/Remote.php:227` (extended)

[S-FN-01] The use of function sleep() is discouraged (Magento2.Functions.DiscouragedFunction)

```
   221          if (!$lock) {
   222              if ($packages = $this->loadStoredPackages()) {
   223                  return $packages;
   224              }
   225  
   226              for ($i = 0; $i < 5; $i++) {
>  227                  sleep(1);
   228                  if ($this->storage->lock(self::RESPONSE_STORAGE_KEY)) {
   229                      break;
   230                  }
   231              }
   232  
   233              return $this->loadStoredPackages();
```

## 2. `app/code/Clerk/Clerk/Model/DeferredProductSync.php:94` (extended)

[S-FN-01] The use of function session_status() is discouraged (Magento2.Functions.DiscouragedFunction)

```
    88          $this->pending = [];
    89          if (empty($pending)) {
    90              return;
    91          }
    92  
    93          // Release the session lock first, or the checkout success page waits for the sync anyway.
>   94          if (session_status() === PHP_SESSION_ACTIVE) {
    95              session_write_close();
    96          }
    97          if (function_exists('fastcgi_finish_request')) {
    98              fastcgi_finish_request();
    99          }
   100  
```

## 3. `app/code/MageMojo/Cron/Model/Schedule.php:659` (extended)

[S-FN-01] The use of function sleep() is discouraged (Magento2.Functions.DiscouragedFunction)

```
   653              if (extension_loaded ('newrelic')) {
   654                  /* stop timing the current transaction, but continue instrumenting it */
   655                  newrelic_end_of_transaction();
   656              }
   657  
   658              #Take a break
>  659              sleep(5);
   660          }
   661      }
   662  
   663      /**
   664       * Execute a cron from CLI
   665       *
```

## 4. `app/code/Clerk/Clerk/Model/DeferredProductSync.php:95` (extended)

[S-FN-01] The use of function session_write_close() is discouraged (Magento2.Functions.DiscouragedFunction)

```
    89          if (empty($pending)) {
    90              return;
    91          }
    92  
    93          // Release the session lock first, or the checkout success page waits for the sync anyway.
    94          if (session_status() === PHP_SESSION_ACTIVE) {
>   95              session_write_close();
    96          }
    97          if (function_exists('fastcgi_finish_request')) {
    98              fastcgi_finish_request();
    99          }
   100  
   101          // Prices are computed during the sync, and must be the catalogue's, not the buyer's.
```

## 5. `app/code/Trustpilot/Reviews/Controller/Adminhtml/Index/Index.php:30` (extended)

[S-FN-01] The use of function session_write_close() is discouraged (Magento2.Functions.DiscouragedFunction)

```
    24          $this->_pastOrders = $pastOrders;
    25          $this->_products = $products;
    26      }
    27  
    28      public function execute()
    29      {
>   30          session_write_close();
    31          if ($this->getRequest()->isAjax()) {
    32              $post = $this->getRequest()->getPostValue();
    33              $scope = $post['scope'];
    34              $scopeId = (int) $post['scopeId'];
    35              switch ($post["action"]) {
    36                  case 'handle_save_changes':
```

## 6. `app/code/Payone/Core/Observer/Transactionstatus/Paid.php:102` (extended)

[S-FN-01] The use of function sleep() is discouraged (Magento2.Functions.DiscouragedFunction)

```
    96       * @return void
    97       */
    98      protected function waitTillAppointedIsFinished($sLockKey)
    99      {
   100          $iTrys = 10;
   101          while ($iTrys > 0) {
>  102              sleep(5);
   103              $iTrys--;
   104              if (!$this->cache->load($sLockKey)) {
   105                  break;
   106              }
   107          }
   108      }
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Swissup/Core/Model/ComponentList/Loader/Remote.php:227` | TP-low | The sleep(1), up to 5 times, runs only when another process holds the package-list lock, and the loader is used only by admin blocks, admin controllers and CLI commands. |  |  |
| 2 | `app/code/Clerk/Clerk/Model/DeferredProductSync.php:94` | FP | session_status() only checks whether a session is active before session_write_close() releases its lock in a shutdown flush, so it neither starts nor keeps a session. |  |  |
| 3 | `app/code/MageMojo/Cron/Model/Schedule.php:659` | FP | The sleep(5) is the poll interval of the MageMojo cron daemon loop, which runs in a CLI process and holds no web worker. |  |  |
| 4 | `app/code/Clerk/Clerk/Model/DeferredProductSync.php:95` | FP | session_write_close() here releases the session lock before the deferred Clerk sync runs after the response, which shortens lock time instead of lengthening it. |  |  |
| 5 | `app/code/Trustpilot/Reviews/Controller/Adminhtml/Index/Index.php:30` | FP | session_write_close() at the start of an admin AJAX controller releases the session lock early, so it cannot start or keep a session locked. |  |  |
| 6 | `app/code/Payone/Core/Observer/Transactionstatus/Paid.php:102` | TP | The Paid observer runs in the Payone transaction-status webhook controller and, while an appointed status for the same order is processed, sleeps 5 s up to 10 times, holding a PHP-FPM worker for up to 50 s. |  |  |

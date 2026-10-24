# P-FPC-05: calibration sample

2 of 2 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-cms/Controller/Noroute/Index.php:54` (core)

[P-FPC-05] setHeader() in Magento\Cms\Controller\Noroute\Index makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.

```
    48          /** @var \Magento\Cms\Helper\Page $pageHelper */
    49          $pageHelper = $this->_objectManager->get(\Magento\Cms\Helper\Page::class);
    50          $resultPage = $pageHelper->prepareResultPage($this, $pageId);
    51          if ($resultPage) {
    52              $resultPage->setStatusHeader(404, '1.1', 'Not Found');
    53              $resultPage->setHeader('Status', '404 File not found');
>   54              $resultPage->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
    55              return $resultPage;
    56          } else {
    57              /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
    58              $resultForward = $this->resultForwardFactory->create();
    59              $resultForward->setController('index');
    60              $resultForward->forward('defaultNoRoute');
```

## 2. `vendor/magento/module-catalog-search/Controller/Result/Index.php:192` (core)

[P-FPC-05] setNoCacheHeaders() in Magento\CatalogSearch\Controller\Result\Index makes the response uncacheable by Varnish and the built-in page cache. Confirm this is intended.

```
   186              }
   187          }
   188  
   189          $catalogSearchHelper->checkNotes();
   190  
   191          $this->_view->loadLayout($handles);
>  192          $this->getResponse()->setNoCacheHeaders();
   193          $this->_view->renderLayout();
   194      }
   195  
   196      /**
   197       * Checks for toolbar actions
   198       *
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-cms/Controller/Noroute/Index.php:54` |  |  | re-check |  |
| 2 | `vendor/magento/module-catalog-search/Controller/Result/Index.php:192` |  |  | re-check |  |

# P-FPC-06: calibration sample

4 of 4 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `vendor/magento/module-product-alert/Controller/Add/Stock.php:98` (core)

[P-FPC-06] Magento\ProductAlert\Controller\Add\Stock handles GET only but calls Magento\ProductAlert\Model\Stock::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.

```
    92              /** @var \Magento\ProductAlert\Model\Stock $model */
    93              $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Stock::class)
    94                  ->setCustomerId($this->customerSession->getCustomerId())
    95                  ->setProductId($product->getId())
    96                  ->setWebsiteId($store->getWebsiteId())
    97                  ->setStoreId($store->getId());
>   98              $model->save();
    99              $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
   100          } catch (NoSuchEntityException $noEntityException) {
   101              $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
   102              $this->isInternal($backUrl)
   103                  ? $resultRedirect->setUrl($backUrl)
   104                  : $resultRedirect->setPath('/');
```

## 2. `app/code/Mollie/Payment/Controller/Express/Process.php:58` (packagist)

[P-FPC-06] Mollie\Payment\Controller\Express\Process handles GET only but calls Magento\Quote\Api\CartRepositoryInterface::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.

```
    52  
    53          if ($status != 'completed') {
    54              return $this->redirectToCart($cart);
    55          }
    56  
    57          $cart->setIsActive(0);
>   58          $this->cartRepository->save($cart);
    59  
    60          return $this->renderResultPage($token);
    61      }
    62  
    63      private function redirectToCart(CartInterface $cart): ResultInterface
    64      {
```

## 3. `vendor/magento/module-product-alert/Controller/Add/Price.php:100` (core)

[P-FPC-06] Magento\ProductAlert\Controller\Add\Price handles GET only but calls Magento\ProductAlert\Model\Price::save(). Change state only on POST or PUT: GET responses can be cached and served without running this code.

```
    94              $model = $this->_objectManager->create(\Magento\ProductAlert\Model\Price::class)
    95                  ->setCustomerId($this->customerSession->getCustomerId())
    96                  ->setProductId($product->getId())
    97                  ->setPrice($product->getFinalPrice())
    98                  ->setWebsiteId($store->getWebsiteId())
    99                  ->setStoreId($store->getId());
>  100              $model->save();
   101              $this->messageManager->addSuccessMessage(__('You saved the alert subscription.'));
   102          } catch (NoSuchEntityException $noEntityException) {
   103              $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
   104              if ($this->isInternal($backUrl)) {
   105                  $resultRedirect->setUrl($backUrl);
   106              } else {
```

## 4. `vendor/magento/module-product-alert/Controller/Unsubscribe/Price.php:78` (core)

[P-FPC-06] Magento\ProductAlert\Controller\Unsubscribe\Price handles GET only but calls Magento\ProductAlert\Model\Price::delete(). Change state only on POST or PUT: GET responses can be cached and served without running this code.

```
    72                      $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
    73                          ->getStore()
    74                          ->getWebsiteId()
    75                  )
    76                  ->loadByParam();
    77              if ($model->getId()) {
>   78                  $model->delete();
    79              }
    80  
    81              $this->messageManager->addSuccessMessage(__('You deleted the alert subscription.'));
    82          } catch (NoSuchEntityException $noEntityException) {
    83              $this->messageManager->addErrorMessage(__("The product wasn't found. Verify the product and try again."));
    84              $resultRedirect->setPath('customer/account/');
```

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `vendor/magento/module-product-alert/Controller/Add/Stock.php:98` |  |  | re-check |  |
| 2 | `app/code/Mollie/Payment/Controller/Express/Process.php:58` |  |  | re-check |  |
| 3 | `vendor/magento/module-product-alert/Controller/Add/Price.php:100` |  |  | re-check |  |
| 4 | `vendor/magento/module-product-alert/Controller/Unsubscribe/Price.php:78` |  |  |  |  |

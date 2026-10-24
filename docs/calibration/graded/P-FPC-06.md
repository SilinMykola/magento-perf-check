# P-FPC-06: calibration sample

1 of 1 findings (seed 20261023). Verdicts: TP, TP-low, FP, unclear. Rows marked `re-check` are for the owner: put `✓` in the owner column, or `was <verdict>` after changing the verdict.

## 1. `app/code/Mollie/Payment/Controller/Express/Process.php:58` (packagist)

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

## Grades

| # | location | verdict | reason | recheck | owner |
|---|---|---|---|---|---|
| 1 | `app/code/Mollie/Payment/Controller/Express/Process.php:58` | TP | On a completed payment the action deactivates the quote and returns a result page whose layout mollie_express_process has no cacheable=false, so a repeat GET of the same token URL can be served from cache without running the save. | re-check |  |

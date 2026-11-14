# P-FPC-02: calibration grades, round 20

12 of 12 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/ChrisMallory/FreeShippingProgressBar/ViewModel/Cart/ProgressBar.php:97` | FP | The view model is placed only in checkout_cart_index, which core makes uncacheable with the checkout.cart block (cacheable="false"), so the session is never depersonalized there. |
| 2 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:69` | FP | The view model is used only on the hyvareactcheckout_reactcheckout_index checkout page, whose checkout.container block is cacheable="false". |
| 3 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:76` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 4 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:104` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 5 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:111` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 6 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:118` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 7 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:127` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 8 | `app/code/FriendsOfHyva/ReactCheckout/ViewModel/ReactCheckoutLocalStorage.php:136` | FP | The view model is used only on the uncacheable React checkout page (checkout.container is cacheable="false"). |
| 9 | `app/code/MagePal/GuestToCustomer/Block/Customer/LookupForm.php:81` | FP | The block is placed only on customer_guesttocustomer_lookupform, an account page that includes customer_account and marks the block cacheable="false". |
| 10 | `app/code/Magefan/GoogleTagManager/Block/DataLayer/BeginCheckout.php:61` | FP | The block is placed only on checkout_index_index, hyva_checkout_index_index and onestepcheckout_index_index, checkout pages that are never page-cached. |
| 11 | `app/code/Magefan/GoogleTagManager/Block/DataLayer/ViewCart.php:61` | FP | The block is placed only on checkout_cart_index, which core makes uncacheable. |
| 12 | `app/code/Magestat/FacebookPixel/Block/Checkout.php:96` | FP | The block is placed only on checkout_index_index, whose core checkout.root block is cacheable="false". |

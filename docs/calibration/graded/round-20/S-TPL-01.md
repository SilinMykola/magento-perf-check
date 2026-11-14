# S-TPL-01: calibration grades, round 20

12 of 29 findings (seed 20261114). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-14 by one grader; see [round 20](../../round-20.md). Locations are in the `github` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/MagePal/CustomShippingRate/view/adminhtml/templates/order/create/shipping/method/form.phtml:84` | TP-low | The template uses `$this->getQuote()` inside the admin order-create shipping form, which is deprecated but costs nothing on an admin-only page. |
| 2 | `app/code/MagePal/CustomShippingRate/view/adminhtml/templates/order/create/shipping/method/form.phtml:84` | TP-low | `$this->helper()` resolves the shared helper through the object manager once per render of an admin-only form, so the cost is negligible. |
| 3 | `app/code/Magepow/Categories/view/frontend/templates/categories.phtml:33` | TP-low | `$this->getFrontendCfg()` and `$this->getData()` go through the template engine proxy to the same block, a style issue with no measurable cost. |
| 4 | `app/code/Magepow/Categories/view/frontend/templates/categories.phtml:33` | TP-low | The second `$this` use on the line (`getResponsiveBreakpoints()` loop) is the same block object, so it is a style issue only. |
| 5 | `app/code/Magepow/Categories/view/frontend/templates/categories.phtml:41` | TP-low | `$this->getImage($cat)` should be `$block->getImage($cat)`; the proxy call per category is negligible. |
| 6 | `app/code/Magepow/Categories/view/frontend/templates/categories.phtml:50` | TP-low | `$this->getDescription($cat)` is the same block call through the engine proxy, a style issue only. |
| 7 | `app/code/Magepow/Categories/view/frontend/templates/categories_widget.phtml:28` | TP-low | `$this->getData('slide')` is the block through the engine proxy, deprecated style with no real cost. |
| 8 | `app/code/Magepow/Categories/view/frontend/templates/categories_widget.phtml:32` | TP-low | `$this->getData('subtitle')` is a deprecated style use of `$this` with no real cost. |
| 9 | `app/code/Magepow/Categories/view/frontend/templates/categories_widget.phtml:35` | TP-low | `$this->getFrontendCfg()` and `$this->getData()` in the slider attributes are the same block, style only. |
| 10 | `app/code/Magepow/Categories/view/frontend/templates/categories_widget.phtml:35` | TP-low | The second `$this` loop on the line (`getResponsiveBreakpoints()`) is the same block object, style only. |
| 11 | `app/code/Magepow/Categories/view/frontend/templates/categories_widget.phtml:37` | TP-low | `$this->getImageInfo($cat)` should use `$block`; the per-category proxy call is negligible. |
| 12 | `app/code/Magepow/Categories/view/frontend/templates/cmspage.phtml:36` | TP-low | `$this->getImageInfo($cat)` is a deprecated use of `$this` that calls the same block, so there is no performance cost. |

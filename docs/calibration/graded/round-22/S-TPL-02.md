# S-TPL-02: calibration grades, round 22

6 of 6 findings (seed 20261115). Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-15 by one grader; see [round 22](../../round-22.md). Locations are in the `github2` part of the calibration corpus.

| # | where | verdict | reason |
|---|---|---|---|
| 1 | `app/code/Magefan/AutoRelatedProduct/view/adminhtml/templates/form/versionsManager.phtml:14` | TP-low | One shared-instance lookup on an admin edit page. |
| 2 | `app/code/Magefan/HtmlSitemap/view/frontend/templates/footer_link.phtml:16` | TP-low | Creates one small config object per render of a cached page. |
| 3 | `app/code/Magefan/ProductLabel/view/adminhtml/templates/form/versionsManager.phtml:13` | TP-low | The same admin-only lookup. |
| 4 | `app/code/Magepow/CancelOrder/view/frontend/templates/product.phtml:3` | TP-low | Object manager calls in a template of the cancel-order email. |
| 5 | `app/code/RicardoMartins/PagSeguro/view/frontend/templates/cc_images_checkout.phtml:3` | TP-low | Shared-instance lookups on the checkout page only. |
| 6 | `app/code/RicardoMartins/PagSeguro/view/frontend/templates/multicc_images_checkout.phtml:3` | TP-low | The same. |

# U-JS-01: calibration grades, round 18

All 5 findings. Verdicts: TP, TP-low, FP, unclear. Graded on 2026-11-13 by one grader; see [round 18](../../round-18.md). Private stores are named by their letter of [round 15](../../round-15.md), and their code is described, not listed.

| # | where | what | verdict | reason |
|---|---|---|---|---|
| 1 | `app/code/KiwiCommerce/AdminActivity/view/adminhtml/templates/activity/js.phtml:93` (extended) | `jQuery.ajax(...).success(...)` in the revert dialog of the admin activity log | TP | From 2.4.6 the call throws, so reverting an activity does nothing. |
| 2 | `app/code/Mageplaza/Blog/view/adminhtml/web/category/form.js:66` (extended) | `$.ajax(...).success(...)` that refreshes a blog category's path in the admin | TP | From 2.4.6 the call throws, so the path is not refreshed. |
| 3 | store A | a custom storefront script that validates a form with `storage.post(...).success(...)` | TP | mage/storage returns the jqXHR of `$.ajax`; from 2.4.6 the form is never validated. |
| 4 | store D | the theme's copy of `Magento_Review/web/js/process-reviews.js`, `$.ajax(...).done(...).complete(...)` | TP | From 2.4.6 the reviews block is loaded but `contentUpdated` is never triggered and the script stops. |
| 5 | store E | the same copy in a commercial theme | TP | As 4. |

# Calibration follow-ups

Recurring false-positive patterns found while grading the M2 calibration samples. Fixing them changes what a rule's spec says it reports, so they are left for a later change. Row numbers refer to the grading table of the rule's file in [`graded/`](graded/). Patterns fixed since are removed; see [round 2](round-2.md).

## X-LAY-01: allowlist gaps

Handles still reported that are personal or private by design: `mailchimp_cart_loadquote` (a cart restored from an email link) and `blog_non_cacheable` (blog post preview). Both are specific to one module, so they stay out of the default allowlist.

## P-COL-02: collections that load at most one row

- Filters on a column that is unique in practice: `setUserFilter()` on one user's role, `level = 0` for the tree root (2, 4, 7, 9, 11, 13, 16, 17).
- A page size set by a helper: `getSelect()->limit(1)` in a called method (18, 21).
- A collection that the getter has already loaded (5, 15).

## P-FPC-02: blocks that never render on a cacheable page

- Blocks reading `Magento\Backend\Model\Session\Quote`, or rendered only from `adminhtml` templates, outside the `Block\Adminhtml` namespace (rows 1, 2, 3, 19, 25, 30).
- Blocks placed only on layouts with `cacheable="false"` or on uncacheable pages such as the cart and checkout (most other rows). This needs the layout index to know where a block is placed.

## P-DI-02 and P-DI-06: backward-compatible fallbacks

- `ObjectManager::getInstance()` kept in a local variable, or an injected or inherited `$objectManager`/`$_objectManager`, used for the fallback (P-DI-02 8, 16, 17, 21).
- Classes built with `new`, not by the object manager: exceptions, value objects, ZF1 and MFTF code (P-DI-02 3, 10, 22, 23).

## X-AREA-01: dead observers

Observers on `controller_action_postdispatch_adminhtml_catalog_product_*` never fire: `Magento_Catalog`'s admin route id is `catalog`, and `FrontController` builds the event name from the route id (`module-cache-invalidate/etc/events.xml:43,46`, `Fastly/Cdn/etc/events.xml:59,62`). X-AREA-01 tells people to move them. A separate check that resolves `routes.xml` could report them as dead.

## P-LOOP-03 and P-DI-06, from the check on other modules

See [held-out.md](held-out.md): a save behind a check on an id in the loop, and classes that a library builds by name.

## P-LOOP-02 and P-LOOP-03: a `switch` case that matches one item

Seen in November 2026 on archived projects. A query or a save sits in a `switch` case inside a loop, and in practice the case matches one item. The rules do not know which items a case matches, so they report it. Two shapes were seen:
- `switch ($key)` inside `foreach ($items as $key => $item)`. Array keys are unique only by `===`, while `switch` compares with `==`: the keys `1` and `'1.0'` both match `case 1`. An iterator or a generator can also yield the same key twice. In the case seen, the loop ran over the result of `json_decode()`, which PHPStan does not type as an array.
- `switch ($item->getCode())` on a code that Magento keeps unique, such as `customer_group_code`. The check runs on save in `_initUniqueFields()` ([Group.php#L62-L67](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/Model/ResourceModel/Group.php#L62-L67)). The database index on that column is not unique ([db_schema.xml#L433-L435](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Customer/etc/db_schema.xml#L433-L435)). The rules cannot see this check, and the same shape on a getter whose value repeats is a real N+1.

The rules cannot tell these cases from real findings without guessing. They stay reported and need a suppression with a reason.

## Round 6, on the extended corpus part

Row numbers refer to [`graded/round-6/`](graded/round-6/).

- **X-LAY-01.** Handles that are private by design under prefixes the allowlist does not cover: Dotdigital email content endpoints `connector_*` (one customer's basket, order or coupon from the URL), payment return and checkout-step pages (`paynl_order_*`, `paynl_checkout_*`, `payone_onepage_*`, `payone_amazon_*`, `buckaroo_*_pay`), and Mageplaza Blog's post history and preview for the logged-in author.
- **P-COL-02.** Filters that match one row through a unique index in `db_schema.xml`, `_uniqueFields`, a primary key under another name or a token; a page size set by the caller before it passes the collection in; a collection that an earlier call already loaded.
- **P-FPC-02.** Blocks and view models that render only on pages without full page cache (cart, checkout, customer account, layouts with `cacheable="false"`), or whose session read is not reached while a cacheable page renders.
- **P-DI-02.** Value and SDK objects that code creates with `new`, not the object manager; `return $this;` in a constructor that otherwise only assigns; a full ternary that falls back to an array literal, which the `?:` and `??` forms already allow. Getters on the context object (`$context->getRequest()` and the like) are correct but always negligible.
- **P-LOOP-12.** Core repositories that cache by id (`QuoteRepository::get()`, `GroupRepository::getById()` through its registry); a product that the same iteration has just loaded into the repository's cache; a loop that returns on the first hit; a caller that keeps results behind an `isset()` on a map it filled before the loop.
- **S-PERF-01.** As in round 4: no result that grows across iterations, or a merge into itself in a loop that runs once.
- **S-FN-01.** `session_write_close()` and a `session_status()` guard that release the session lock; `sleep()` as a poll in a CLI daemon.
- **S-DI-01.** The sniff takes `Proxy` as a namespace segment for a generated Proxy: the Factory of `Model\Import\Proxy\Category\ResourceModel` is reported.
- **P-LOOP-04.** A second `getProductUrl()` on the same product in one iteration reads the request path that the first call stored.

## Low impact

These rules are correct but report mostly negligible cases; calibration lowers their severity through the impact share. A narrower scope could raise it again:
- P-LOOP-03: saves in loops over small, capped sets in cron and setup.
- P-FPC-01: blocks placed only on uncacheable pages.
- P-LOOP-09, P-LOOP-10, P-IDX-01: all graded findings so far are admin-only or capped.
- T-01: one block created per render of an uncacheable form.

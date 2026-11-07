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

- `if (!isset($x))` / `if (empty($x))` fallbacks in the constructor, the same pattern as the allowed `??` and `?:` (P-DI-06 rows 4, 5).
- `ObjectManager::getInstance()` kept in a local variable, or an injected or inherited `$objectManager`/`$_objectManager`, used for the fallback (P-DI-06 23; P-DI-02 8, 16, 17, 21).
- Classes built with `new`, not by the object manager: exceptions, value objects, ZF1 and MFTF code (P-DI-02 3, 10, 22, 23).

## X-AREA-01: dead observers

Observers on `controller_action_postdispatch_adminhtml_catalog_product_*` never fire: `Magento_Catalog`'s admin route id is `catalog`, and `FrontController` builds the event name from the route id (`module-cache-invalidate/etc/events.xml:43,46`, `Fastly/Cdn/etc/events.xml:59,62`). X-AREA-01 tells people to move them. A separate check that resolves `routes.xml` could report them as dead.

## P-LOOP-03 and P-DI-06, from the check on other modules

See [held-out.md](held-out.md): a save behind a check on an id in the loop, and classes that a library builds by name.

## Low impact

These rules are correct but report mostly negligible cases; calibration lowers their severity through the impact share. A narrower scope could raise it again:
- P-DI-01: sessions in frontend actions, which `ContextPlugin` starts anyway (see the [rule page](../rules/P-DI-01.md)).
- P-DI-06: lazy BC getters that create a dependency on first use.
- P-LOOP-03: saves in loops over small, capped sets in cron and setup.
- P-FPC-01: blocks placed only on uncacheable pages.
- P-LOOP-09, P-LOOP-10, P-IDX-01: all graded findings so far are admin-only or capped.
- T-01: one block created per render of an uncacheable form.

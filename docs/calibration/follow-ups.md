# Calibration follow-ups

Recurring false-positive patterns found while grading the M2 calibration samples. Fixing them changes what a rule's spec says it reports, so they are left for a later change. Row numbers refer to the grading table of the rule's file in [`graded/`](graded/).

## X-LAY-01: allowlist gaps

Layouts that are personal by design and should be allowed by default:
- Hyvä Checkout handles, `hyva_checkout_*` (rows 2, 9). The allowlist covers `checkout_` but not this prefix.
- Layouts that include the `customer_account` handle with `<update handle="customer_account"/>`, for example `mollie_savedcards_index` (row 3).
- Pages bound to one customer, order or email link: `productalert_unsubscribe_*` (1), `sendfriend_product_send` (6), `mailchimp_cart_loadquote` (7), `shipping_tracking_popup` (8).
- Preview-only handles such as `blog_non_cacheable` (4).

## P-COL-02: collections that load at most one row

- A guard on the size before the call: `getSize() === 1`, `getTotalCount() !== 1` (rows 3, 8, 10, 19).
- Filters on the primary key or on a column that is unique in practice: `setIdFilter()` on an id, `setUserFilter()` on one user's role, `level = 0` for the tree root (2, 4, 7, 9, 11, 13, 16, 17).
- A page size set by a helper: `setPage(0, 1)`, `getSelect()->limit(1)` in a called method (18, 21).
- A collection that the getter has already loaded (5, 15).

## P-PLG-01: state read before `$proceed` and used after it

18 of the 30 sampled findings (rows 1, 2, 6, 7, 12, 13, 15–21, 23, 24, 27–29). The method reads something before the call that the call changes or deletes (`isObjectNew()`, an id before save, children or gallery rows before delete) and uses it after. A before plus after plugin pair cannot do this without shared state, so around is the right choice.

Possible check: report only when no local variable assigned before `$proceed` is read after it.

## P-FPC-01: `_isScopePrivate = false`

A property declaration or assignment with `false`, the default, marks nothing private (rows 4, 10, 13, 25). Only `true` should count.

## P-FPC-02: blocks that never render on a cacheable page

- Blocks reading `Magento\Backend\Model\Session\Quote`, or rendered only from `adminhtml` templates, outside the `Block\Adminhtml` namespace (rows 1, 2, 3, 19, 25, 30).
- Blocks placed only on layouts with `cacheable="false"` or on uncacheable pages such as the cart and checkout (most other rows). This needs the layout index to know where a block is placed.

## P-DI-02 and P-DI-06: backward-compatible fallbacks

- `if (!isset($x))` / `if (empty($x))` fallbacks in the constructor, the same pattern as the allowed `??` and `?:` (P-DI-06 rows 4, 5).
- `ObjectManager::getInstance()` kept in a local variable, or an injected or inherited `$objectManager`/`$_objectManager`, used for the fallback (P-DI-06 23; P-DI-02 8, 16, 17, 21).
- Classes built with `new`, not by the object manager: exceptions, value objects, ZF1 and MFTF code (P-DI-02 3, 10, 22, 23).

## P-LOOP-01: loads that touch no database

- `Magento\Directory\Model\Currency::load()` only sets the currency code (rows 1, 11).
- `Magento\Store\Model\StoreRepository::getById()` reads cached configuration (26).

## Low impact

These rules are correct but report mostly negligible cases; calibration lowers their severity through the impact share. A narrower scope could raise it again:
- P-DI-01: sessions in frontend actions, which `ContextPlugin` starts anyway (see the [rule page](../rules/P-DI-01.md)).
- P-DI-06: lazy BC getters that create a dependency on first use.
- P-LOOP-03: saves in loops over small, capped sets in cron and setup.
- P-FPC-01: blocks placed only on uncacheable pages.
- T-01: one block created per render of an uncacheable form.

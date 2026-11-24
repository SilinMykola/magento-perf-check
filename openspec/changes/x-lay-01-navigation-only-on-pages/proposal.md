# Proposal

## Why

The copied account layout check (`x-lay-01-copied-account-navigation`) made every handle that declares the `customer_account_navigation` block an account page, and every handle that includes such a handle too, unless it was shop-wide. That hides true findings:

- A handle that is no page and declares the navigation for a header dropdown, which `default.xml` includes, was taken for an account page although it is on every page. X-LAY-01 did not report a `cacheable="false"` block in it, which turns Full Page Cache off for the whole store, and P-FPC-02 did not report a session read in a block placed only there.
- A public page that includes such a dropdown through another handle, as `contact_index_index` including a header handle that includes the dropdown, became an account page, and so did every handle that only it includes: their `cacheable="false"` blocks were not reported.

## What Changes

- A handle that declares the navigation is an account page when it is a page, the handle of a storefront controller action. A handle that is no page and declares the navigation is an account page only when every handle that includes it, directly or through other handles, is a page that is not shop-wide: a handle that a shop-wide handle includes, or that only a handle no layout includes takes in, may be on any page. Such a handle makes the pages that include it account pages, as a copied account layout of a module does.
- Account pages still follow includes upward, but now only from these handles and from handles that include `customer_account`.
- An include of `customer_account` counts as before, and a shop-wide handle of X-LAY-03 is still an account page only by including `customer_account` itself.
- P-FPC-02 reads the same account pages. The project index version is raised, as its layout part changes.

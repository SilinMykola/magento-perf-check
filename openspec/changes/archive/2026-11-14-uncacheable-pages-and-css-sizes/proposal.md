# Proposal

## Why

Round 20 graded all 12 P-FPC-02 findings on the GitHub modules false: each block or view model sat only on pages that are never cached, such as the cart, the checkout of core, Hyvä or a checkout module, or a customer account page. Round 6 found the same. The rule does not look at where a class is placed. Round 20 also graded all 4 T-06 findings false because CSS fixed the image box; one of them in a `<style>` block of the same template.

## What Changes

- **P-FPC-02.** The project index records, for each frontend layout handle of the modules and themes, the classes it names (`<block class>`, `<referenceBlock class>`, an `<argument>` or `<item>` of type `object`), and which handles no cacheable page uses: those whose files hold a `cacheable="false"` block without `ifconfig` that no file of the handle removes (not the shop-wide `default`, `catalog_*` and `cms_*`, where that block is an X-LAY-03 error to fix), account pages, and handles only those include. X-LAY-01's allowlist is not used: it names pages where being uncacheable is acceptable, not pages that are never cached. A class placed only on such handles is not reported, unless a subclass of it is placed on a handle a cacheable page may use.
- **T-06.** An image is not reported when CSS fixes its box: the rules of the template's `<style>` elements for all media, in order and outside `@media`, whose selector ends in classes the image has, then its `style` attribute, set a height in a length unit with a width or an aspect ratio, or a width with an aspect ratio. CSS in the theme's or module's LESS is not read and stays a known false positive.

Measured on the whole corpus before and after.

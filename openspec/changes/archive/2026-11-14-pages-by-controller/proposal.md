# Proposal

## Why

The rules took every handle whose name starts with a storefront route id for a page. Magento 2.4.9 has many that are not: `customer_account`, `checkout_cart_sidebar_item_renderers`, `sales_order_item_renderers` and others are only included by other layouts. Two reviews of P-FPC-02 found the consequences:
- X-LAY-01 skipped any file whose handle matches its allowlist, so a `cacheable="false"` block in `checkout_cart_sidebar_*`, which `default.xml` includes on every page, was never reported.
- P-FPC-02 took `customer_account`, which only account pages include, for a page of its own that may be cached, so blocks of the account sidebar kept being reported.

## What Changes

- A page is the handle of a storefront controller action: route id, controller path and action in lower case, as `getFullActionName()` builds them, from the `Controller` classes of the route's modules (`PrintAction` serves `print`, as for every PHP reserved word; a controller path with `adminhtml` in it and an abstract class are left out, as `Router\ActionList` does; routes.xml is read as XML). A handle that extends a page's name, such as `catalog_product_view_type_simple`, belongs to that page. A route whose modules have no controllers keeps the old reading: each of its handles is a page.
- A page is personal when it is itself: allowlisted for X-LAY-01, an account page, or holding a `cacheable="false"` block for P-FPC-02. A handle that extends a page's name is personal when it or its page is and every layout that includes it is personal, as `checkout_cart_configure_type_simple` includes `catalog_product_view_type_simple`. Any other handle follows the handles that include it; one that no layout includes is added by code and is decided as a page.
- X-LAY-01 applies its allowlist to pages only when it knows the project's handles; without them each file stands alone and the allowlist applies to every handle as before.
- A page decides for itself, as before; its `hyva_` copy also needs every layout that includes the copy to be personal. Measuring a first version that also followed the includers of a page brought back two P-FPC-02 findings graded false: Magento includes `customer_address_form` in multishipping pages and `checkout_onepage_success` in the PayPal Payment Services success page.
- The allowlist gets `paymentservicespaypal_smartbuttons_` and the Braintree pages `braintree_paypal_review`, `braintree_googlepay_review` and `braintree_lpm_fallback`: these review, success and return pages include `checkout_onepage_review_item_renderers`, whose `cacheable="false"` block would otherwise be reported now.

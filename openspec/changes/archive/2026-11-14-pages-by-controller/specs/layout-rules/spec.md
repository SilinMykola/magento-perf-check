## MODIFIED Requirements

### Requirement: Handles that only personal pages include
X-LAY-01 SHALL NOT report blocks in a handle that other handles include when every one of them is personal: allowlisted, an account page, or itself included only by personal handles. A handle that includes itself, or that a cycle leads back to, SHALL NOT count as one that includes it. A page is the handle of a storefront controller action of the route's modules: route id, controller path and action in lower case, as `Router\ActionList` finds the class (an action class named after a PHP reserved word, such as `PrintAction`, serves that word; a controller path with `adminhtml` in it, an abstract class, an interface and a trait serve nothing). A handle of a route whose modules have no controllers counts as a page. A handle of a route whose id starts with `hyva_` SHALL NOT be read as a `hyva_` copy. A page SHALL be personal when it is allowlisted or an account page; its `hyva_` copy only when, besides, every handle that includes the copy is personal. A handle that extends a page's name with `_` and more is added by that page's controller: it SHALL be personal when it or its page is allowlisted or an account page and every handle that includes it is personal. Any other handle SHALL follow the handles that include it, whatever the allowlist says of its name; one that no layout includes SHALL be decided by the allowlist, as code adds it. A `hyva_` handle SHALL be personal only when the handle without the prefix is personal and every handle that includes the `hyva_` handle itself is personal. X-LAY-03 SHALL NOT follow includes, because a controller may add a shop-wide handle itself.

#### Scenario: Handle included only by order pages
- **WHEN** `acme_fee_total.xml` has a block with `cacheable="false"`, and only `sales_order_view.xml` and `sales_order_print.xml` include `acme_fee_total`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Handle included by a public page
- **WHEN** `acme_widget.xml` has a block with `cacheable="false"`, and `sales_order_view.xml` and `contact_index_index.xml` include `acme_widget`
- **THEN** X-LAY-01 reports the block

#### Scenario: Handle included through a chain
- **WHEN** only `acme_totals.xml` includes `acme_fee_total`, and only `sales_order_view.xml` includes `acme_totals`
- **THEN** X-LAY-01 reports nothing in `acme_fee_total.xml`

#### Scenario: Handles that only include each other
- **WHEN** `acme_cycle_a` and `acme_cycle_b` include each other, both have a block with `cacheable="false"`, and nothing else includes them
- **THEN** X-LAY-01 reports both blocks

#### Scenario: Cycle with an order page
- **WHEN** `acme_cyc_x` and `acme_cyc_y` include each other, both have a block with `cacheable="false"`, and only `sales_order_view.xml` includes `acme_cyc_x` besides them
- **THEN** X-LAY-01 reports neither block

#### Scenario: Route handle
- **WHEN** a module has the storefront route `acmeroute`, and only `sales_order_view.xml` includes `acmeroute_index_index`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Allowlisted handle that is no page
- **WHEN** the route `acmeshop` has the controller `Controller/Cart/Index.php`, `acmeshop_` is allowlisted, and `default.xml` includes `acmeshop_cart_sidebar`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block
- **WHEN** the block is in `acmeshop_cart_index.xml`, in `acmeshop_order_print.xml` with `Controller/Order/PrintAction.php`, in `acmeshop_cart_index_type_bundle.xml`, or in a handle no layout includes
- **THEN** X-LAY-01 reports nothing

#### Scenario: Hyvä copy included by a public page
- **WHEN** only `sales_order_view.xml` includes `acme_hyva_pub`, and `contact_index_index.xml` includes `hyva_acme_hyva_pub`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Addition to a personal page that a public page includes
- **WHEN** `acme_cart_index` is an allowlisted page, and the public page `acme_order_print.xml` includes `acme_cart_index_type_virtual`, which has a block with `cacheable="false"`
- **THEN** X-LAY-01 reports the block

#### Scenario: Shop-wide handle included by a personal page
- **WHEN** only `checkout_cart_configure_type_simple.xml` includes `catalog_product_view_type_simple`, which has a block with `cacheable="false"`
- **THEN** X-LAY-03 reports the block

#### Scenario: Handle that nothing includes
- **WHEN** `acme_page_index.xml` has a block with `cacheable="false"` and no layout file includes `acme_page_index`
- **THEN** X-LAY-01 reports the block

### Requirement: X-LAY-01 default allowlist contents
The default allowlist SHALL be these handle prefixes:
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `hyva_checkout_`, `onestepcheckout_`, `multishipping_`, `sales_`, `shipping_tracking_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`, `paymentservicespaypal_smartbuttons_`, `braintree_paypal_review`, `braintree_googlepay_review`, `braintree_lpm_fallback`.
- Lists: `wishlist_`, `catalog_product_compare_`.
- Forms bound to one customer or email link: `productalert_unsubscribe_`, `sendfriend_`.
- Personal pages of known modules: `connector_` (Dotdigital), `paynl_order_`, `paynl_checkout_` (Pay.nl), `payone_onepage_`, `payone_amazon_` (Payone), `buckaroo_mrcash_pay` (Buckaroo), `mpblog_post_history`, `mpblog_post_preview`, `mpblog_author_information` (Mageplaza Blog), `clerk_clerk_powerstep_` (Clerk), `sharecart_index_download` (Mageplaza Share Cart), `mailchimp_cart_loadquote` (Mailchimp), `blog_non_cacheable` (Magefan Blog), `hyvareactcheckout_reactcheckout_` (Hyvä React Checkout).

#### Scenario: Vault handle
- **WHEN** `vault_cards_listaction.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Module email endpoint
- **WHEN** `connector_email_basket.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Same module, other handle
- **WHEN** `payone_promo_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports it, because no prefix of the module covers that handle

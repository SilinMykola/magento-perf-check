## MODIFIED Requirements

### Requirement: X-LAY-01 default allowlist contents
The default allowlist SHALL be these handle prefixes:
- Customer: `customer_`, `loginascustomer_`, `newsletter_manage_`, `review_customer_`, `downloadable_customer_`.
- Checkout and sales: `checkout_`, `hyva_checkout_`, `onestepcheckout_`, `multishipping_`, `sales_`, `shipping_tracking_`.
- Payment: `paypal_`, `vault_`, `paymentservicespaypal_vault_`.
- Lists: `wishlist_`, `catalog_product_compare_`.
- Forms bound to one customer or email link: `productalert_unsubscribe_`, `sendfriend_`.
- Personal pages of known modules: `connector_` (Dotdigital), `paynl_order_`, `paynl_checkout_` (Pay.nl), `payone_onepage_`, `payone_amazon_` (Payone), `buckaroo_mrcash_pay` (Buckaroo), `mpblog_post_history`, `mpblog_post_preview`, `mpblog_author_information` (Mageplaza Blog), `clerk_clerk_powerstep_` (Clerk), `sharecart_index_download` (Mageplaza Share Cart), `mailchimp_cart_loadquote` (Mailchimp), `blog_non_cacheable` (Magefan Blog).

#### Scenario: Vault handle
- **WHEN** `vault_cards_listaction.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Module email endpoint
- **WHEN** `connector_email_basket.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports nothing

#### Scenario: Same module, other handle
- **WHEN** `payone_promo_index.xml` contains a block with `cacheable="false"`
- **THEN** X-LAY-01 reports it, because no prefix of the module covers that handle

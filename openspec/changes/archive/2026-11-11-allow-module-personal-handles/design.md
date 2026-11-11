# Design

## D1. Vendor handles in the default list

The allowlist exists so that pages which are personal by design are not reported. Whether a page is personal does not depend on who wrote the module, so a known module's personal handles belong in the default list as much as core's. A project can still remove them with `layout.replaceAllowlist`.

Each prefix is checked against the module's layouts in the extended corpus part, so that it covers only personal pages:

| Prefix | Module | Pages (round 6 rows, or round 1) |
|---|---|---|
| `connector_` | Dotdigitalgroup_Email | endpoints that Dotdigital's servers fetch with a passcode and an IP check (`ExternalDynamicContentController::authenticate()`): email content for one quote, order, wishlist or customer, coupon generation, and five report endpoints whose content is the same for every caller (bestsellers, most viewed, Feefo score and reviews, product push). Shoppers never request them. The route id `connector` is Dotdigital's in the corpus; another module could use it |
| `paynl_order_`, `paynl_checkout_` | Paynl_Payment | return pages after payment (finish, confirm, cancel, invoice, pending, pickup) and the fast-checkout fallback of the current quote |
| `payone_onepage_`, `payone_amazon_` | Payone_Core | checkout steps of the current quote (review, debit mandate, Amazon review) |
| `buckaroo_mrcash_pay` | Buckaroo_Magento2 | the Bancontact payment page of one transaction |
| `mpblog_post_history`, `mpblog_post_preview`, `mpblog_author_information` | Mageplaza_Blog | the author's own post history, unpublished preview and author form; the public author page `mpblog_author_view` stays reported |
| `clerk_clerk_powerstep_` | Clerk_Clerk | the popup with recommendations for the product just added to the current cart |
| `sharecart_index_download` | Mageplaza_ShareCart | a PDF of the current cart |
| `onestepcheckout_` | one-step checkout modules (seen in Taxjar_SalesTax) | the checkout page, like core `checkout_` |
| `mailchimp_cart_loadquote` | Ebizmarts_MailChimp | a cart restored from an email link (round 1) |
| `blog_non_cacheable` | Magefan_Blog | the blog post preview, which the module itself makes uncacheable (round 1) |

## D2. What stays reported

Pages that every visitor sees remain reported: the public contact pages and the popular search terms page, in core and in Hyvä. Other handles of these modules stay reported too, for example a public Mageplaza Blog page, if it ever sets `cacheable="false"`.

Three round 6 findings graded TP-low drop with `connector_`: report endpoints whose content is shared but which only Dotdigital fetches. The full page cache never serves them to shoppers, so they cost nothing that the rule is about.

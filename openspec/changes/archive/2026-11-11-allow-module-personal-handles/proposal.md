# Proposal

## Why

Round 6 graded 30 X-LAY-01 findings on the extended corpus part: 24 false positives, precision 20 %. All 24 are pages of a few modules that are personal by design, under handles the allowlist does not cover:
- Dotdigital's endpoints under `connector_`, which Dotdigital's servers fetch with a passcode and an IP check, mostly to render one customer's basket, order, wishlist or coupon into an email: 19 of the 47 findings.
- Payment return and checkout-step pages of Pay.nl, Payone and Buckaroo.
- Mageplaza Blog's post history and preview, shown only to the post's author.

Round 1 left module-specific handles out of the default list (`mailchimp_cart_loadquote`, `blog_non_cacheable`), so each project had to add them itself. Round 6 shows that one popular module can hold a third of all findings, which buries the real ones.

## What Changes

- The default allowlist of X-LAY-01 and X-LAY-03 gets a group of personal pages of known modules, each tied to the module it comes from, with the round 1 and round 6 handles.
- The prefixes are as narrow as the graded pages allow, so that other handles of the same module, such as Mageplaza Blog's public `mpblog_author_view` and `mpblog_post_view`, would still be reported. For Buckaroo only `buckaroo_mrcash_pay` is added.
- The spec's list of default prefixes is brought up to date with the code (it misses `hyva_checkout_`, `shipping_tracking_`, `productalert_unsubscribe_` and `sendfriend_`).
- X-LAY-01 is measured again on both corpus parts; it stays experimental unless the thresholds say otherwise.

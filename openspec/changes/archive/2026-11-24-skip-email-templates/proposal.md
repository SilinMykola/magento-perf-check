# Proposal

## Why

T-05 and T-06 check storefront templates: every `.phtml` file under `view/frontend`, `view/base` or `app/design/frontend`. Magento keeps the templates that render parts of an email there too, under `templates/email` of a module or a theme: `Magento_Sales::email/items.phtml`, `Magento_Wishlist::email/items.phtml` and their theme overrides. An email is not a page: no layout shift is measured there, and mail clients do not run scripts. On one private Magento 2.1.7 store T-06 reported an image in a theme's `Magento_Sales/templates/email/shipment/items.phtml`, graded false; the rule page already listed email content as a known false positive.

## What Changes

- A template under a `templates/email` folder is no longer a storefront template, for T-05 and T-06 alike (`TemplateMarkup::isStorefrontTemplate`).
- A storefront template whose own name mentions email, such as `templates/newsletter/email.phtml`, stays checked.
- A `.phtml` file under `view/frontend/web/template`, which Magento never renders as a block template, stays checked: the owner ruled that a file holding the problem is a correct finding even when nothing loads it.

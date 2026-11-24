# Proposal

## Why

`x-lay-01-navigation-only-on-pages` takes a handle that declares the `customer_account_navigation` block for an account page when it is a page, or when only pages include it. The page check it uses missed two cases:

- It did not strip the `hyva_` prefix, as the rest of the layout handling does. The `hyva_` copy of a page that declares the navigation, such as `hyva_acmeroute_navpage_index.xml`, was an account page before that change and stopped being one, so X-LAY-01 reported its `cacheable="false"` block. So did a handle that declares the navigation and that only a page's `hyva_` copy includes.
- It took any handle that counts as a page only by its name for a page, whoever includes it: a handle that extends a page's name and a handle of a route whose controllers were not found. A handle such as `acme_header_nav.xml` of a route `acme` without controllers, which declares the navigation and which `default.xml` includes, became an account page although it is on every page, and its `cacheable="false"` block was not reported.

## What Changes

- The `hyva_` copy of a handle counts as that handle when deciding whether only pages include a handle that declares the navigation; a route whose id starts with `hyva_` keeps its own handles, as before.
- A controller action's own handle still decides for itself. The `hyva_` copy of a page, a handle that extends a page's name and a handle of a route whose controllers were not found count as pages there only when no layout includes them or every layout that does counts as a page too.
- P-FPC-02 reads the same account pages; its scenarios for a copied account layout and a header dropdown get a rule-level test. The project index version is raised, as its layout part changes.

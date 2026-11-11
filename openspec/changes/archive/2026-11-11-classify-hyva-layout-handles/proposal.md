# Proposal

## Why

On a store view with a Hyvä theme, Hyvä adds a `hyva_<handle>` handle next to every handle of the page ([Hyvä: The hyva_ layout handles](https://docs.hyva.io/hyva-themes/writing-code/layout-and-templates/the-hyva_-layout-handles.html), [AddLayoutHandles.php#L60-L66](https://github.com/hyva-themes/magento2-theme-module/blob/6c922be3cd2e9ff9512447c681f388ca829a9c25/src/Observer/AddLayoutHandles.php#L60-L66)). A block with `cacheable="false"` in `hyva_default.xml` therefore takes every page of the store out of Full Page Cache, as one in `default.xml` does.

`NonCacheableBlockRule` classifies a handle by its literal name. `hyva_default`, `hyva_catalog_*` and `hyva_cms_*` fall to X-LAY-01, which is experimental and off by default, so the worst case is silent with the default rules. The allowlist matches the literal name too, so `hyva_customer_*` pages are reported by X-LAY-01 although `customer_*` pages are not.

## What Changes

- X-LAY-03 and X-LAY-01 classify a handle that starts with `hyva_` as the handle that follows the prefix.
- The allowlist is matched against the handle as written and against it without the prefix, so a configured prefix such as `hyva_acme_` keeps working.
- The message keeps the handle as written in the file.
- Measured on 2026-11-11, before and after, on the Magento 2.4.9 core and both parts of the calibration corpus: no `hyva_*` layout there has a `cacheable="false"` block outside `hyva_checkout_*`, so the results do not change.

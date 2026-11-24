# Proposal

## Why

Some modules build their customer account pages without `<update handle="customer_account"/>`: the page includes a handle of the module that copies the account layout, declaring the `customer_account_navigation` block itself. X-LAY-01 then reports the `cacheable="false"` block of such a page, although the page is as personal as any account page. On three private Magento 2.1 and 2.2 stores two of ten graded X-LAY-01 findings were this case. The tool also stopped at the first include: a page that includes a handle which itself includes `customer_account` was not taken for an account page, although Magento merges includes recursively.

## What Changes

- A layout file that declares a `<block name="customer_account_navigation">` makes its handle an account page, as an include of `customer_account` does. A shop-wide handle of X-LAY-03 (`default`, `catalog_*`, `cms_*`) does not count, nor does including such a handle make it an account page: only its own include of `customer_account` does, as before.
- A handle that includes an account page's handle, directly or through other handles, is an account page too, unless it is shop-wide. P-FPC-02 reads the same account pages. The project index version is raised, as its layout part changes.
- Not changed: payment redirect, return and process pages of payment modules, which were the other seven false findings. Their layouts have nothing that tells them apart from a public page; only the handle name or the controller's code does, and guessing by name would hide true findings. They stay reported; the allowlist and `perf-ignore` cover them.

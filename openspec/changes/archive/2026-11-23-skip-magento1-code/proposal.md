# Proposal

## Why

Old Magento 2 projects migrated from Magento 1 can keep Magento 1 code: the code pools `app/code/community` (and `core`, `local`) and a Magento 1 theme such as `app/design/frontend/base/default`. Magento 2 never runs it, and Magento 2 rules do not apply to it. On one private Magento 2.1.7 project it gave 15 findings (P-DI-02 on `Clarion_Customerattribute_Block_*` classes, T-01 and T-06 in `template/` files). The owner ruled on 2026-11-23 that the tool is for Magento 2 and Magento 1 code is not checked.

## What Changes

- `app/code/core`, `app/code/community`, `app/code/local` and every `app/design/<area>/<package>/<theme>` without `theme.xml` and without `registration.php` are left out of the PHP, XML, script and coding-standard analysis, like a disabled module.
- One `magento1-code` notice names each such folder that lies in, or holds, an analyzed path.
- Modules in `app/code/<Vendor>/<Module>` without `etc/module.xml` stay analyzed: core class overrides such as `app/code/Magento/Catalog/...` are loaded through Composer's autoload and run.

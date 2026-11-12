# Proposal

## Why

The Luma source audit of SPEC milestone M6 ranked JavaScript merging, switched on in `app/etc/config.php`, after the RequireJS rules. Adobe tells stores not to enable it, and core merges only the scripts that layout XML adds to the page head, which RequireJS pages hardly have.

| Source | Level | What it says |
|---|---|---|
| [Adobe: Optimize resource files](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/optimize-css-js-files) ([source L106](https://github.com/AdobeDocs/commerce-operations.en/blob/0c13885f16ac339066198329f38d5c5e2d4a06d1/help/implementation-playbook/best-practices/development/optimize-css-js-files.md?plain=1#L106)) | A | "We do not recommend enabling Merge JavaScript Files." It "can cause bundling and RequireJS logic to work incorrectly" and "provides no performance benefit when HTTP/2 is enabled." |
| [View/Asset/Config.php#L25](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/Config.php#L25), [#L81-L87](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/Config.php#L81-L87) | B | The setting is `dev/js/merge_files`, read per store with `isSetFlag()`. |
| [MergeService.php#L86-L112](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Asset/MergeService.php#L86-L112), [Page/Config/Renderer.php#L350-L359](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/View/Page/Config/Renderer.php#L350-L359) | B | When it is on, the page renderer replaces each mergeable group of head scripts with one merged file. |
| [Config etc/di.xml#L183-L200](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Config/etc/di.xml#L183-L200), [#L211-L216](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/app/code/Magento/Config/etc/di.xml#L211-L216) | B | The `system` section of the deployment configuration is merged last, over the database, so a value there wins over it; `env.php` is merged over `config.php`. |

Counted on 2026-11-12: no `app/etc/config.php` of the calibration corpus has a `system` section, so the rule cannot be calibrated there.

## What Changes

- A reader of `app/etc/config.php` that walks its tokens and gives every literal value of the returned array with its key path and line; the file is never run ([ADR 11](../../../docs/adr/0011-settings-read-from-config-php.md)).
- C-02, experimental, at `warning`: `dev/js/merge_files` set to a true value in the `system` section of `app/etc/config.php`, for the default scope, a website or a store view.
- Without corpus data the rule stays experimental; it has no calibration round.

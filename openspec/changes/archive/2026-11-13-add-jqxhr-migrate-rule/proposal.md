# Proposal

## Why

Magento 2.4.6 stopped loading jquery-migrate on every page. jQuery 3 has no `success()`, `error()` or `complete()` on the jqXHR of an Ajax call; jquery-migrate added them back. Code that still calls them throws "is not a function" from 2.4.6, for example theme copies of an old core `Magento_Review/web/js/process-reviews.js`, which then never show the reviews. No other tool reports it: PHPStan does not read JavaScript, and the ESLint rules of magento/magento-coding-standard cover other jQuery APIs (audit of 2026-11-13).

| Source | Level | What it says |
|---|---|---|
| [Release notes, Magento Open Source 2.4.6](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/magento-open-source/2-4-6) ([source L94](https://github.com/AdobeDocs/commerce-operations.en/blob/78d4aa2619b6b9d083ebf07e621405ecb2582025/help/release/release-notes/open-source/2-4-6.md?plain=1#L94)) | A | "`jquery-migrate` has been removed from the Commerce and Magento Open Source code bases." |
| [Theme requirejs-config.js at 2.4.5#L79-L81](https://github.com/magento/magento2/blob/1df4565907d40f14ee1c753cc2de2ce567bfa8d7/app/code/Magento/Theme/view/base/requirejs-config.js#L79-L81) | B | Up to 2.4.5 every page loads `jquery/jquery-migrate`; at 2.4.6 (d846142a) the entry is gone. Patch releases of 2.4.4 and 2.4.5 keep it. |
| [jquery-migrate.js at 2.4.5#L309-L314](https://github.com/magento/magento2/blob/1df4565907d40f14ee1c753cc2de2ce567bfa8d7/lib/web/jquery/jquery-migrate.js#L309-L314) | B | jquery-migrate adds `success`, `error` and `complete` to every jqXHR as aliases of `done`, `fail` and `always`. |

## What Changes

- The script scanner gives the code of a script without its comments and strings, and script rules name the areas they read, so admin scripts can be read ([ADR 12](../../../docs/adr/0012-script-code-and-admin-scripts.md)).
- U-JS-01, experimental, at `error`, an upgrade rule for 2.4.6: `.success()`, `.error()` or `.complete()` in a chain that starts with `$.ajax`, `$.get`, `$.post`, `$.getJSON` or `$.getScript` (also through `jQuery`) or `storage.get`, `post`, `put` or `delete` of mage/storage, after any of `done`, `fail`, `always` or `progress`; `then`, `pipe` and `catch` return a new promise and end the chain.

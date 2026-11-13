# U-JS-01: `success()`, `error()` or `complete()` on a jqXHR

| | |
|---|---|
| Severity | `error` |
| Status | experimental, off by default: [calibration round 18](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/calibration/round-18.md) found 5 findings, all correct (fewer than 10: insufficient data). Enable it with `enabled: true` in `magento-perf-check.neon` |
| Analyzer | XML analyzer, storefront and admin scripts ([ADR 12](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0012-script-code-and-admin-scripts.md)) |
| Upgrade rule | for Magento 2.4.6: runs when the installed version or `--target-version` is 2.4.6 or newer ([configuration](../configuration.md#upgrade-rules)) |
| Protects | compatibility ([ADR 8](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0008-scope-beyond-performance.md)) |

## What it catches

A call of `.success()`, `.error()` or `.complete()` in a chain that starts with an Ajax call: `$.ajax()`, `$.get()`, `$.post()`, `$.getJSON()` or `$.getScript()`, also written with `jQuery`, or `get()`, `post()`, `put()` or `delete()` of mage/storage when it is imported as `storage`. A call inside the arguments of another, such as a `$.get()` in a `success` callback, counts too. The chain may pass through `done()`, `fail()`, `always()` and `progress()` first, which return the same jqXHR. It ends at `then()`, `pipe()` or `catch()`: they return a new promise, which never had these methods, so a call there failed before 2.4.6 too. It reads `.js` files and the JavaScript of `.phtml` templates of the storefront and of the admin, in modules and in themes.

Not reported:
- the same names on anything else, such as `this.city().error(false)` on a Knockout observable or `.error()` on a jQuery collection, and `this.storage.get()`, which is not mage/storage;
- code in comments and in strings;
- a jqXHR kept in a variable and used later (`var xhr = $.ajax(...); xhr.complete(...)`);
- jQuery under another name or path, such as `window.jQuery.ajax()` or `$j.ajax()`, and an optional call (`$.ajax(u)?.success(f)`);
- a file that is a copy of jQuery itself, or a template with such a copy in any of its script blocks: before jQuery 3 its own jqXHR has these methods.

A script whose brackets nest too deep for the PCRE limits of the PHP that runs the tool is not left out in silence: its findings found so far are kept, and the report has a `script` notice for it. A call whose brackets do not close in the code the scanner gives, such as after a misread regular expression (above), is skipped without a notice.

The scanner tells a regular expression from a division by the token before it. A regular expression right after `)`, as in `if (a) /x/.test(b)`, is read as a division, so code after it on that line can be missed, and in a minified one-line file the rest of the file ([ADR 12](https://github.com/SilinMykola/magento-perf-check/blob/main/docs/adr/0012-script-code-and-admin-scripts.md)).

## Why it breaks

jQuery 3 removed `success()`, `error()` and `complete()` from the jqXHR that its Ajax calls return. Up to Magento 2.4.5, every page loaded jquery-migrate, which added them back as aliases of `done()`, `fail()` and `always()`. Magento 2.4.6 removed jquery-migrate, so from 2.4.6 such a call throws:

```
TypeError: $.ajax(...).done(...).complete is not a function
```

The rest of that script stops. Seen on 2026-11-13 in two themes that copied an old core `Magento_Review/web/js/process-reviews.js`: core uses `always()` there since 2.4.5 at least, and the copies' `complete()` would stop the product reviews from appearing.

## Example

Bad:

```javascript
$.ajax({url: url}).done(function (data) {
    $('#reviews').html(data);
}).complete(function () {
    $('#reviews').trigger('contentUpdated');
});
```

Good:

```javascript
$.ajax({url: url}).done(function (data) {
    $('#reviews').html(data);
}).always(function () {
    $('#reviews').trigger('contentUpdated');
});
```

`done()`, `fail()` and `always()` also work on Magento 2.4.5 and older.

## How to suppress

Put `// perf-ignore U-JS-01: reason` on the line of the reported method or on the line before it, as for the other script rules. In a chain broken over lines, that is the line of `.success(`, `.error(` or `.complete(`, not of the Ajax call.

## Known false positives

- A variable named `storage` that is not mage/storage but has `get()` or `post()` methods returning an object with `success()`.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Release notes, Magento Open Source 2.4.6](https://experienceleague.adobe.com/en/docs/commerce-operations/release/notes/magento-open-source/2-4-6) ([source L94](https://github.com/AdobeDocs/commerce-operations.en/blob/78d4aa2619b6b9d083ebf07e621405ecb2582025/help/release/release-notes/open-source/2-4-6.md?plain=1#L94)) | A | "`jquery-migrate` has been removed from the Commerce and Magento Open Source code bases." |
| [Theme requirejs-config.js at 2.4.5#L79-L81](https://github.com/magento/magento2/blob/1df4565907d40f14ee1c753cc2de2ce567bfa8d7/app/code/Magento/Theme/view/base/requirejs-config.js#L79-L81), [at 2.4.6](https://github.com/magento/magento2/blob/d846142a3ab8b49597dfb8bd7508d875efdab19a/app/code/Magento/Theme/view/base/requirejs-config.js) | B | Up to 2.4.5 every page loads `jquery/jquery-migrate` through the top-level `deps`; the 2.4.6 file has no such entry. Patch releases of 2.4.4 and 2.4.5 keep it. |
| [jquery-migrate.js at 2.4.5#L309-L314](https://github.com/magento/magento2/blob/1df4565907d40f14ee1c753cc2de2ce567bfa8d7/lib/web/jquery/jquery-migrate.js#L309-L314) | B | jquery-migrate adds `success`, `error` and `complete` to every jqXHR as aliases of `done`, `fail` and `always`. |
| [Review process-reviews.js at 2.4.5#L32](https://github.com/magento/magento2/blob/1df4565907d40f14ee1c753cc2de2ce567bfa8d7/app/code/Magento/Review/view/frontend/web/js/process-reviews.js#L32) | B | Core's own script uses `always()`. |

Verified 2026-11-13.

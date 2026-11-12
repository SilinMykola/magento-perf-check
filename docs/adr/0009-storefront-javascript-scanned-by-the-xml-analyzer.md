# 9. Storefront JavaScript is scanned by the XML analyzer, without a JavaScript parser

- Status: Accepted
- Date: 2026-11-12

## Context

SPEC milestone M6 plans rules for storefront JavaScript: RequireJS modules, their configuration and the scripts of templates. The first one, J-DEP-01, looks for the module ID `jquery/ui` among the dependencies of storefront code. Neither analyzer reads JavaScript: PHPStan reads PHP and the PHP parts of templates, the XML analyzer reads XML and GraphQL schemas.

What such rules need from the source is small: the string literals, whether each one is an object key, their line numbers, and the comments, for suppression. A full JavaScript parser would be a new dependency, and none is written in PHP and kept up to date with the language.

## Decision

- The XML analyzer gets a third kind of file, `script`, next to XML and GraphQL schemas: `.js` files and `.phtml` templates under a module's `view/frontend` or `view/base`, and under `app/design/frontend`, the paths T-05 and T-06 use for storefront templates. The same discovery applies: test code, disabled modules and core packages are skipped as for XML files. `node_modules` folders are skipped, and so is an `x.min.js` next to its `x.js`. The files are read only when a script rule is enabled.
- A small scanner (`src/Xml/Script/JsScanner.php`) splits JavaScript into string literals and comments. It tells a regular expression literal from a division by the token before the `/`. It does not build a syntax tree.
- In a `.phtml` template, only the bodies of JavaScript `<script>` elements outside HTML comments are scanned, through `TemplateMarkup`, which T-05 and T-06 use. A `<?=` tag in a body reads as a value, so a `/` after it divides; other PHP reads as white space. A `type` that PHP prints counts as JavaScript.
- A script finding is suppressed by a JavaScript comment `// perf-ignore RULE-ID: reason` (or `/* ... */`) that ends on the line of the finding or on the line before it. As for XML, the reason is mandatory.
- Script rules use the prefix `J-`.

## Consequences

- A rule that needs more than strings, such as the structure of a `require.config()` object, needs more of the scanner, or a parser and another ADR.
- The scanner can be wrong on unusual code: a `/` after `}` that is a division, or a regular expression right after the `)` of `if`, `while` or `for`, which it reads as a division. That changes which strings it sees, not whether the tool runs.
- A `<!--` inside an attribute value of a template is taken for the start of an HTML comment, which hides the scripts after it until a `-->`.
- Not read: `x-magento-init` blocks and `data-mage-init` attributes, which are JSON, and scripts that PHP builds as a string, for example for `$secureRenderer->renderTag()`.
- A run with a script rule enabled reads the storefront `.js` and `.phtml` files. On the calibration corpus this took about a second (2026-11-12).
- Themes outside `app/design`, such as a theme installed through Composer, are not read, as for T-05 and T-06.

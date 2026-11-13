# 12. The script scanner gives the code without comments and strings, and script rules name their areas

- Status: Accepted
- Date: 2026-11-13

## Context

[ADR 9](0009-storefront-javascript-scanned-by-the-xml-analyzer.md) and [ADR 10](0010-property-paths-in-the-script-scanner.md) gave the script rules the string literals and comments of storefront JavaScript. U-JS-01 looks at code instead: a call such as `.complete()` chained on `$.ajax(...)`. A text search cannot tell that from `.error(false)` on a Knockout observable, or from code in a comment or a string. Two of the places U-JS-01 is meant for are admin scripts, which the scanner did not read.

A first version gave a list of code tokens. On a module's bundled `ckeditor.js` of a few megabytes, the list did not fit in the 128 MB the command runs with.

## Decision

- On request, the scanner also gives the code of a script: the source with every comment, string, template literal and regular expression literal replaced by spaces, line breaks kept, so an offset in it is an offset in the source. A blanked string starts with a double quote, a template with a backtick and a regular expression with `/`, so a value is still seen there. It takes about as much memory as the file, and it is built only when a rule asks for it.
- A rule matches the code with regular expressions, brackets balanced by recursion, and counts line breaks for the line. When a pattern fails on a PCRE limit, the rule throws `ScriptMatchError` with the findings it made so far; the analyzer keeps them and reports a `script` notice for the file instead of a silent gap.
- Each script rule names the areas whose scripts it reads: `frontend` and `base` for the storefront rules J-DEP-01 and J-DEP-02, the admin too for U-JS-01. File discovery finds scripts of `view/adminhtml` and of `app/design/adminhtml` as well, and the analyzer reads a script only when an enabled rule reads its area.

## Consequences

- No JavaScript parser is needed. A call reached through a variable (`var xhr = $.ajax(...); xhr.complete(...)`) is not seen.
- A regular expression right after `)`, as in `if (a) /x/.test(b)`, is read as a division, as before. In code mode that can blank the rest of the line, so a call there is missed; in a minified one-line file, the rest of the file.
- The patterns use possessive repetition, so a large callback does not make PCRE backtrack through it; the recursion still grows with the depth of nested brackets. A script that hits a PCRE limit anyway is a notice, not a silent gap. Code the scanner misreads, such as a regular expression after `)`, is still a silent gap.
- Storefront rules see exactly what they saw before; an admin script is read only while U-JS-01 is enabled.

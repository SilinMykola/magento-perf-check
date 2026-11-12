# 10. The script scanner gives each string its property path

- Status: Accepted
- Date: 2026-11-12

## Context

[ADR 9](0009-storefront-javascript-scanned-by-the-xml-analyzer.md) gave the script rules the string literals of storefront JavaScript, each with its line and whether it is an object key. J-DEP-02 needs to know where in a `requirejs-config.js` a string is: a module in the top-level `deps` loads on every page, one in `shim.<module>.deps` only orders the loading of a module that something else asks for, and a value in `map` or `paths` is a name, not a dependency.

## Decision

- The scanner tracks the brackets it is inside. For each `{`, it remembers the key of the property whose value is being read: a quoted literal or an identifier that follows `{` or `,` and is followed by `:`. A `,` ends that property.
- Each string literal gets a property path: those keys, outermost first. Arrays, parentheses, template substitutions and the braces of blocks add nothing. A key is not in its own path.
- A closing bracket that matches no open one is ignored, so a scanning mistake does not shift every later path.

## Consequences

- `var config = {deps: ['a'], shim: {b: {deps: ['c']}}}` gives `a` the path `deps` and `c` the path `shim`, `b`, `deps`. A rule compares paths instead of parsing the object.
- Computed keys (`{[name]: x}`), shorthand properties and spread elements give no key. Their values get the path of the object around them.
- The braces of a block are counted like those of an object. Without a key, they add nothing to the paths.

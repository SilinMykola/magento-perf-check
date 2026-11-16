# Proposal

## Why

T-06 reads only the template's own `<style>`. Most of its false positives in [round 20](../../../docs/calibration/round-20.md) and [round 22](../../../docs/calibration/round-22.md) are images that a stylesheet of the same module sizes: two by the module's `_module.less`, which the Luma theme compiles, in round 20, and three in round 22, by a CSS file the module's layout adds or a `_module.less`.

## What Changes

- T-06 also reads the CSS and LESS files under `web/css` of the template's module (`view/frontend`, `view/base`), or of the theme and the theme's module folder, before the template's `<style>`. LESS nesting is followed, a guard is read as always true, and `.min.css` files are left out. Whether a layout loads the file, or the theme compiles it, is not checked: a stylesheet that is not loaded can now hide an image that shifts.

# Proposal

## Why

SPEC milestone M6 plans rules for storefront code, Hyvä first. A source audit of the Hyvä documentation found two candidates that a template check can see, that have level A sources on cost, and that the calibration corpus has enough of to calibrate:

| Candidate | Level A sources | Findings in the corpus |
|---|---|---|
| A `<script src>` without `async`, `defer` or `type="module"` in a frontend template | [Hyvä: Loading external JavaScript](https://docs.hyva.io/hyva-themes/writing-code/patterns/loading-external-javascript.html), [Hyvä: Core Web Vitals](https://docs.hyva.io/hyva-themes/performance/core-web-vitals.html), [Adobe: Optimize resource files](https://experienceleague.adobe.com/en/docs/commerce-operations/implementation-playbook/best-practices/development/optimize-css-js-files) | 4 in 4 of the 25 modules, 14 in 5 modules of the extended part |
| An `<img>` without both `width` and `height` in a frontend template | [Hyvä: Performance tips](https://docs.hyva.io/hyva-themes/performance/hyva-performance-tips.html) ("Always set width and height"), [Hyvä: Core Web Vitals](https://docs.hyva.io/hyva-themes/performance/core-web-vitals.html) ("Without them, the browser cannot reserve space before the media loads, causing a shift when each asset arrives.") | 18 in 3 of the 25 modules, 78 in 14 modules of the extended part, 6 of them in the Hyvä default theme |

Sources opened and counts measured on 2026-11-11. The counts come from a scan of `view/frontend` and `app/design/frontend` templates, with PHP blocks removed first, test code left out. They are an upper bound: they do not yet leave out scripts built in `<template>` or images sized by bound attributes.

Rejected in the same audit for lack of a level A or B source on cost: GraphQL calls from templates, `x-init` with `fetch`, a plain `reload-customer-section-data` dispatch, missing Tailwind content paths, `x-defer`, `Alpine.store`.

## What Changes

- T-05, experimental: a synchronous external script in a frontend template.
- T-06, experimental: an image without `width` and `height` in a frontend template.
- Both are calibrated on both corpus parts before any decision on the default set.
- A synchronous script in layout XML `<head>` is left for later: core adds `defer` itself when `dev/js/defer_non_critical` is on, so that check needs the configuration value.

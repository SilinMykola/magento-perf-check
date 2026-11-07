# Proposal

## Why

P-FPC-05 reported `setHeader('Pragma', 'no-cache')` with the message that it makes the response uncacheable. That does not hold: `Magento\Framework\App\PageCache\Kernel::process()` and `varnish6.vcl` read `Cache-Control` from a response and never `Pragma`. The grader of the second check on other modules marked this as a false positive. Every such finding in the 78 modules checked sits on the line after a `Cache-Control` finding in the same controller, so it reported the same decision twice.

## What Changes

- **P-FPC-05:** `setHeader()` counts only for a `Cache-Control` name.

## Impact

The default set changes: 3 fewer findings on the 78 modules checked, none on Magento core.

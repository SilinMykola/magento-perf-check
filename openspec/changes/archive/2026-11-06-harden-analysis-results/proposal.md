# Proposal

## Why

An independent audit of v0.1.1 found runs that report a clean result when they are not: a PHPStan worker crash ends in exit code 0, PHPStan's result cache ignores the XML that PHP rules read, concurrent runs read each other's configuration, and paths with nothing to analyze pass. A static analyzer that can be wrong silently is worse than none. The audit also found P-LOOP-01 errors on calls that run no query, and a README that omits how weak some default rules look.

## What Changes

- **Failed analysis:** errors outside any file in PHPStan's output mean exit code 2. The report is still written, and no baseline is.
- **Nothing to analyze:** exit code 2.
- **Result cache:** a fingerprint of the indexed XML is a PHPStan parameter, so a change invalidates cached results.
- **Concurrent runs:** the generated configuration is named after its content.
- **Discovery:** XML under symlinked directories is found, except through a link that leads back up the path; a single `.phtml` path is analyzed.
- **Paths:** normalized and deduplicated; `..` is rejected.
- **Notices:** reach stderr where the report does not show them; a missing `generated/code` is a notice.
- **Temp directory:** per user and private.
- **P-LOOP-01:** `Currency::load()` and the `Magento\Store\Api` repositories are out of scope.
- **P-COL-01:** `count($this)` in `_afterLoad()` is out of scope.
- **Docs:** security note, temp files, weak default rules.

Not changed: which rules are in the default set and their severities. The source for the P-LOOP-01 scope is core at the SPEC pin: `Currency::load()` calls only `setData`, and the store repositories read the `scopes` configuration.

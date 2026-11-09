# Proposal

## Why

P-LOOP-03 reports a `save()` or `delete()` in a loop even when the loop is left right after it, by `break`, `return` or `throw`. Such a write runs at most once per run of the loop. Calibration graded one such case as a false positive (P-LOOP-03 row 18, `Magento\Ui\Controller\Adminhtml\Bookmark\Save`); the same pattern holds 4 more findings in the Magento core and 1 in the calibration corpus.

## What Changes

- **P-LOOP-03:** a call that, in its own iteration, is followed by leaving the loop is not reported. That holds when the call is in a statement of its own (or a `return`), and every way on from it in the same iteration reaches `return` or `throw`, or `break` when no other loop in the function encloses it, with no `continue`, `break` or `goto` in between. Calls inside `try`, in a closure, or in the condition of an `if` are still reported.

## Impact

Fewer P-LOOP-03 findings in the default set at `info`: in the calibration corpus 1 of 57 goes, in the Magento core 5 of 160. No finding is added.

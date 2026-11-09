# Proposal

## Why

Issue #4: a call after which the loop is left is still reported when the loop itself sits in a `try`, the common shape of a helper whose whole body is wrapped in `try { ... } catch (\Throwable $e) { log }`. The loop exit visitor did not walk into `try` at all, so no call in a loop inside one was marked. An exception from such a call reaches a `catch` outside the loop, which has left the loop already: the call still runs at most once per run of the loop.

## What Changes

- **All loop rules:** a `try` outside any loop of the function is walked like other code: its body, its `catch` blocks and its `finally`. A call in a loop inside it is left out under the same conditions as anywhere else.
- A `try` inside the loop stays as it is: its `catch` can go on with the next iteration, so the call in it counts, and so does a call in a loop nested in such a `try`.

## Impact

Fewer findings of P-LOOP-01 (default set, `error`) and the other loop rules on code that wraps a loop in `try`. No finding is added.

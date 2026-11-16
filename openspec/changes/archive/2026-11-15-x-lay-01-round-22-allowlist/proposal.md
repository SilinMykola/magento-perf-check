# Proposal

## Why

[Round 22](../../../docs/calibration/round-22.md) graded 5 X-LAY-01 findings on the `github2` corpus part; 4 were false: pages that are personal by design but that the allowlist did not name. One module's handle, `bonlineco_checkout`, is added by its observer to `checkout_index_index` only; another module's `withdrawal_guest_view`, `withdrawal_index_success` and `withdrawal_index_view` show one order of the visitor, read from the session, and redirect without it.

## What Changes

- The default allowlist of X-LAY-01 gets `bonlineco_checkout`, `withdrawal_guest_view`, `withdrawal_index_success` and `withdrawal_index_view`, each as narrow as the module's personal pages allow: the same module's public guest search form, `withdrawal_guest_search`, is still reported.
